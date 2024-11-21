<?php
/**
 * Raw Statistics for Easy Digital Downloads (EDD)
 *
 *
 *
 * @package       ArrayPress\EDD\Stats
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Stats;

/**
 * Raw statistics functionality for Easy Digital Downloads.
 */
class Raw {

	/**
	 * Get average order earnings based on provided criteria.
	 *
	 * @param array  $statuses      Order statuses to include. Defaults to complete order statuses.
	 * @param string $amount_column The column to average. Defaults to 'total'.
	 * @param string $type          Order type to filter by. Defaults to 'sale'.
	 * @param int    $cache_time    Cache duration in seconds. Defaults to HOUR_IN_SECONDS.
	 *
	 * @return float
	 */
	public static function get_average_order_earnings(
		array $statuses = [],
		string $amount_column = 'total',
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): float {
		return self::get_order_stat( 'AVG', $amount_column, $statuses, $type, $cache_time );
	}

	/**
	 * Get total order earnings based on provided criteria.
	 *
	 * @param array  $statuses      Order statuses to include. Defaults to complete order statuses.
	 * @param string $amount_column The column to sum. Defaults to 'total'.
	 * @param string $type          Order type to filter by. Defaults to 'sale'.
	 * @param int    $cache_time    Cache duration in seconds. Defaults to HOUR_IN_SECONDS.
	 *
	 * @return float
	 */
	public static function get_total_order_earnings(
		array $statuses = [],
		string $amount_column = 'total',
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): float {
		return self::get_order_stat( 'SUM', $amount_column, $statuses, $type, $cache_time );
	}

	/**
	 * Get highest order amount based on provided criteria.
	 *
	 * @param array  $statuses      Order statuses to include. Defaults to complete order statuses.
	 * @param string $amount_column The column to check. Defaults to 'total'.
	 * @param string $type          Order type to filter by. Defaults to 'sale'.
	 * @param int    $cache_time    Cache duration in seconds. Defaults to HOUR_IN_SECONDS.
	 *
	 * @return float
	 */
	public static function get_highest_order_amount(
		array $statuses = [],
		string $amount_column = 'total',
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): float {
		return self::get_order_stat( 'MAX', $amount_column, $statuses, $type, $cache_time );
	}

	/**
	 * Get lowest order amount based on provided criteria.
	 *
	 * @param array  $statuses      Order statuses to include. Defaults to complete order statuses.
	 * @param string $amount_column The column to check. Defaults to 'total'.
	 * @param string $type          Order type to filter by. Defaults to 'sale'.
	 * @param int    $cache_time    Cache duration in seconds. Defaults to HOUR_IN_SECONDS.
	 *
	 * @return float
	 */
	public static function get_lowest_order_amount(
		array $statuses = [],
		string $amount_column = 'total',
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): float {
		return self::get_order_stat( 'MIN', $amount_column, $statuses, $type, $cache_time );
	}

	/**
	 * Get count of orders matching criteria.
	 *
	 * @param array  $statuses   Order statuses to include. Defaults to complete order statuses.
	 * @param string $type       Order type to filter by. Defaults to 'sale'.
	 * @param int    $cache_time Cache duration in seconds. Defaults to HOUR_IN_SECONDS.
	 *
	 * @return int
	 */
	public static function get_order_count(
		array $statuses = [],
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): int {
		return (int) self::get_order_stat( 'COUNT', 'id', $statuses, $type, $cache_time );
	}

	/**
	 * Get order statistic based on provided criteria.
	 *
	 * @param string $function      The SQL aggregate function (AVG, SUM, MAX, MIN, COUNT).
	 * @param string $amount_column The column to calculate against.
	 * @param array  $statuses      Order statuses to include.
	 * @param string $type          Order type to filter by.
	 * @param int    $cache_time    Cache duration in seconds.
	 *
	 * @return float
	 */
	protected static function get_order_stat(
		string $function,
		string $amount_column,
		array $statuses = [],
		string $type = 'sale',
		int $cache_time = HOUR_IN_SECONDS
	): float {
		global $wpdb;

		$statuses     = ! empty( $statuses ) ? $statuses : edd_get_complete_order_statuses();
		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		$cache_key = sprintf(
			'edd_%s_%s_%s',
			strtolower( $function ),
			$amount_column,
			md5( implode( '_', $statuses ) )
		);

		$result = wp_cache_get( $cache_key, 'edd_raw_stats' );

		if ( false === $result ) {
			$query = $wpdb->prepare(
				"SELECT {$function}({$amount_column}) as result 
                FROM {$wpdb->prefix}edd_orders 
                WHERE type = %s AND status IN ({$placeholders})",
				array_merge( [ $type ], $statuses )
			);

			$result = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $result, 'edd_raw_stats', $cache_time );
		}

		return (float) $result ?: 0.0;
	}

}