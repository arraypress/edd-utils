<?php
/**
 * Region Utilities for Easy Digital Downloads (EDD)
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

class Regions {

	/**
	 * Get the region options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of region options grouped by country with nested states.
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

			$states = edd_get_shop_states( $country_code );

			if ( ! empty( $states ) ) {
				$country_options = [
					'label'   => esc_html( html_entity_decode( $country_name ) ),
					'options' => [],
				];

				foreach ( $states as $state_code => $state_name ) {
					if ( empty( $state_code ) || empty( $state_name ) ) {
						continue;
					}

					$country_options['options'][] = [
						'label' => esc_html( html_entity_decode( $state_name ) ),
						'value' => esc_attr( $state_code ),
					];
				}

				if ( ! empty( $country_options['options'] ) ) {
					$options[] = $country_options;
				}
			}
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
			foreach ( $options as &$country ) {
				$country['options'] = Arr::sort_by_column( $country['options'], 'label' );
			}
		}

		return $options;
	}

}