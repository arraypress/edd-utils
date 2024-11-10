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

use EDD_Download;

trait Price {
	/**
	 * Required trait method for getting validated download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null
	 */
	abstract protected static function get_validated( int $download_id = 0 ): ?EDD_Download;

	/**
	 * Get the raw price of a download as a float.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional price ID for variable-priced downloads
	 *
	 * @return float Returns the price as a float, 0.00 if not found
	 */
	public static function get_price( int $download_id = 0, ?int $price_id = null ): float {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return 0.00;
		}

		if ( edd_has_variable_prices( $download_id ) ) {
			return self::get_variable_price( $download_id, $price_id );
		}

		$price = $download->get_price();

		return round( $price, edd_currency_decimal_filter() );
	}

	/**
	 * Get variable price based on price ID or default options.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Price ID
	 *
	 * @return float Price amount
	 */
	public static function get_variable_price( int $download_id, ?int $price_id ): float {
		$prices = edd_get_variable_prices( $download_id );

		// Specific price ID exists
		if ( ! is_null( $price_id ) && isset( $prices[ $price_id ] ) ) {
			$price = edd_get_price_option_amount( $download_id, $price_id );
		} // Default price exists
		elseif ( $default_id = edd_get_default_variable_price( $download_id ) ) {
			$price = edd_get_price_option_amount( $download_id, $default_id );
		} // Fallback to lowest price
		else {
			$price = edd_get_lowest_price_option( $download_id );
		}

		return round( (float) $price, edd_currency_decimal_filter() );
	}

	/**
	 * Get the filtered price with EDD's standard price filters applied.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional price ID for variable-priced downloads
	 *
	 * @return float Returns the filtered price as a float
	 */
	public static function get_filtered_price( int $download_id = 0, ?int $price_id = null ): float {
		$price = self::get_price( $download_id, $price_id );

		return (float) apply_filters( 'edd_download_price', $price, $download_id, $price_id );
	}

}