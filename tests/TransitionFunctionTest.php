<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FSM\TransitionFunction;

/**
 * Unit tests for {@see TransitionFunction}.
 *
 * Covers adding and overwriting transitions, querying their existence,
 * executing transitions, and counting states and transitions.
 */
class TransitionFunctionTest extends TestCase {

	/**
	 * A freshly registered transition should immediately be discoverable via hasTransition().
	 */
	public function testAddTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransition('S0', 'a'));
	}

	/**
	 * execute() should return the correct next state for each registered (state, symbol) pair.
	 */
	public function testExecuteTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S1', 'b', 'S2');
		$tf->addTransition('S2', 'c', 'S0');

		$this->assertSame('S1', $tf->execute('S0', 'a'));
		$this->assertSame('S2', $tf->execute('S1', 'b'));
		$this->assertSame('S0', $tf->execute('S2', 'c'));
	}

	/**
	 * hasTransition() should return true only for exact (state, symbol) pairs that were registered.
	 *
	 * Checks that a different symbol on the same state, and the same symbol on a different
	 * state, each return false.
	 */
	public function testHasTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransition('S0', 'a'));   // registered pair
		$this->assertFalse($tf->hasTransition('S0', 'b'));  // wrong symbol
		$this->assertFalse($tf->hasTransition('S1', 'a'));  // wrong state
	}

	/**
	 * hasTransitionsForState() should return true only for states that have at least one
	 * outgoing transition registered.
	 *
	 * Note: S1 is a *target* state of a registered transition but has no transitions
	 * of its own, so hasTransitionsForState('S1') must return false.
	 */
	public function testHasTransitionsForState (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransitionsForState('S0'));   // has outgoing transitions
		$this->assertFalse($tf->hasTransitionsForState('S1'));  // target only, no outgoing transitions
	}

	/**
	 * Attempting to execute an unregistered (state, symbol) pair should throw
	 * an InvalidArgumentException with a descriptive message.
	 */
	public function testUndefinedTransitionThrowsExceptionWhenExecuted (): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Unknown transition with input state 'S0' and input symbol 'x'.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$tf->execute('S0', 'x');
	}

	/**
	 * getStatesCount() should return the number of distinct states that have at least
	 * one outgoing transition registered, regardless of how many transitions each has.
	 */
	public function testGetStatesCount (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S0', 'b', 'S2');
		$tf->addTransition('S1', 'a', 'S0');
		$tf->addTransition('S1', 'b', 'S2');
		$tf->addTransition('S2', 'a', 'S1');
		$tf->addTransition('S2', 'b', 'S0');

		$this->assertSame(3, $tf->getStatesCount());
	}

	/**
	 * getTransitionsCountForState() should return the exact number of outgoing transitions
	 * registered for a given state, independently for each state.
	 */
	public function testGetTransitionsCountForState (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');
		$tf->addTransition('S0', 'b', 'S2');
		$tf->addTransition('S1', 'a', 'S0');

		$this->assertSame(2, $tf->getTransitionsCountForState('S0'));
		$this->assertSame(1, $tf->getTransitionsCountForState('S1'));
	}

	/**
	 * Calling addTransition() with the same (state, symbol) pair a second time should
	 * silently overwrite the previously registered next state.
	 */
	public function testOverwritingTransition (): void {
		$tf = new TransitionFunction();

		$tf->addTransition('S0', 'a', 'S1');
		$this->assertSame('S1', $tf->execute('S0', 'a'));

		// Same (state, symbol) pair registered again with a different next state.
		$tf->addTransition('S0', 'a', 'S2');
		$this->assertSame('S2', $tf->execute('S0', 'a'));
	}

	/**
	 * A single state may have multiple outgoing transitions, one per symbol.
	 * Each should resolve independently to its own next state.
	 */
	public function testMultipleTransitionsFromSameState (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', '0', 'S1');
		$tf->addTransition('S0', '1', 'S2');
		$tf->addTransition('S0', '2', 'S3');

		$this->assertSame('S1', $tf->execute('S0', '0'));
		$this->assertSame('S2', $tf->execute('S0', '1'));
		$this->assertSame('S3', $tf->execute('S0', '2'));
	}

	/**
	 * A transition where the next state is the same as the input state (a self-loop)
	 * should be stored and executed correctly.
	 */
	public function testSelfLoopingTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');

		$this->assertSame('S0', $tf->execute('S0', 'a'));
	}
}
