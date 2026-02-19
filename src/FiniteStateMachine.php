<?php

declare(strict_types=1);

namespace FSM;

class FiniteStateMachine {

	private array $allowedStates;
	private array $alphabet;
	private string $initialState;
	private array $acceptedStates;
	private TransitionFunction $transitionFunction;

	public function __construct (
		array $allowedStates,
		array $alphabet,
		string $initialState,
		array $acceptedStates,
		TransitionFunction $transitionFunction
	) {
		$this->allowedStates = $allowedStates;
		$this->alphabet = $alphabet;
		$this->initialState = $initialState;
		$this->acceptedStates = $acceptedStates;
		$this->transitionFunction = $transitionFunction;

		$this->assertAlphabetValid();
		$this->assertAllowedStatesValid();
		$this->assertAcceptedStatesValid();
		$this->assertInitialStateValid();
		$this->assertTransitionFunctionValid();
	}

	public function execute (string $input): string {
		$currentState = $this->initialState;

		for ($i = 0; $i < strlen($input); $i++) {
			$symbol = $input[$i];
			$this->assertInputSymbolInAlphabet($symbol, $input, $i);
			$currentState = $this->transitionFunction->execute($currentState, $symbol);
		}

		$this->assertFinalStateAccepted($currentState, $input);

		return $currentState;
	}

	private function assertAlphabetValid (): void {
		$this->assertUniqueNonEmptyStringArray($this->alphabet, [
			'arrayEmpty' => "Invalid alphabet array. Expected non-empty array.",
			'valueNotString' => "Invalid type for alphabet symbol: %s. Expected string.",
			'valueEmptyString' => "Invalid value for symbol '%s' in alphabet. Expected non-empty string.",
			'valueDuplicate' => "Duplicate symbol '%s' in alphabet. Expected alphabet symbols to be unique.",
		]);
	}

	private function assertAllowedStatesValid (): void {
		$this->assertUniqueNonEmptyStringArray($this->allowedStates, [
			'arrayEmpty' => "Invalid allowed states array. Expected non-empty array.",
			'valueNotString' => "Invalid type for allowed state: %s. Expected string.",
			'valueEmptyString' => "Invalid value for allowed state '%s'. Expected non-empty string.",
			'valueDuplicate' => "Duplicate allowed state '%s'. Expected allowed states to be unique.",
		]);
	}

	private function assertAcceptedStatesValid (): void {
		$acceptedStates = $this->acceptedStates;

		foreach ($acceptedStates as $acceptedState) {
			$this->assertStateAllowed(
				$acceptedState,
				"Unknown accepted state '%s'. Expected accepted state to be found in list of allowed states."
			);
		}
	}

	private function assertInitialStateValid (): void {
		$this->assertStateAllowed(
			$this->initialState,
			"Unknown initial state '%s'. Expected initial state to be found in list of allowed states."
		);
	}

	private function assertStateAllowed (string $state, string $errorMessage): void {
		if (!in_array($state, $this->allowedStates, true)) {
			throw new \InvalidArgumentException(sprintf($errorMessage, $state));
		}
	}

	private function assertTransitionFunctionValid (): void {
		$transitionFunction = $this->transitionFunction;
		$allowedStates = $this->allowedStates;

		foreach ($allowedStates as $inputState) {
			if (!$transitionFunction->hasTransitionsForState($inputState)) {
				throw new \InvalidArgumentException("Transitions not defined for state '$inputState'.");
			}

			$this->assertTransitionsForStateValid($transitionFunction, $inputState);
		}

		$transitionsStatesCount = $transitionFunction->getStatesCount();
		$allowedStatesCount = count($allowedStates);

		if ($transitionsStatesCount > $allowedStatesCount) {
			throw new \InvalidArgumentException(
				"Transitions exist for $transitionsStatesCount states but there are only $allowedStatesCount allowed ".
				"states. Expected transitions to be defined only for allowed states."
			);
		}
	}

	private function assertTransitionsForStateValid (TransitionFunction $transitionFunction, string $inputState): void {
		$alphabet = $this->alphabet;

		foreach ($alphabet as $inputSymbol) {
			if (!$transitionFunction->hasTransition($inputState, $inputSymbol)) {
				throw new \InvalidArgumentException(
					"Missing transition for state '$inputState' and symbol '$inputSymbol'. ".
					"Expected transition to be defined."
				);
			}

			$nextState = $transitionFunction->execute($inputState, $inputSymbol);

			if (!in_array($nextState, $this->allowedStates, true)) {
				throw new \InvalidArgumentException(
					"Transition for state '$inputState' and symbol '$inputSymbol' leads to an invalid next state of ".
					"'$nextState'. Expected next state to be in list of allowed states."
				);
			}
		}

		$transitionsCountForState = $transitionFunction->getTransitionsCountForState($inputState);
		$symbolsCount = count($alphabet);

		if ($transitionsCountForState > $symbolsCount) {
			throw new \InvalidArgumentException(
				"There are $transitionsCountForState transitions for state '$inputState' but there are only ".
				"$symbolsCount symbols in alphabet. Expected transitions to be defined only for symbols in alphabet."
			);
		}
	}

	private function assertInputSymbolInAlphabet (string $symbol, string $input, int $position): void {
		$alphabet = $this->alphabet;

		if (!in_array($symbol, $alphabet, true)) {
			throw new \InvalidArgumentException(
				"Unknown symbol '$symbol' within input '$input' at position $position. ".
				"Expected symbol to be found in alphabet: ".implode(', ', $alphabet)
			);
		}
	}

	private function assertFinalStateAccepted (string $finalState, string $input): void {
		if (!in_array($finalState, $this->acceptedStates, true)) {
			throw new \DomainException(
				"Rejected final state '$finalState' for input '$input'. ".
				"Expected final state to be found in list of accepted states."
			);
		}
	}

	private static function assertUniqueNonEmptyStringArray (array $array, array $errorMessages): void {
		if (empty($array)) {
			throw new \InvalidArgumentException($errorMessages['arrayEmpty']);
		}

		$processedValues = [];

		foreach ($array as $value) {
			if (!is_string($value)) {
				throw new \InvalidArgumentException(sprintf($errorMessages['valueNotString'], $value));
			}

			if ($value === '') {
				throw new \InvalidArgumentException(sprintf($errorMessages['valueEmptyString'], $value));
			}

			if (in_array($value, $processedValues)) {
				throw new \InvalidArgumentException(sprintf($errorMessages['valueDuplicate'], $value));
			}

			$processedValues[] = $value;
		}
	}

	public function getAllowedStates (): array {
		return $this->allowedStates;
	}

	public function getAlphabet (): array {
		return $this->alphabet;
	}

	public function getInitialState (): string {
		return $this->initialState;
	}

	public function getAcceptedStates (): array {
		return $this->acceptedStates;
	}

	public function getTransitionFunction (): TransitionFunction {
		return $this->transitionFunction;
	}
}
