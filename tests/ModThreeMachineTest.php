<?php

/**
 * NOTE: More comprehensive tests can be found in ModMachineTest.php.
 *       ModThreeMachine is a thin specialisation of ModMachine with the modulus fixed to 3.
 *       It is ModMachine (and ultimately FiniteStateMachine) that do the heavy lifting, so
 *       the bulk of correctness and edge-case coverage lives there.
 *       These tests focus on confirming that ModThreeMachine wires up correctly and returns
 *       accurate mod-3 results across a broad range of binary inputs.
 */

declare(strict_types=1);

require_once 'data/BinaryData.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use FSM\ModThreeMachine;

/**
 * Unit tests for {@see ModThreeMachine}.
 *
 * A single shared {@see ModThreeMachine} instance is used across all data-driven
 * test cases to verify that the machine is stateless and produces consistent results
 * on repeated calls.
 */
class ModThreeMachineTest extends TestCase {

	/** Shared instance used for all test cases. Re-created before each test by setUp(). */
	private ModThreeMachine $modThreeMachine;

	/**
	 * Creates a fresh ModThreeMachine before each test to ensure test isolation.
	 */
	protected function setUp (): void {
		$this->modThreeMachine = new ModThreeMachine();
	}

	/**
	 * Verifies that ModThreeMachine returns the correct mod-3 remainder for every
	 * binary input provided by {@see validBinaryInputAndExpectedRemainderProvider}.
	 *
	 * Expected remainders are computed independently via PHP's bindec() and the
	 * modulo operator, so this test does not rely on the machine's own implementation
	 * for the reference value.
	 */
	#[DataProvider('validBinaryInputAndExpectedRemainderProvider')]
	public function testCorrectModuloThreeResult (string $binaryInput, int $expectedRemainder): void {
		$actualRemainder = $this->modThreeMachine->execute($binaryInput);

		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * Generates test cases covering a broad range of binary inputs, ensuring all
	 * three possible mod-3 remainders (0, 1, and 2) are exercised.
	 *
	 * The binary numbers provided by {@see BinaryData::getComprehensiveBinaryNumbers()}
	 * vary in length and bit pattern (all zeros, all ones, mixed), and also include
	 * edge cases such as the empty string (representing 0) and strings with leading zeros.
	 *
	 * Expected remainders are computed via bindec($binaryInput) % 3, which serves as
	 * an independent reference implementation.
	 *
	 * @return array<array{string, int}> Tuples of [binaryInput, expectedRemainder].
	 */
	public static function validBinaryInputAndExpectedRemainderProvider (): array {
		$provided = [];

		$binaryNumbers = BinaryData::getComprehensiveBinaryNumbers();

		foreach ($binaryNumbers as $binaryInput) {
			$expectedRemainder = bindec($binaryInput) % 3;
			$provided[] = [$binaryInput, $expectedRemainder];
		}

		return $provided;
	}
}
