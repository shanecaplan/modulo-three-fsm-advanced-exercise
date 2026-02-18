<?php

/**
 * NOTE: More comprehensive tests can be found in ModMachineTest.php
 *       ModThreeMachine has ModMachine as a dependency, and it is ModMachine (and ultimately FiniteStateMachine)
 *       that are doing the heavy lifting.
 */

declare(strict_types=1);

require_once 'data/BinaryData.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use FSM\ModThreeMachine;

class ModThreeMachineTest extends TestCase {

	private ModThreeMachine $modThreeMachine;

	protected function setUp (): void {
		$this->modThreeMachine = new ModThreeMachine();
	}

	/**
	 * Test correct modulo three result using a wide variety of valid binary numbers.
	 */
	#[DataProvider('validBinaryInputAndExpectedRemainderProvider')]
	public function testCorrectModuloThreeResult (string $binaryInput, int $expectedRemainder): void {
		$actualRemainder = $this->modThreeMachine->execute($binaryInput);

		$this->assertSame($expectedRemainder, $actualRemainder);
	}

	/**
	 * Provides comprehensive valid binary inputs for execution and corresponding expected remainders for comparison
	 * with said binary inputs.
	 * All possible remainders for mod 3 are covered (0, 1, and 2).
	 * Binary inputs vary by bits / number of bits.
	 * Valid edge cases are also covered.
	 * See BinaryData::getComprehensiveBinaryNumbers
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
