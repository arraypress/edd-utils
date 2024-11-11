<?php
/**
 * Adjustment Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Misc;

class Format {

	/**
	 * Formats rate based on the given type.
	 *
	 * @param float  $amount   Rate.
	 * @param string $type     Optional. Rate type. Accepts 'percentage' or 'flat'. Default 'percentage'.
	 * @param bool   $decimals Optional. Whether to use decimals. Default true.
	 *
	 * @return string Formatted rate string.
	 */
	public static function rate( float $amount, string $type = 'percentage', bool $decimals = true ): string {
		if ( $type === 'flat' ) {
			return self::currency( $amount );
		}

		return edd_format_amount( $amount, $decimals ) . '%';
	}

	/**
	 * Format currency amount.
	 *
	 * @param float|int $amount   The amount to format.
	 * @param string    $currency Optional. The currency code. Default is the store's currency.
	 *
	 * @return string Formatted currency string.
	 */
	public static function currency( $amount, string $currency = '' ): string {
		$formatted_amount = edd_format_amount( $amount );

		return edd_currency_filter( $formatted_amount, $currency );
	}

	/**
	 * Format a gateway label.
	 *
	 * @param string $gateway The gateway slug.
	 *
	 * @return string Formatted gateway label.
	 */
	public static function gateway_label( string $gateway ): string {
		return edd_get_gateway_admin_label( $gateway );
	}

}