<?php
/**
 * Gateway Statistics for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Gateways
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Gateways;

use ArrayPress\EDD\Date\Generate;
use EDD\Stats;
use Exception;

class Gateway {

	/**
	 * Get total earnings for a specific gateway.
	 *
	 * @param string      $gateway       Gateway ID (e.g., 'stripe', 'paypal')
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false
	 * @param bool        $formatted     Whether to return formatted amount. Default false
	 *
	 * @return float|string|false Returns:
	 *                           - float: When formatted is false (raw amount)
	 *                           - string: When formatted is true (formatted currency amount)
	 *                           - false: On error or invalid gateway
	 * @throws Exception When there's an error processing the stats
	 */
	public static function get_earnings( string $gateway, ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, bool $formatted = false ) {
		if ( empty( $gateway ) || ! self::validate_gateway( $gateway ) ) {
			return false;
		}

		$args = [
			'gateway'       => $gateway,
			'exclude_taxes' => $exclude_taxes,
			'output'        => $formatted ? 'formatted' : 'raw',
			'grouped'       => false // Ensure we don't get an array result
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats  = new Stats( $args );
		$result = $stats->get_gateway_earnings();

		// Type safety: ensure we get the expected type based on formatting
		if ( $formatted ) {
			return is_string( $result ) ? $result : edd_currency_filter( edd_format_amount( $result ) );
		}

		return is_numeric( $result ) ? (float) $result : 0.0;
	}

	/**
	 * Get total number of orders processed by a gateway.
	 *
	 * @param string      $gateway    Gateway ID (e.g., 'stripe', 'paypal')
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 *
	 * @return int|false Number of orders or false on error
	 * @throws Exception
	 */
	public static function get_order_count( string $gateway, ?string $start_date = null, ?string $end_date = null ) {
		if ( empty( $gateway ) || ! self::validate_gateway( $gateway ) ) {
			return false;
		}

		$args = [
			'gateway' => $gateway,
			'output'  => 'raw',
			'grouped' => false // Ensure we don't get an array result
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats = new Stats( $args );

		return $stats->get_gateway_sales();
	}

	/**
	 * Get refund amount for a specific gateway.
	 *
	 * @param string      $gateway       Gateway ID (e.g., 'stripe', 'paypal')
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false
	 * @param bool        $formatted     Whether to return formatted amount. Default false
	 *
	 * @return float|string|false Returns:
	 *                           - float: When formatted is false (raw amount)
	 *                           - string: When formatted is true (formatted currency amount)
	 *                           - false: On error or invalid gateway
	 * @throws Exception When there's an error processing the stats
	 */
	public static function get_refund_amount(
		string $gateway,
		?string $start_date = null,
		?string $end_date = null,
		bool $exclude_taxes = false,
		bool $formatted = false
	) {
		if ( empty( $gateway ) || ! self::validate_gateway( $gateway ) ) {
			return false;
		}

		$args = [
			'gateway'       => $gateway,
			'exclude_taxes' => $exclude_taxes,
			'output'        => $formatted ? 'formatted' : 'raw',
			'grouped'       => false // Ensure we don't get an array result
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats  = new Stats( $args );
		$result = $stats->get_gateway_refund_amount();

		// Type safety: ensure we get the expected type based on formatting
		if ( $formatted ) {
			return is_string( $result ) ? $result : edd_currency_filter( edd_format_amount( $result ) );
		}

		return is_numeric( $result ) ? (float) $result : 0.0;
	}

	/**
	 * Get refund rate for a specific gateway.
	 *
	 * @param string      $gateway    Gateway ID (e.g., 'stripe', 'paypal')
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 *
	 * @return float|false Refund rate as percentage or false on error
	 * @throws Exception
	 */
	public static function get_refund_rate( string $gateway, ?string $start_date = null, ?string $end_date = null ): ?float {
		if ( empty( $gateway ) || ! self::validate_gateway( $gateway ) ) {
			return null;
		}

		$args = [
			'gateway' => $gateway,
			'type'    => 'refund',
			'output'  => 'raw',
			'grouped' => false // Ensure we don't get an array result
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats        = new Stats( $args );
		$refund_count = $stats->get_gateway_sales();
		$total_count  = self::get_order_count( $gateway, $start_date, $end_date );

		if ( $total_count === 0 || $total_count === false ) {
			return 0.0;
		}

		return round( ( $refund_count / $total_count ) * 100, 2 );
	}

	/**
	 * Get average order value for a specific gateway.
	 *
	 * @param string      $gateway       Gateway ID (e.g., 'stripe', 'paypal')
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false
	 * @param bool        $formatted     Whether to return formatted amount. Default false
	 *
	 * @return float|string|false Returns:
	 *                           - float: When formatted is false (raw amount)
	 *                           - string: When formatted is true (formatted currency amount)
	 *                           - false: On error or invalid gateway
	 * @throws Exception When there's an error processing the stats
	 */
	public static function get_average_order_value(
		string $gateway,
		?string $start_date = null,
		?string $end_date = null,
		bool $exclude_taxes = false,
		bool $formatted = false
	) {
		if ( empty( $gateway ) || ! self::validate_gateway( $gateway ) ) {
			return false;
		}

		// Calculate average manually using earnings and order count for more reliability
		$earnings = self::get_earnings( $gateway, $start_date, $end_date, $exclude_taxes, false );
		$count    = self::get_order_count( $gateway, $start_date, $end_date );

		if ( $earnings === false || $count === false || $count === 0 ) {
			return $formatted ? edd_currency_filter( edd_format_amount( 0.0 ) ) : 0.0;
		}

		$average = $earnings / $count;

		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $average ) );
		}

		return round( $average, 2 );
	}

	/** Helper Methods ******************************************************/

	/**
	 * Validate that a gateway exists.
	 *
	 * @param string $gateway Gateway ID to validate
	 *
	 * @return bool Whether the gateway exists
	 */
	private static function validate_gateway( string $gateway ): bool {
		$gateways = edd_get_payment_gateways();

		return array_key_exists( $gateway, $gateways );
	}

}