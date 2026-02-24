<?php

declare(strict_types=1);

namespace FSM;

use FSM\Utils\Validation;

/**
 * A DFA-based machine that computes the remainder of a binary number modulo N.
 *
 * Given a binary string representing a non-negative integer, this machine returns
 * the remainder when that integer is divided by the configured modulus.
 *
 * How it works
 * ------------
 * The machine has N states, labelled '0' through 'N-1', each representing a
 * possible remainder value. Reading a '0' or '1' bit shifts the accumulated
 * value left (equivalent to multiplying by 2) and optionally adds 1:
 *
 *   nextRemainder = (currentRemainder * 2 + bit) mod N
 *
 * The transition function is pre-computed for every (remainder, bit) pair.
 * All N states are both allowed and accepted, so the machine always produces
 * a result for any valid binary input.
 *
 * Example (modulus = 3, input = "1101" = 13):
 *   State '0' --1--> State '1' --1--> State '0' --0--> State '0' --1--> State '1'
 *   Result: 1  (because 13 mod 3 = 1)
 */
class ModMachine {

	/** The underlying DFA that processes binary input. */
	private FiniteStateMachine $fsm;

	/**
	 * Constructs a modulo machine for the given modulus.
	 *
	 * @param int $modulus The divisor (must be greater than zero).
	 *
	 * @throws \InvalidArgumentException If $modulus is not a positive integer.
	 */
	public function __construct (int $modulus) {
		$this->assertModulusValid($modulus);

		$states = $this->resolveStates($modulus);
		$alphabet = ['0', '1'];
		$initialState = '0';
		$transitionFunction = $this->createTransitionFunction($modulus);

		// All states are accepted because the machine always terminates in a valid remainder state.
		$this->fsm = new FiniteStateMachine($states, $alphabet, $initialState, $states, $transitionFunction);
	}

	/**
	 * Processes a binary string and returns its value modulo the configured modulus.
	 *
	 * @param string $binary A string containing only '0' and '1' characters.
	 *
	 * @throws \InvalidArgumentException If $binary contains characters other than '0' and '1'.
	 *
	 * @return int The remainder of the binary number divided by the modulus (0 ≤ result < modulus).
	 */
	public function execute (string $binary): int {
		if (!Validation::isBinaryString($binary)) {
			throw new \InvalidArgumentException("Invalid binary string '$binary'. Expected only '0' or '1' characters.");
		}

		$acceptedState = $this->fsm->execute($binary);
		$remainder = (int) $acceptedState;

		return $remainder;
	}

	/**
	 * Validates that the modulus is a positive integer.
	 *
	 * @throws \InvalidArgumentException If $modulus is zero or negative.
	 */
	private function assertModulusValid (int $modulus): void {
		if ($modulus <= 0) {
			throw new \InvalidArgumentException("Invalid modulus $modulus. Expected modulus to be greater than zero.");
		}
	}

	/**
	 * Builds the list of state labels for the given modulus.
	 *
	 * States are the string representations of integers from 0 to $modulus - 1,
	 * each corresponding to a possible remainder value.
	 *
	 * @param int $modulus The modulus (and therefore the number of states).
	 *
	 * @return string[] E.g. ['0', '1', '2'] for modulus 3.
	 */
	private function resolveStates (int $modulus): array {
		return array_map('strval', range(0, $modulus - 1));
	}

	/**
	 * Builds the transition function for the modulo DFA.
	 *
	 * The implementation iterates over all (remainder, symbol) pairs in a fixed
	 * order using a running counter ($nextRemainder) that cycles through
	 * [0, modulus - 1], which is equivalent to the following formula:
	 * nextRemainder = (r * 2 + symbol) mod modulus
	 *
	 * Of note is that PHP's mod operator is not actually used.
	 *
	 * @param int $modulus The modulus used to calculate next states.
	 *
	 * @return TransitionFunction A fully-populated transition function for the DFA.
	 */
	private function createTransitionFunction (int $modulus): TransitionFunction {
		$transitionFunction = new TransitionFunction();

		// $nextRemainder cycles from 0 to $modulus - 1 across all (remainder, symbol) pairs.
		// This works because iterating remainder from 0..N-1 with symbols 0 and 1 produces
		// all values of (remainder * 2 + symbol) mod N in ascending order.
		$nextRemainder = 0;

		for ($remainder = 0; $remainder < $modulus; $remainder++) {
			$inputState = (string) $remainder;

			for ($symbol = 0; $symbol <= 1; $symbol++) {
				$inputSymbol = (string) $symbol;

				$nextState = (string) $nextRemainder++;

				// Wrap the counter back to 0 once all remainders have been covered.
				if ($nextRemainder === $modulus) {
					$nextRemainder = 0;
				}

				$transitionFunction->addTransition($inputState, $inputSymbol, $nextState);
			}
		}

		return $transitionFunction;
	}

	/** Returns the underlying {@see FiniteStateMachine} instance. */
	public function getFiniteStateMachine (): FiniteStateMachine {
		return $this->fsm;
	}
}
