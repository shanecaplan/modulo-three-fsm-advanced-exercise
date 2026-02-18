<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FSM\TransitionFunction;

class TransitionFunctionTest extends TestCase {

	/**
	 * Test adding a single transition.
	 */
	public function testAddTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransition('S0', 'a'));
	}

	/**
	 * Test execution of transition.
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
	 * Test hasTransition for existing and non-existing transitions.
	 */
	public function testHasTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransition('S0', 'a'));
		$this->assertFalse($tf->hasTransition('S0', 'b'));
		$this->assertFalse($tf->hasTransition('S1', 'a'));
	}

	/**
	 * Test hasTransitionsForState for existing and non-existing states.
	 */
	public function testHasTransitionsForState (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$this->assertTrue($tf->hasTransitionsForState('S0'));
		$this->assertFalse($tf->hasTransitionsForState('S1'));
	}

	/**
	 * Test undefined transition throws exception when executed.
	 */
	public function testUndefinedTransitionThrowsExceptionWhenExecuted (): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Unknown transition with input state 'S0' and input symbol 'x'.");

		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S1');

		$tf->execute('S0', 'x');
	}

	/**
	 * Test getting states count.
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
	 * Test getting transitions count for a specific state.
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
	 * Test overwriting a transition.
	 */
	public function testOverwritingTransition (): void {
		$tf = new TransitionFunction();

		// Add the transition S0.
		$tf->addTransition('S0', 'a', 'S1');
		$this->assertSame('S1', $tf->execute('S0', 'a'));

		// Overwrite the transition S0.
		$tf->addTransition('S0', 'a', 'S2');
		$this->assertSame('S2', $tf->execute('S0', 'a'));
	}

	/**
	 * Test multiple transitions from same state.
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
	 * Test self looping transition.
	 */
	public function testSelfLoopingTransition (): void {
		$tf = new TransitionFunction();
		$tf->addTransition('S0', 'a', 'S0');

		$this->assertSame('S0', $tf->execute('S0', 'a'));
	}
}
