<?php
/**
 * Misc Translation Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\I18n;

class Misc {

	/**
	 * Get available units for the commission rate.
	 *
	 * @return array{array{label: string, value: string}}
	 */
	public static function get_amount_types(): array {
		$statuses = [
			[
				'label' => esc_html__( 'Percentage', 'arraypress' ),
				'value' => 'percentage'
			],
			[
				'label' => esc_html__( 'Flat', 'arraypress' ),
				'value' => 'flat'
			]
		];

		/**
		 * Filters the array of discount status options.
		 *
		 * @param array $statuses The array of status options.
		 */
		return apply_filters( 'arraypress_amount_types', $statuses );
	}

}