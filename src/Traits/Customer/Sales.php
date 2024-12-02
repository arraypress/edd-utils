<?php
/**
 * Customer Sales Operations Trait for Easy Digital Downloads (EDD)
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\EDD\Customers\Customer;
use ArrayPress\EDD\Stats\Dates;
use ArrayPress\EDD\Common\Generate;
use EDD\Stats;

trait Sales {

	/**
	 * Meta key for storing customer sales stats
	 *
	 * @var string
	 */
	protected static string $sales_meta_key = 'edd_customer_sales_stats';

	/**
	 * Get sales count for a customer.
	 *
	 * @param int         $customer_id The customer ID
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return int Total number of sales for the customer.
	 */
	public static function get_sales( int $customer_id, ?string $period = 'all_time', array $args = [] ): int {
		if ( empty( $customer_id ) ) {
			return 0;
		}

		$stats = new Stats();

		$query_args             = Dates::parse_args( $args, $period );
		$query_args['customer'] = $customer_id;

		return (int) $stats->get_customer_order_count( $query_args );
	}

	/**
	 * Get average daily sales.
	 *
	 * @param int         $customer_id The customer ID
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return float Average sales per day.
	 */
	public static function get_average_daily_sales( int $customer_id, ?string $period = 'all_time', array $args = [] ): float {
		$args['function'] = 'AVG';

		return (float) self::get_sales( $customer_id, $period, $args );
	}

	/**
	 * Get net sales (includes refunds).
	 *
	 * @param int         $customer_id The customer ID
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return int Net number of sales.
	 */
	public static function get_net_sales( int $customer_id, ?string $period = 'all_time', array $args = [] ): int {
		$args['revenue_type'] = 'net';

		return self::get_sales( $customer_id, $period, $args );
	}

	/**
	 * Process and cache all period stats for a customer.
	 *
	 * @param int   $customer_id The customer ID
	 * @param array $args        Optional. Additional query arguments.
	 *
	 * @return array Array of stats for all periods.
	 */
	public static function process_sales_stats( int $customer_id, array $args = [] ): array {
		if ( empty( $customer_id ) ) {
			return [];
		}

		$stats = [];

		// Get all available periods
		foreach ( Dates::get_periods() as $period ) {
			$stats[ $period ] = [
				'sales'               => self::get_sales( $customer_id, $period, $args ),
				'net_sales'           => self::get_net_sales( $customer_id, $period, $args ),
				'average_daily_sales' => self::get_average_daily_sales( $customer_id, $period, $args ),
				'generated'           => time()
			];
		}

		// Store the stats in customer meta
		edd_update_customer_meta( $customer_id, self::$sales_meta_key, $stats );

		return $stats;
	}

	/**
	 * Get cached stats for a customer.
	 *
	 * @param int    $customer_id The customer ID
	 * @param string $period      Optional. Specific period to retrieve. Default returns all periods.
	 * @param bool   $force       Optional. Force regeneration of stats. Default false.
	 *
	 * @return array Array of cached stats
	 */
	public static function get_cached_sales_stats( int $customer_id, ?string $period = null, bool $force = false ): array {
		if ( empty( $customer_id ) ) {
			return [];
		}

		// Get cached stats
		$stats = edd_get_customer_meta( $customer_id, self::$sales_meta_key, true );

		// If no stats exist, force is true, or stats are older than 24 hours, regenerate them
		if ( empty( $stats ) || $force || ! isset( $stats['all_time']['generated'] ) || ( time() - $stats['all_time']['generated'] ) > DAY_IN_SECONDS ) {
			$stats = self::process_sales_stats( $customer_id );
		}

		// Return specific period if requested
		if ( ! is_null( $period ) && isset( $stats[ $period ] ) ) {
			return $stats[ $period ];
		}

		return $stats;
	}

	/**
	 * Clear cached stats for a customer.
	 *
	 * @param int $customer_id The customer ID
	 *
	 * @return bool Whether the meta was successfully deleted.
	 */
	public static function clear_cached_sales_stats( int $customer_id ): bool {
		if ( empty( $customer_id ) ) {
			return false;
		}

		return edd_delete_customer_meta( $customer_id, self::$sales_meta_key );
	}

}