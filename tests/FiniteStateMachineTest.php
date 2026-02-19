<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FSM\FiniteStateMachine;
use FSM\TransitionFunction;

class FiniteStateMachineTest extends TestCase {

	/**
	 * Test that empty alphabet throws exception.
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
	 * Test that non-string symbols in alphabet throws exception.
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
	 * Test that empty symbol (empty string) in alphabet throws exception.
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
	 * Test that duplicate symbol in alphabet throws exception.
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

	/**
	 * Test that empty allowed states throws exception.
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
	 * Test that non-string values in allowed states throws exception.
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
	 * Test that empty value in allowed states throws exception.
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
	 * Test that duplicate value in allowed states throws exception.
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

	/**
	 * Test that accepted states must be in allowed states.
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

	/**
	 * Test that initial state must be in allowed states.
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

	/**
	 * Test transitions not defined for state throws exception.
	 */
	public function testTransitionsNotDefinedForStateThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Transitions not defined for state 'S2'.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S1');
		$tf->addTransition('S1', 'a', 'S1');
		$tf->addTransition('S1', 'b', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);

		$fsm->execute('ac');
	}

	/**
	 * Test throws exception when number of states defined in TransitionFunction is greater than number of allowed states.
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
		$tf->addTransition('S3', 'a', 'S0');
		$tf->addTransition('S3', 'b', 'S1');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);

		$fsm->execute('ac');
	}

	/**
	 * Test transition not defined for state and symbol throws exception.
	 */
	public function testTransitionNotDefinedForStateAndSymbolThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Missing transition for state 'S0' and symbol 'b'. Expected transition to be defined.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'c', 'S0');

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * Test throws exception when number of transitions for state is greater than number of symbols in alphabet.
	 */
	public function testThrowsExceptionWhenNumberOfTransitionsForStateIsGreaterThanNumberOfSymbolsInAlphabet (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("There are 3 transitions for state 'S0' but there are only 2 symbols in alphabet. Expected transitions to be defined only for symbols in alphabet.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');
		$tf->addTransition('S0', 'b', 'S0');
		$tf->addTransition('S0', 'c', 'S0');

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a', 'b'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * Test transition leading to invalid state throws exception.
	 */
	public function testTransitionLeadingToInvalidStateThrowsException (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Transition for state 'S0' and symbol 'a' leads to an invalid next state of 'S1'. Expected next state to be in list of allowed states.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		new FiniteStateMachine(
			allowedStates: ['S0'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S0'],
			transitionFunction: $tf
		);
	}

	/**
	 * Test symbol not found in alphabet throws exception.
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

		$fsm->execute('ac');
	}

	/**
	 * Test final state that is not in accepted states throws exception.
	 */
	public function testFinalStateNotInAcceptedStatesThrowsException (): void {
		$this->expectException(\DomainException::class);
		$this->expectExceptionMessage("Rejected final state 'S0' for input 'aaa'. Expected final state to be found in list of accepted states.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S1', 'a', 'S2');
		$tf->addTransition('S2', 'a', 'S0');

		$fsm = new FiniteStateMachine(
			allowedStates: ['S0', 'S1', 'S2'],
			alphabet: ['a'],
			initialState: 'S0',
			acceptedStates: ['S1', 'S2'],
			transitionFunction: $tf
		);

		$fsm->execute('aaa');
	}

	/**
	 * Test instantiate with valid configuration.
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

	/**
	 * Test execute with valid input.
	 */
	public function testExecuteWithValidInput (): void {
		$tf = new TransitionFunction();

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
	 * Test execute with self looping input.
	 */
	public function testExecuteWithSelfLoopingInput (): void {
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

		$this->assertSame('S0', $fsm->execute('b'));
		$this->assertSame('S0', $fsm->execute('ba'));
		$this->assertSame('S0', $fsm->execute('aab'));
		$this->assertSame('S0', $fsm->execute('aaab'));
	}

	/**
	 * Test execute empty string input returns initial state.
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
}
