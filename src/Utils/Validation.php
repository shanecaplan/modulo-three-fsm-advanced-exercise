<?php

declare(strict_types=1);

namespace FSM\Utils;

class Validation {

	/**
	 * Returns true if the string contains only '0' and '1' characters.
	 *
	 * An empty string is considered valid and represents the integer 0.
	 *
	 * @param string $binary The string to test.
	 *
	 * @return bool True if $binary is a valid binary string (including empty), false otherwise.
	 */
	public static function isBinaryString (string $binary): bool {
		return preg_match('/^[01]*$/', $binary) === 1;
	}

}
