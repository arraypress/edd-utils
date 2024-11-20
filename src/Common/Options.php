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

namespace ArrayPress\EDD\Common;

use ArrayPress\Utils\Common\Arr;

class Options {

	/**
	 * Get the country options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of country options with label and value.
	 */
	public static function get_countries( bool $sort = true ): array {
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

	/**
	 * Get the region options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of region options grouped by country with nested states.
	 */
	public static function get_states( bool $sort = true ): array {
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

	/**
	 * Get the currency options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of currency options with label and value.
	 */
	public static function get_currencies( bool $sort = true ): array {
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

	/**
	 * Get the payment gateway options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of payment gateway options with label and value.
	 */
	public static function get_gateways( bool $sort = true ): array {
		$all_gateways = edd_get_payment_gateways();

		if ( empty( $all_gateways ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_gateways as $key => $gateway ) {
			$options[] = [
				'label' => esc_html( $gateway['admin_label'] ),
				'value' => esc_attr( $key ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

	/**
	 * Get the payment status options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of payment status options with label and value.
	 */
	public static function get_payment_statuses( bool $sort = true ): array {
		$all_statuses = edd_get_payment_statuses();

		if ( empty( $all_statuses ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_statuses as $status => $label ) {
			$options[] = [
				'label' => esc_html( $label ),
				'value' => esc_attr( $status ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

}