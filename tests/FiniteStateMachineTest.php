<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FSM\FiniteStateMachine;
use FSM\TransitionFunction;

/**
 * Unit tests for {@see FiniteStateMachine}.
 *
 * Tests are grouped into three broad areas:
 *
 *  1. Construction-time validation — verifies that each of the five DFA components
 *     (alphabet, allowed states, accepted states, initial state, transition function)
 *     is validated correctly and throws {@see \InvalidArgumentException} on bad input.
 *
 *  2. Happy-path construction — verifies that a well-formed DFA can be instantiated
 *     and that its getters return the values passed to the constructor.
 *
 *  3. Input execution — verifies that execute() processes input strings correctly,
 *     returns the right final state for accepted input, and throws the right exceptions
 *     for invalid symbols or rejected final states.
 */
class FiniteStateMachineTest extends TestCase {

	// =========================================================================
	// Alphabet validation
	// =========================================================================

	/**
	 * An empty alphabet array must be rejected at construction time.
	 */
	public function testEmptyAlphabetThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid alphabet array. Expected non-empty array.');

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: [],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * All symbols in the alphabet must be strings; non-string values (e.g. int, bool)
	 * must be rejected at construction time.
	 */
	public function testNonStringSymbolsInAlphabetThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid type for alphabet symbol: 0. Expected string.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: [0, true, '2'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * The empty string is not a valid alphabet symbol and must be rejected at
	 * construction time, even when surrounded by valid symbols.
	 */
	public function testEmptySymbolInAlphabetThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid value for symbol '' in alphabet. Expected non-empty string.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b', '', 'd'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * All alphabet symbols must be unique; a duplicate symbol must be rejected
	 * at construction time.
	 */
	public function testDuplicateSymbolInAlphabetThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Duplicate symbol 'b' in alphabet. Expected alphabet symbols to be unique.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b', 'b', 'd'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	// =========================================================================
	// Allowed states validation
	// =========================================================================

	/**
	 * An empty allowed states array must be rejected at construction time.
	 */
	public function testEmptyAllowedStatesThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid allowed states array. Expected non-empty array.');

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: [],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * All state labels must be strings; non-string values (e.g. int, bool) in the
	 * allowed states array must be rejected at construction time.
	 */
	public function testNonStringValuesInAllowedStatesThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid type for allowed state: 2. Expected string.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 2, false],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * The empty string is not a valid state label and must be rejected at
	 * construction time, even when surrounded by valid state labels.
	 */
	public function testEmptyValueInAllowedStatesThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid value for allowed state ''. Expected non-empty string.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1', '', 'S3'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	/**
	 * All state labels must be unique; a duplicate in the allowed states array
	 * must be rejected at construction time.
	 */
	public function testDuplicateValueInAllowedStatesThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Duplicate allowed state 'S1'. Expected allowed states to be unique.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S1', 'S3'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	// =========================================================================
	// Accepted states validation
	// =========================================================================

	/**
	 * Every accepted state must appear in the allowed states list. A state that
	 * exists only in the accepted states list must be rejected at construction time.
	 */
	public function testAcceptedStatesMustBeInAllowedStates (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Unknown accepted state 'S2'. Expected accepted state to be found in list of allowed states.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S1', 'S2'],
			transitionFunction: $tf
		);
	}

	// =========================================================================
	// Initial state validation
	// =========================================================================

	/**
	 * The initial state must appear in the allowed states list. Passing a state
	 * label that is not in the allowed states list must be rejected at construction time.
	 */
	public function testInitialStateMustBeInAllowedStates (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Unknown initial state 'S2'. Expected initial state to be found in list of allowed states.");

		$tf = new TransitionFunction();

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1'],
			alphabet: ['a'],
			initialState: 'S2',
			acceptedStates: [],
			transitionFunction: $tf
		);
	}

	// =========================================================================
	// Transition function validation
	// =========================================================================

	/**
	 * Every allowed state must have at least one outgoing transition registered.
	 * A state in the allowed states list that has no transitions in the transition
	 * function must be rejected at construction time.
	 *
	 * Here S0 and S1 have full transitions but S2 has none.
	 */
	public function testTransitionsNotDefinedForStateThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Transitions not defined for state 'S2'.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S1');
		$tf->addTransition('S1', 'a', 'S1');
		$tf->addTransition('S1', 'b', 'S0');

		// S2 is an allowed state but has no outgoing transitions defined.
		new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * The transition function must not reference more states than there are allowed states.
	 * Here the transition function defines transitions for four states (S0–S3) but only
	 * three states are declared as allowed (S0–S2).
	 */
	public function testThrowsExceptionWhenNumberOfStatesDefinedInTransitionFunctionIsGreaterThanNumberOfAllowedStates (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Transitions exist for 4 states but there are only 3 allowed states. Expected transitions to be defined only for allowed states.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S1');
		$tf->addTransition('S1', 'a', 'S1');
		$tf->addTransition('S1', 'b', 'S0');
		$tf->addTransition('S2', 'a', 'S1');
		$tf->addTransition('S2', 'b', 'S2');
		$tf->addTransition('S3', 'a', 'S0'); // S3 is not in allowedStates
		$tf->addTransition('S3', 'b', 'S1');

		new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * For every allowed state, a transition must be defined for every alphabet symbol.
	 * Here S0 has a transition for 'a' and 'c' but is missing the required transition
	 * for 'b'.
	 */
	public function testTransitionNotDefinedForStateAndSymbolThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Missing transition for state 'S0' and symbol 'b'. Expected transition to be defined.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'c', 'S0'); // 'c' is not in the alphabet

		// 'b' is in the alphabet but has no transition for S0.
		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * The transition function must not define more transitions for a state than there
	 * are symbols in the alphabet. Here S0 has three transitions but the alphabet only
	 * contains two symbols.
	 */
	public function testThrowsExceptionWhenNumberOfTransitionsForStateIsGreaterThanNumberOfSymbolsInAlphabet (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("There are 3 transitions for state 'S0' but there are only 2 symbols in alphabet. Expected transitions to be defined only for symbols in alphabet.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S0');
		$tf->addTransition('S0', 'c', 'S0'); // 'c' is not in the alphabet

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * Every transition's next state must appear in the allowed states list.
	 * Here the transition for ('S0', 'a') leads to 'S1', which is not an allowed state.
	 */
	public function testTransitionLeadingToInvalidStateThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Transition for state 'S0' and symbol 'a' leads to an invalid next state of 'S1'. Expected next state to be in list of allowed states.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1'); // S1 is not in allowedStates

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	// =========================================================================
	// Happy-path construction
	// =========================================================================

	/**
	 * A fully valid DFA configuration should construct without throwing and expose
	 * all five components unchanged via the corresponding getters.
	 */
	public function testInstantiateWithValidConfiguration(): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S0', 'b', 'S0');
		$tf->addTransition('S1', 'a', 'S1');
		$tf->addTransition('S1', 'b', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S1'],
			transitionFunction: $tf
		);

		$this->assertInstanceOf(FiniteStateMachine::class, $fsm);

		$this->assertSame(['S0', 'S1'], $fsm->getAllowedStates());
		$this->assertSame(['a', 'b'], $fsm->getAlphabet());
		$this->assertSame('S0', $fsm->getInitialState());
		$this->assertSame(['S1'], $fsm->getAcceptedStates());
	}

	// =========================================================================
	// Input execution — success cases
	// =========================================================================

	/**
	 * execute() should follow transitions correctly and return the accepted final state.
	 *
	 * The machine under test has three states (S0, S1, S2) with an alphabet of {a, b}.
	 * Multiple inputs are tested to exercise different paths through the transition table.
	 */
	public function testExecuteWithValidInput (): void {
		$tf = new TransitionFunction();

		// Transition table:
		//        a     b
		// S0 -> S2   S1
		// S1 -> S0   S1
		// S2 -> S1   S0
		$tf->addTransition('S0', 'a', 'S2');
		$tf->addTransition('S0', 'b', 'S1');
		$tf->addTransition('S1', 'a', 'S0');
		$tf->addTransition('S1', 'b', 'S1');
		$tf->addTransition('S2', 'a', 'S1');
		$tf->addTransition('S2', 'b', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0', 'S1'],
			transitionFunction: $tf
		);

		$this->assertSame('S1', $fsm->execute('b'));
		$this->assertSame('S0', $fsm->execute('ba'));
		$this->assertSame('S1', $fsm->execute('aab'));
		$this->assertSame('S1', $fsm->execute('aaab'));
	}

	/**
	 * A machine in which every transition is a self-loop should always remain in
	 * its initial state, regardless of the input.
	 */
	public function testExecuteWithSelfLoopingInput (): void {
		$tf = new TransitionFunction();

		// Both transitions loop back to S0.
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);

		$this->assertSame('S0', $fsm->execute('b'));
		$this->assertSame('S0', $fsm->execute('ba'));
		$this->assertSame('S0', $fsm->execute('aab'));
		$this->assertSame('S0', $fsm->execute('aaab'));
	}

	/**
	 * An empty string input should cause no transitions to be executed, leaving the
	 * machine in its initial state. Provided the initial state is accepted, execute()
	 * should return it without throwing.
	 */
	public function testExecuteEmptyStringInputReturnsInitialState (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S1', 'a', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);

		$this->assertSame('S0', $fsm->execute(''));
	}

	// =========================================================================
	// Input execution — failure cases
	// =========================================================================

	/**
	 * execute() should throw an InvalidArgumentException if the input contains a
	 * symbol that is not part of the alphabet.
	 *
	 * The error message should include the offending symbol, the full input string,
	 * and its zero-based position within the input.
	 */
	public function testSymbolNotFoundInAlphabetThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Unknown symbol 'c' within input 'ac' at position 1. Expected symbol to be found in alphabet: a, b");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);

		// 'c' at position 1 is not in the alphabet {a, b}.
		$fsm->execute('ac');
	}

	/**
	 * execute() should throw a DomainException if the machine ends in a state that
	 * is not in the accepted states list after consuming the entire input.
	 *
	 * Here the input 'aaa' cycles through S0 → S1 → S2 → S0, landing back on S0,
	 * which is not accepted.
	 */
	public function testFinalStateNotInAcceptedStatesThrowsException (): void {
		$this->expectException(\DomainException::class);
		$this->expectExceptionMessage("Rejected final state 'S0' for input 'aaa'. Expected final state to be found in list of accepted states.");

		$tf = new TransitionFunction();

		// Cycle: S0 -a-> S1 -a-> S2 -a-> S0
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S1', 'a', 'S2');
		$tf->addTransition('S2', 'a', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S1', 'S2'], // S0 is intentionally excluded
			transitionFunction: $tf
		);

		$fsm->execute('aaa');
	}
}
