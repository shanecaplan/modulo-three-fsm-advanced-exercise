<?php

declare(strict_types=1);

namespace FSM;

use DomainException;
use InvalidArgumentException;

/**
 * A deterministic finite state machine (DFA).
 *
 * A DFA is formally defined by a 5-tuple (Q, Σ, δ, q₀, F):
 *   - Q  — a finite set of states ($allowedStates)
 *   - Σ  — a finite input alphabet ($alphabet)
 *   - δ  — a transition function δ: Q × Σ → Q ($transitionFunction)
 *   - q₀ — the initial state ($initialState)
 *   - F  — a set of accepted (final) states ($acceptedStates)
 *
 * The constructor validates that all five components are mutually consistent
 * before the machine is used. {@see execute()} then processes an input string
 * symbol by symbol and returns the final state if it is an accepted state,
 * or throws if the input is rejected.
 */
class FiniteStateMachine {

	/** @var string[] The complete set of valid states (Q). */
	private array $allowedStates;

	/** @var string[] The input alphabet (Σ) — the set of symbols the machine can read. */
	private array $alphabet;

	/** The state in which the machine starts processing input (q₀). */
	private string $initialState;

	/** @var string[] The subset of allowed states that are accepting/final states (F). */
	private array $acceptedStates;

	/** The transition function δ: Q × Σ → Q, mapping (state, symbol) pairs to next states. */
	private TransitionFunction $transitionFunction;

	/**
	 * Constructs a DFA and validates its definition.
	 *
	 * All five DFA components are validated to ensure they are internally
	 * consistent, for example: the initial state must be in $allowedStates,
	 * all accepted states must be in $allowedStates, and every (state, symbol)
	 * pair in Q × Σ must have exactly one transition defined.
	 *
	 * @param string[]           $allowedStates     The finite set of states Q.
	 * @param string[]           $alphabet          The input alphabet Σ.
	 * @param string             $initialState      The initial state q₀.
	 * @param string[]           $acceptedStates    The set of accepting states F.
	 * @param TransitionFunction $transitionFunction The transition function δ.
	 *
	 * @throws InvalidArgumentException If any component of the DFA definition is invalid.
	 */
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

	/**
	 * Runs the machine on the given input string and returns the final state.
	 *
	 * Starting from the initial state, each character of the input is consumed
	 * in order. After all characters are processed, the resulting state must be
	 * an accepted state; otherwise a {@see \DomainException} is thrown.
	 *
	 * @param string $input The string to process, composed of symbols from the alphabet.
	 *
	 * @throws InvalidArgumentException If the input contains a symbol not in the alphabet.
	 * @throws DomainException          If the final state is not an accepted state.
	 *
	 * @return string The accepted final state after processing the entire input.
	 */
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

	// -------------------------------------------------------------------------
	// Validation helpers
	// -------------------------------------------------------------------------

	/**
	 * Validates that the alphabet is a non-empty array of unique, non-empty strings.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertAlphabetValid (): void {
		$this->assertUniqueNonEmptyStringArray($this->alphabet, [
			'arrayEmpty' => "Invalid alphabet array. Expected non-empty array.",
			'valueNotString' => "Invalid type for alphabet symbol: %s. Expected string.",
			'valueEmptyString' => "Invalid value for symbol '%s' in alphabet. Expected non-empty string.",
			'valueDuplicate' => "Duplicate symbol '%s' in alphabet. Expected alphabet symbols to be unique.",
		]);
	}

	/**
	 * Validates that the allowed states list is a non-empty array of unique, non-empty strings.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertAllowedStatesValid (): void {
		$this->assertUniqueNonEmptyStringArray($this->allowedStates, [
			'arrayEmpty' => "Invalid allowed states array. Expected non-empty array.",
			'valueNotString' => "Invalid type for allowed state: %s. Expected string.",
			'valueEmptyString' => "Invalid value for allowed state '%s'. Expected non-empty string.",
			'valueDuplicate' => "Duplicate allowed state '%s'. Expected allowed states to be unique.",
		]);
	}

	/**
	 * Validates that every accepted state is a member of the allowed states list.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertAcceptedStatesValid (): void {
		$acceptedStates = $this->acceptedStates;

		foreach ($acceptedStates as $acceptedState) {
			$this->assertStateAllowed(
				$acceptedState,
				"Unknown accepted state '%s'. Expected accepted state to be found in list of allowed states."
			);
		}
	}

	/**
	 * Validates that the initial state is a member of the allowed states list.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertInitialStateValid (): void {
		$this->assertStateAllowed(
			$this->initialState,
			"Unknown initial state '%s'. Expected initial state to be found in list of allowed states."
		);
	}

	/**
	 * Asserts that a given state is present in the allowed states list.
	 *
	 * @param string $state        The state to check.
	 * @param string $errorMessage A sprintf-compatible message accepting the state as %s.
	 *
	 * @throws InvalidArgumentException If the state is not in the allowed states list.
	 */
	private function assertStateAllowed (string $state, string $errorMessage): void {
		if (!in_array($state, $this->allowedStates, true)) {
			throw new InvalidArgumentException(sprintf($errorMessage, $state));
		}
	}

	/**
	 * Validates that the transition function provides exactly one transition for every
	 * (state, symbol) pair in Q × Σ, and that it references no states or symbols
	 * outside of the allowed states list and alphabet.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertTransitionFunctionValid (): void {
		$transitionFunction = $this->transitionFunction;
		$allowedStates = $this->allowedStates;

		foreach ($allowedStates as $inputState) {
			if (!$transitionFunction->hasTransitionsForState($inputState)) {
				throw new InvalidArgumentException("Transitions not defined for state '$inputState'.");
			}

			$this->assertTransitionsForStateValid($transitionFunction, $inputState);
		}

		// Guard against the transition function referencing states not in $allowedStates.
		$transitionsStatesCount = $transitionFunction->getStatesCount();
		$allowedStatesCount = count($allowedStates);

		if ($transitionsStatesCount > $allowedStatesCount) {
			throw new InvalidArgumentException(
				"Transitions exist for $transitionsStatesCount states but there are only $allowedStatesCount allowed ".
				"states. Expected transitions to be defined only for allowed states."
			);
		}
	}

	/**
	 * Validates that for a specific state, a transition exists for every alphabet symbol,
	 * each transition leads to an allowed state, and no extra symbols are defined.
	 *
	 * @param TransitionFunction $transitionFunction The transition function to validate.
	 * @param string             $inputState         The state whose transitions are being checked.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertTransitionsForStateValid (TransitionFunction $transitionFunction, string $inputState): void {
		$alphabet = $this->alphabet;

		foreach ($alphabet as $inputSymbol) {
			if (!$transitionFunction->hasTransition($inputState, $inputSymbol)) {
				throw new InvalidArgumentException(
					"Missing transition for state '$inputState' and symbol '$inputSymbol'. ".
					"Expected transition to be defined."
				);
			}

			$nextState = $transitionFunction->execute($inputState, $inputSymbol);

			if (!in_array($nextState, $this->allowedStates, true)) {
				throw new InvalidArgumentException(
					"Transition for state '$inputState' and symbol '$inputSymbol' leads to an invalid next state of ".
					"'$nextState'. Expected next state to be in list of allowed states."
				);
			}
		}

		// Guard against the transition function referencing symbols not in the alphabet for this state.
		$transitionsCountForState = $transitionFunction->getTransitionsCountForState($inputState);
		$symbolsCount = count($alphabet);

		if ($transitionsCountForState > $symbolsCount) {
			throw new InvalidArgumentException(
				"There are $transitionsCountForState transitions for state '$inputState' but there are only ".
				"$symbolsCount symbols in alphabet. Expected transitions to be defined only for symbols in alphabet."
			);
		}
	}

	/**
	 * Asserts that a symbol encountered during input processing belongs to the alphabet.
	 *
	 * @param string $symbol   The symbol read from the input.
	 * @param string $input    The full input string (used in the error message).
	 * @param int    $position The zero-based index of $symbol within $input.
	 *
	 * @throws InvalidArgumentException
	 */
	private function assertInputSymbolInAlphabet (string $symbol, string $input, int $position): void {
		$alphabet = $this->alphabet;

		if (!in_array($symbol, $alphabet, true)) {
			throw new InvalidArgumentException(
				"Unknown symbol '$symbol' within input '$input' at position $position. ".
				"Expected symbol to be found in alphabet: ".implode(', ', $alphabet)
			);
		}
	}

	/**
	 * Asserts that the machine's final state after processing the input is an accepted state.
	 *
	 * @param string $finalState The state the machine is in after consuming the full input.
	 * @param string $input      The input string that was processed (used in the error message).
	 *
	 * @throws DomainException If the final state is not in the accepted states list.
	 */
	private function assertFinalStateAccepted (string $finalState, string $input): void {
		if (!in_array($finalState, $this->acceptedStates, true)) {
			throw new DomainException(
				"Rejected final state '$finalState' for input '$input'. ".
				"Expected final state to be found in list of accepted states."
			);
		}
	}

	/**
	 * Validates that an array is non-empty and contains only unique, non-empty strings.
	 *
	 * The $errorMessages array must contain the following keys, each holding a
	 * human-readable message (sprintf-formatted where noted):
	 *   - 'arrayEmpty'      — used when $array is empty
	 *   - 'valueNotString'  — sprintf with the offending value
	 *   - 'valueEmptyString'— sprintf with the offending value
	 *   - 'valueDuplicate'  — sprintf with the duplicate value
	 *
	 * @param array    $array         The array to validate.
	 * @param string[] $errorMessages Keyed error message templates.
	 *
	 * @throws InvalidArgumentException
	 */
	private static function assertUniqueNonEmptyStringArray (array $array, array $errorMessages): void {
		if (empty($array)) {
			throw new InvalidArgumentException($errorMessages['arrayEmpty']);
		}

		$processedValues = [];

		foreach ($array as $value) {
			if (!is_string($value)) {
				throw new InvalidArgumentException(sprintf($errorMessages['valueNotString'], $value));
			}

			if ($value === '') {
				throw new InvalidArgumentException(sprintf($errorMessages['valueEmptyString'], $value));
			}

			if (in_array($value, $processedValues)) {
				throw new InvalidArgumentException(sprintf($errorMessages['valueDuplicate'], $value));
			}

			$processedValues[] = $value;
		}
	}

	// -------------------------------------------------------------------------
	// Getters
	// -------------------------------------------------------------------------

	/** @return string[] The complete set of allowed states (Q). */
	public function getAllowedStates (): array {
		return $this->allowedStates;
	}

	/** @return string[] The input alphabet (Σ). */
	public function getAlphabet (): array {
		return $this->alphabet;
	}

	/** Returns the initial state (q₀). */
	public function getInitialState (): string {
		return $this->initialState;
	}

	/** @return string[] The set of accepted/final states (F). */
	public function getAcceptedStates (): array {
		return $this->acceptedStates;
	}

	/** Returns the transition function δ. */
	public function getTransitionFunction (): TransitionFunction {
		return $this->transitionFunction;
	}
}
