<?php

declare(strict_types=1);

namespace FSM;

/**
 * A convenience DFA that computes the remainder of a binary number modulo 3.
 *
 * This is a thin specialisation of {@see ModMachine} with the modulus fixed to 3.
 * It has three states — '0', '1', and '2' — each representing a possible remainder.
 *
 * Transition table:
 *
 *   State | Symbol '0' → | Symbol '1' →
 *   ------|--------------|--------------
 *   '0'   |     '0'      |     '1'
 *   '1'   |     '2'      |     '0'
 *   '2'   |     '1'      |     '2'
 *
 * Example: input "1101" (decimal 13) → remainder 1  (13 mod 3 = 1)
 */
class ModThreeMachine extends ModMachine {

	/**
	 * Constructs a mod-3 machine.
	 */
	public function __construct() {
		parent::__construct(3);
	}

}
