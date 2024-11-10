<?php
/**
 * Country Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Countries;

use ArrayPress\Utils\Common\Arr;

class Countries {

	/**
	 * Get the country options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of country options with label and value.
	 */
	public static function get_options( bool $sort = true ): array {
		$all_countries = edd_get_country_list();

		if ( empty( $all_countries ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_countries as $country_code => $country_name ) {
			if ( empty( $country_code ) || empty( $country_name ) ) {
				continue;
			}

			$options[] = [
				'label' => esc_html( html_entity_decode( $country_name ) ),
				'value' => esc_attr( $country_code ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

}