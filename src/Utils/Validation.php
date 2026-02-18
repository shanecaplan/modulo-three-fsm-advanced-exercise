<?php

declare(strict_types=1);

namespace FSM\Utils;

class Validation {

	public static function isBinaryString (string $binary): bool {
		return preg_match('/^[01]*$/', $binary) === 1;
	}

}
