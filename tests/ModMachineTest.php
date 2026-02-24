<?php

declare(strict_types=1);

require_once 'data/BinaryData.php';

use FSM\ModMachine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use tests\SharedData;

/**
 * Unit tests for {@see ModMachine}.
 *
 * Tests are grouped into three areas:
 *
 *  1. Data-driven correctness — verifies that the machine produces the correct
 *     remainder for a comprehensive set of (modulus, binary input) pairs using
 *     {@see validModMachineDataProvider}.
 *
 *  2. Construction-time validation — verifies that invalid moduli are rejected
 *     with a descriptive exception.
 *
 *  3. Input validation — verifies that non-binary characters in the input string
 *     are rejected before execution reaches the underlying FSM.
 *
 *  4. Edge cases — empty input, very large numbers, and a range sweep to confirm
 *     correctness and consistency across moduli.
 */
class ModMachineTest extends TestCase {

	// =========================================================================
	// Data-driven correctness
	// =========================================================================

	/**
	 * Verifies that ModMachine returns the correct remainder for every combination
	 * of modulus (1–25) and binary input provided by {@see validModMachineDataProvider}.
	 *
	 * The expected remainder is computed independently using PHP's bindec() and the
	 * modulo operator, so this test does not rely on the machine's own implementation
	 * for the expected value.
	 */
	#[DataProvider('validModMachineDataProvider')]
	public function testModMachine (int $modulus, string $binaryInput, int $expectedRemainder): void {
		$modMachine = new ModMachine($modulus);
		$actualRemainder = $modMachine->execute($binaryInput);
		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * Generates test cases for every combination of modulus in [1, 25] and every
	 * binary number returned by {@see BinaryData::getComprehensiveBinaryNumbers()}.
	 *
	 * The comprehensive binary numbers cover:
	 *   - All possible remainders for each modulus (0 through modulus - 1)
	 *   - Single-bit values ('0', '1')
	 *   - Multi-bit values with varying patterns (all zeros, all ones, mixed)
	 *   - Edge cases such as the empty string (representing 0) and leading zeros
	 *
	 * Expected remainders are derived via PHP's built-in bindec() + modulo, acting
	 * as an independent reference implementation.
	 *
	 * @return array<array{int, string, int}> Tuples of [modulus, binaryInput, expectedRemainder].
	 */
	public static function validModMachineDataProvider (): array {
		$provided = [];

		$minModulus = 1;
		$maxModulus = 25;

		$binaryNumbers = BinaryData::getComprehensiveBinaryNumbers();

		for ($modulus = $minModulus; $modulus <= $maxModulus; $modulus++) {
			foreach ($binaryNumbers as $binaryInput) {
				$expectedRemainder = bindec($binaryInput) % $modulus;
				$provided[] = [$modulus, $binaryInput, $expectedRemainder];
			}
		}

		return $provided;
	}

	// =========================================================================
	// Construction-time validation
	// =========================================================================

	/**
	 * A modulus of zero is invalid because it would require division by zero to
	 * compute a remainder. The constructor must reject it immediately.
	 */
	public function testModulusOfZeroThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid modulus 0. Expected modulus to be greater than zero.');

		new ModMachine(0);
	}

	/**
	 * A negative modulus has no meaningful interpretation and must be rejected
	 * by the constructor.
	 */
	public function testNegativeModulusThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid modulus -1. Expected modulus to be greater than zero.');

		new ModMachine(-1);
	}

	// =========================================================================
	// Input validation
	// =========================================================================

	/**
	 * A digit other than '0' or '1' in the input should be caught and rejected
	 * before execution reaches the underlying FSM.
	 *
	 * Note: '3' at position 3 of '10031' is the first invalid character encountered.
	 */
	public function testInvalidNumericCharacterThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '10031'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('10031');
	}

	/**
	 * Non-numeric characters in the input must be rejected by the binary-string
	 * validation before execution reaches the underlying FSM.
	 */
	public function testNonNumericCharacterThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '1b010'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('1b010');
	}

	/**
	 * A whitespace-only input must be rejected; a space character is not a valid
	 * binary digit.
	 */
	public function testSpaceCharacterAloneThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string ' '. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute(' ');
	}

	/**
	 * A space embedded within an otherwise valid binary string must be rejected;
	 * whitespace is not a valid binary digit.
	 */
	public function testSpaceCharacterInMiddleThrowsException (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '10 01'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('10 01');
	}

	// =========================================================================
	// Edge cases
	// =========================================================================

	/**
	 * An empty input string represents the integer 0, so the remainder should be
	 * 0 for any modulus (since 0 mod N = 0).
	 */
	public function testEmptyInputEvaluatesToZero (): void {
		$modMachine = new ModMachine(4);

		$expectedRemainder = 0;
		$actualRemainder = $modMachine->execute('');

		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * A 100-bit all-ones binary string (2^100 - 1) exceeds a 64-bit integer, so PHP
	 * would overflow if evaluated with bindec(). The DFA computes remainders
	 * incrementally and therefore handles arbitrarily large numbers correctly.
	 *
	 * For mod 5: (2^100 - 1) mod 5 = 0, verified by number theory (since 2^4 ≡ 1 mod 5,
	 * and 100 is a multiple of 4, so 2^100 ≡ 1, meaning 2^100 - 1 ≡ 0 mod 5).
	 *
	 * The test also checks that two identical calls return the same result, confirming
	 * that the machine is stateless between executions.
	 */
	public function testVeryLargeNumber (): void {
		$modMachine = new ModMachine(5);

		$largeBinaryInput = str_repeat('1', 100); // 100-bit number, well beyond 64-bit range

		$expectedRemainder = 0;

		$actualRemainder1 = $modMachine->execute($largeBinaryInput);
		$actualRemainder2 = $modMachine->execute($largeBinaryInput);

		$this->assertSame($expectedRemainder, $actualRemainder1); // 2^100 - 1 ≡ 0 (mod 5)
		$this->assertSame($actualRemainder1, $actualRemainder2);  // machine must be stateless
	}

	/**
	 * Sweeps all integers from 0 to 100, converting each to a binary string and
	 * verifying that the machine returns the same remainder as PHP's native modulo
	 * operator. This provides a dense, sequential check across a realistic input range.
	 */
	public function testComprehensiveRangeOfNumbers (): void {
		$modMachine = new ModMachine(5);

		for ($i = 0; $i <= 100; $i++) {
			$binary = decbin($i);

			$expectedRemainder = $i % 5;
			$actualRemainder = $modMachine->execute($binary);

			$this->assertSame($expectedRemainder, $actualRemainder, "Failed for decimal $i (binary $binary).");
		}
	}
}
