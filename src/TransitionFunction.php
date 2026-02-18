<?php

declare(strict_types=1);

namespace FSM;

class TransitionFunction {

	private array $transitions = [];

	public function execute (string $inputState, string $inputSymbol): string {
		if (isset($this->transitions[$inputState][$inputSymbol])) {
			$nextState = $this->transitions[$inputState][$inputSymbol];
			return $nextState;
		}

		throw new \InvalidArgumentException(
			"Unknown transition with input state '$inputState' and input symbol '$inputSymbol'."
		);
	}

	public function addTransition (string $inputState, string $inputSymbol, string $nextState): void {
		if (!isset($this->transitions[$inputState])) {
			$this->transitions[$inputState] = [];
		}

		$this->transitions[$inputState][$inputSymbol] = $nextState;
	}

	public function hasTransition (string $inputState, string $inputSymbol): bool {
		return isset($this->transitions[$inputState][$inputSymbol]);
	}

	public function hasTransitionsForState (string $inputState): bool {
		return isset($this->transitions[$inputState]);
	}

	public function getStatesCount (): int {
		return count($this->transitions);
	}

	public function getTransitionsCountForState (string $inputState): int {
		return count($this->transitions[$inputState] ?? []);
	}
}
