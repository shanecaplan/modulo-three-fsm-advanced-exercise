<?php

/**
 * Provides a shared set of binary number strings for use across test data providers.
 *
 * The dataset is designed to give broad coverage when testing machines that operate
 * on binary input (e.g. {@see ModMachine}, {@see ModThreeMachine}). It is intentionally
 * static so that all test classes reference the same canonical inputs, making failures
 * easy to reproduce and compare across test suites.
 */
class BinaryData {

	/**
	 * Returns a comprehensive set of binary strings for use in data-driven tests.
	 *
	 * The set is organised into the following groups:
	 *
	 *  - **1–5 bit exhaustive coverage** — every possible binary string of length 1
	 *    through 5, ensuring all 2^N combinations are exercised for small widths.
	 *
	 *  - **Larger inputs** — a selection of longer strings (up to 12 bits) with
	 *    varied patterns (mixed bits, all ones, all zeros, powers of two) to exercise
	 *    the machine across a wider numeric range without enumerating every possibility.
	 *
	 *  - **Leading zeros** — strings such as '0001' and '00101' to confirm that
	 *    leading zeros are handled correctly and do not affect the computed value.
	 *    Note: several of these are already present in the exhaustive 1–5 bit groups;
	 *    they are repeated here to make the intent explicit in isolation.
	 *
	 *  - **Empty string** — represents the integer 0. Included to verify that machines
	 *    handle zero-length input gracefully (expected result: 0 mod N = 0).
	 *
	 * @return string[] A flat list of binary strings.
	 */
	public static function getComprehensiveBinaryNumbers (): array {
		return array(
			// ---- 1 bit (2 values: 0–1) ----
			'0',
			'1',

			// ---- 2 bits (4 values: 0–3) ----
			'00',
			'01',
			'10',
			'11',

			// ---- 3 bits (8 values: 0–7) ----
			'000',
			'001',
			'010',
			'011',
			'100',
			'101',
			'110',
			'111',

			// ---- 4 bits (16 values: 0–15) ----
			'0000',
			'0001',
			'0010',
			'0011',
			'0100',
			'0101',
			'0110',
			'0111',
			'1000',
			'1001',
			'1010',
			'1011',
			'1100',
			'1101',
			'1110',
			'1111',

			// ---- 5 bits (32 values: 0–31) ----
			'00000',
			'00001',
			'00010',
			'00011',
			'00100',
			'00101',
			'00110',
			'00111',
			'01000',
			'01001',
			'01010',
			'01011',
			'01100',
			'01101',
			'01110',
			'01111',
			'10000',
			'10001',
			'10010',
			'10011',
			'10100',
			'10101',
			'10110',
			'10111',
			'11000',
			'11001',
			'11010',
			'11011',
			'11100',
			'11101',
			'11110',
			'11111',

			// ---- Larger inputs (selected values, not exhaustive) ----
			// Mixed-bit patterns of varying lengths.
			'100101',    // 37
			'101101',    // 45
			'110101',    // 53
			'11111111',  // 255  — all ones, 8 bits
			'100000000', // 256  — power of two (2^8)
			'101010101', // 341  — alternating bits, 9 bits
			'1111101000', // 1000 — a round decimal value in binary
			'10000000000', // 1024 — power of two (2^10)
			'10010010011', // 1171 — mixed pattern, 11 bits
			'000000000000', // 0    — all zeros, 12 bits
			'111111111111', // 4095 — all ones,  12 bits

			// ---- Leading zeros (edge cases) ----
			// Confirms that a leading zero does not alter the numeric value.
			// Note: some of these already appear in the exhaustive groups above;
			// they are listed again here to make the leading-zero intent explicit.
			'0000',  // 0  (same value as '0')
			'0001',  // 1  (same value as '1')
			'00001', // 1  (same value as '1', extra leading zero)
			'00011', // 3  (same value as '11')
			'00101', // 5  (same value as '101')

			// ---- Empty string (edge case) ----
			// An empty string represents the integer 0; machines should return 0 mod N.
			''
		);
	}
}
