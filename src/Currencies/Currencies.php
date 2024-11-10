<?php
/**
 * Currency Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Currencies;

use ArrayPress\Utils\Common\Arr;

class Currencies {

	/**
	 * Get the currency options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of currency options with label and value.
	 */
	public static function get_options( bool $sort = true ): array {
		$all_currencies = edd_get_currencies();

		if ( empty( $all_currencies ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_currencies as $key => $currency ) {
			$options[] = [
				'label' => esc_html( html_entity_decode( $currency ) ),
				'value' => esc_attr( $key ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

}