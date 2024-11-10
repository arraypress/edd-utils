<?php
/**
 * Price Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides price-related functionality for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use EDD\Utils\ListHandler;
use EDD_Download;

trait VariablePrices {

	/**
	 * Required trait method for getting validated download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null
	 */
	abstract protected static function get_validated( int $download_id = 0 ): ?EDD_Download;

	/**
	 * Get the specified field value from a variable price option.
	 *
	 * @param int    $download_id Download ID
	 * @param int    $price_id    Price ID
	 * @param string $field       Field name to retrieve
	 *
	 * @return mixed|null Field value or null if not found
	 */
	public static function get_variable_price_field( int $download_id, int $price_id, string $field ) {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$prices = $download->get_prices();
		if ( ! is_array( $prices ) || empty( $price_id ) ) {
			return null;
		}

		$value = $prices[ $price_id ][ $field ] ?? null;

		// Filter & return
		return apply_filters( 'edd_get_download_variable_price_field', $value, $download_id, $price_id, $field );
	}

	/**
	 * Check if a variable-priced download has any free options.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return bool True if the download has free options
	 */
	public static function has_free_variable_price_option( int $download_id = 0 ): bool {
		$download = self::get_validated( $download_id );
		if ( ! $download || ! edd_has_variable_prices( $download_id ) ) {
			return false;
		}

		$prices = edd_get_variable_prices( $download_id );
		if ( empty( $prices ) ) {
			return false;
		}

		foreach ( $prices as $price ) {
			if ( empty( $price['amount'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the price ID with the highest amount.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return int|false Price ID or false if not found
	 */
	public static function get_highest_price_id( int $download_id = 0 ) {
		return self::get_price_id_by_field( $download_id );
	}

	/**
	 * Get the price ID with the lowest amount.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return int|false Price ID or false if not found
	 */
	public static function get_lowest_price_id( int $download_id = 0 ) {
		return self::get_price_id_by_field( $download_id, 'amount', 'min' );
	}

	/**
	 * Get the price ID with the highest/lowest value for a specific field.
	 *
	 * @param int    $download_id Download ID
	 * @param string $field       Field to compare (e.g., 'amount', 'licenses', etc.)
	 * @param string $type        Type of comparison ('min' or 'max')
	 *
	 * @return int|false Price ID or false if not found
	 */
	public static function get_price_id_by_field( int $download_id = 0, string $field = 'amount', string $type = 'max' ) {
		$download = self::get_validated( $download_id );
		if ( ! $download || ! edd_has_variable_prices( $download_id ) ) {
			return false;
		}

		if ( ! in_array( $type, [ 'min', 'max' ], true ) ) {
			return false;
		}

		$list_handler = new ListHandler( edd_get_variable_prices( $download_id ) );
		$key          = $list_handler->search( $field, $type );

		return $key !== false ? absint( $key ) : false;
	}

	/**
	 * Get the default variable price ID for a download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return int|false Default price ID or false if not found
	 */
	public static function get_default_variable_price( int $download_id = 0 ) {
		$download = self::get_validated( $download_id );
		if ( ! $download || ! edd_has_variable_prices( $download_id ) ) {
			return false;
		}

		$price = edd_get_default_variable_price( $download_id );
		if ( empty( $price ) ) {
			$price = edd_get_lowest_price_id( $download_id );
		}

		return $price;
	}

}