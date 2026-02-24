<?php

declare(strict_types=1);

namespace FSM;

use InvalidArgumentException;

/**
 * Represents the transition function (δ) of a finite state machine.
 *
 * The transition function maps (state, symbol) pairs to a next state,
 * defining how the machine moves between states as it processes input.
 *
 * Transitions are stored internally as a nested associative array:
 *   $transitions[inputState][inputSymbol] = nextState
 */
class TransitionFunction {

	/**
	 * Nested map of transitions: [inputState => [inputSymbol => nextState]].
	 *
	 * @var array<string, array<string, string>>
	 */
	private array $transitions = [];

	/**
	 * Returns the next state for the given input state and symbol.
	 *
	 * @param string $inputState  The current state of the machine.
	 * @param string $inputSymbol The input symbol being consumed.
	 *
	 * @throws InvalidArgumentException If no transition is defined for the given (state, symbol) pair.
	 *
	 * @return string The next state after consuming the symbol.
	 */
	public function execute (string $inputState, string $inputSymbol): string {
		if (isset($this->transitions[$inputState][$inputSymbol])) {
			$nextState = $this->transitions[$inputState][$inputSymbol];
			return $nextState;
		}

		throw new InvalidArgumentException(
			"Unknown transition with input state '$inputState' and input symbol '$inputSymbol'."
		);
	}

	/**
	 * Registers a transition from a state on a given symbol to a next state.
	 *
	 * Overwrites any existing transition for the same (state, symbol) pair.
	 *
	 * @param string $inputState  The state from which the transition originates.
	 * @param string $inputSymbol The symbol that triggers the transition.
	 * @param string $nextState   The state to transition into.
	 */
	public function addTransition (string $inputState, string $inputSymbol, string $nextState): void {
		if (!isset($this->transitions[$inputState])) {
			$this->transitions[$inputState] = [];
		}

		$this->transitions[$inputState][$inputSymbol] = $nextState;
	}

	/**
	 * Checks whether a transition is defined for the given (state, symbol) pair.
	 *
	 * @param string $inputState  The state to check.
	 * @param string $inputSymbol The symbol to check.
	 *
	 * @return bool True if a transition exists, false otherwise.
	 */
	public function hasTransition (string $inputState, string $inputSymbol): bool {
		return isset($this->transitions[$inputState][$inputSymbol]);
	}

	/**
	 * Checks whether any transitions are defined for the given state.
	 *
	 * @param string $inputState The state to check.
	 *
	 * @return bool True if at least one transition exists for this state, false otherwise.
	 */
	public function hasTransitionsForState (string $inputState): bool {
		return isset($this->transitions[$inputState]);
	}

	/**
	 * Returns the number of distinct states that have transitions registered.
	 *
	 * Used during FSM validation to confirm that no extra (unknown) states appear
	 * in the transition function beyond those in the allowed states list.
	 *
	 * @return int The number of states with at least one outgoing transition.
	 */
	public function getStatesCount (): int {
		return count($this->transitions);
	}

	/**
	 * Returns the number of transitions defined for a specific state.
	 *
	 * Used during FSM validation to confirm that no extra symbols appear in the
	 * transition function for a given state beyond those in the alphabet.
	 *
	 * @param string $inputState The state whose transitions should be counted.
	 *
	 * @return int The number of outgoing transitions for the state, or 0 if none exist.
	 */
	public function getTransitionsCountForState (string $inputState): int {
		return count($this->transitions[$inputState] ?? []);
	}
}
