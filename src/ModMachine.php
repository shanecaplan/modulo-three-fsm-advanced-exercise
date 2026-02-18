<?php

declare(strict_types=1);

namespace FSM;

use FSM\Utils\Validation;

class ModMachine {

	private FiniteStateMachine $fsm;

	public function __construct (int $modulus) {
		$this->assertModulusValid($modulus);

		$states = $this->resolveStates($modulus);
		$alphabet = ['0', '1'];
		$initialState = '0';
		$transitionFunction = $this->createTransitionFunction($modulus);

		$this->fsm = new FiniteStateMachine($states, $alphabet, $initialState, $states, $transitionFunction);
	}

	public function execute (string $binary): int {
		if (!Validation::isBinaryString($binary)) {
			throw new \InvalidArgumentException("Invalid binary string '$binary'. Expected only '0' or '1' characters.");
		}

		$acceptedState = $this->fsm->execute($binary);
		$remainder = (int) $acceptedState;

		return $remainder;
	}

	private function assertModulusValid (int $modulus): void {
		if ($modulus <= 0) {
			throw new \InvalidArgumentException("Invalid modulus $modulus. Expected modulus to be greater than zero.");
		}
	}

	private function resolveStates (int $modulus): array {
		return array_map('strval', range(0, $modulus - 1));
	}

	private function createTransitionFunction (int $modulus): TransitionFunction {
		$transitionFunction = new TransitionFunction();

		$nextRemainder = 0;

		for ($remainder = 0; $remainder < $modulus; $remainder++) {
			$inputState = (string) $remainder;

			for ($symbol = 0; $symbol <= 1; $symbol++) {
				$inputSymbol = (string) $symbol;

				$nextState = (string) $nextRemainder++;

				if ($nextRemainder === $modulus) {
					$nextRemainder = 0;
				}

				$transitionFunction->addTransition($inputState, $inputSymbol, $nextState);
			}
		}

		return $transitionFunction;
	}

	public function getFiniteStateMachine (): FiniteStateMachine {
		return $this->fsm;
	}
}
