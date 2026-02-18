<?php

declare(strict_types=1);

require_once 'data/BinaryData.php';

use FSM\ModMachine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use tests\SharedData;

class ModMachineTest extends TestCase {

	/**
	 * Test wide variety of mod machines using a wide variety of binary inputs.
	 */
	#[DataProvider('validModMachineDataProvider')]
	public function testModMachine (int $modulus, string $binaryInput, int $expectedRemainder): void {
		$modMachine = new ModMachine($modulus);
		$actualRemainder = $modMachine->execute($binaryInput);
		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * Provides modulus for ModMachine instantiation (from mod 1 to mod 25), comprehensive valid binary
	 * inputs for execution, and corresponding expected remainders for comparison with said binary inputs.
	 * Binary inputs vary by bits / number of bits.
	 * Valid edge cases are also covered.
	 * See BinaryData::getComprehensiveBinaryNumbers
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

	/**
	 * Test modulus of 0 (zero) throws exception.
	 */
	public function testModulusOfZeroThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid modulus 0. Expected modulus to be greater than zero.');

		new ModMachine(0);
	}

	/**
	 * Test negative modulus throws exception.
	 */
	public function testNegativeModulusThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid modulus -1. Expected modulus to be greater than zero.');

		new ModMachine(-1);
	}

	/**
	 * Test invalid numeric character throws exception.
	 */
	public function testInvalidNumericCharacterThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '10031'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('10031');
	}

	/**
	 * Test non-numeric character throws exception.
	 */
	public function testNonNumericCharacterThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '1b010'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('1b010');
	}

	/**
	 * Test space character alone throws exception.
	 */
	public function testSpaceCharacterAloneThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string ' '. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute(' ');
	}

	/**
	 * Test space character in middle throws exception.
	 */
	public function testSpaceCharacterInMiddleThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid binary string '10 01'. Expected only '0' or '1' characters.");

		$modMachine = new ModMachine(4);
		$modMachine->execute('10 01');
	}

	/**
	 * Test empty input evaluates to zero.
	 */
	public function testEmptyInputEvaluatesToZero (): void {
		$modMachine = new ModMachine(4);

		$expectedRemainder = 0;
		$actualRemainder = $modMachine->execute('');

		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * Test very large number.
	 */
	public function testVeryLargeNumber (): void {
		$modMachine = new ModMachine(5);

		$largeBinaryInput = str_repeat('1', 100); // over 64 bits

		$expectedRemainder = 0;

		$actualRemainder1 = $modMachine->execute($largeBinaryInput);
		$actualRemainder2 = $modMachine->execute($largeBinaryInput);

		$this->assertSame($expectedRemainder, $actualRemainder1); // check for a remainder of 0
		$this->assertSame($actualRemainder1, $actualRemainder2); // check for a consistent result
	}

	/**
	 * Test comprehensive range of numbers (0 to 100).
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
