<?php
/**
 * Sales Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for sales counts, refunds, and related operations.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

trait Sales {

	/**
	 * Required trait method for getting EDD Stats instance.
	 *
	 * @param array $args Stats arguments
	 *
	 * @return \EDD\Stats
	 */
	abstract protected static function get_stats( array $args = [] ): \EDD\Stats;

	/**
	 * Get the total sales from the database or transient.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return int The total number of sales.
	 */
	public static function get_total_sales( array $args = [] ): int {
		$defaults = [
			'currency'     => 'converted',
			'revenue_type' => 'gross',
			'status'       => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure output is always 'raw'
		$args['output']   = 'raw';
		$args['function'] = 'SUM';

		$stats = self::get_stats( $args );

		return (int) $stats->get_order_count();
	}

	/**
	 * Get average number of orders per customer.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average number of orders per customer.
	 */
	public static function get_average_customer_order_count( array $args = [] ): float {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT AVG(order_count) as avg_count
            FROM (
                SELECT customer_id, COUNT(id) as order_count
                FROM {$wpdb->prefix}edd_orders
                WHERE type = %s 
                AND status IN ($status_placeholder)
                AND customer_id != 0
                GROUP BY customer_id
            ) as customer_orders",
			array_merge(
				[ $args['type'] ],
				$args['status']
			)
		);

		$result = $wpdb->get_var( $sql );

		return is_null( $result ) ? 0.0 : round( (float) $result, 1 );
	}

	/**
	 * Get the refund rate.
	 *
	 * This function calculates the refund rate based on completed order statuses.
	 *
	 * @return float The refund rate.
	 */
	public static function get_refund_rate(): float {
		$stats = self::get_stats( [
			'status' => edd_get_complete_order_statuses(),
			'output' => 'output'
		] );

		return $stats->get_refund_rate();
	}

	/**
	 * Get the average time it takes for an order to be refunded.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return string Average time for an order to be refunded in human-readable format.
	 */
	public static function get_average_refund_time( array $args = [] ): string {
		$stats = self::get_stats( $args );

		return $stats->get_average_refund_time();
	}

	/**
	 * Get the busiest day for the store orders.
	 *
	 * This function retrieves the busiest day for store orders by querying the EDD statistics.
	 * It returns the day with the highest number of completed orders.
	 *
	 * @return ?string The busiest day, or null if the EDD\Stats class does not exist.
	 */
	public static function get_busiest_day(): ?string {
		$stats = self::get_stats( [
			'status' => edd_get_complete_order_statuses()
		] );

		return $stats->get_busiest_day();
	}

	/**
	 * Get average time between purchases for returning customers.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return string|null The average time between purchases in human-readable format.
	 */
	public static function get_average_time_between_purchases( array $args = [] ): ?string {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT AVG(time_diff) as avg_time
            FROM (
                SELECT 
                    customer_id,
                    TIMESTAMPDIFF(SECOND, 
                        LAG(date_created) OVER (PARTITION BY customer_id ORDER BY date_created), 
                        date_created
                    ) as time_diff
                FROM {$wpdb->prefix}edd_orders
                WHERE type = %s 
                AND status IN ($status_placeholder)
                AND customer_id != 0
            ) as time_diffs
            WHERE time_diff IS NOT NULL",
			array_merge(
				[ $args['type'] ],
				$args['status']
			)
		);

		$result = $wpdb->get_var( $sql );

		if ( is_null( $result ) ) {
			return null;
		}

		// Convert seconds to human-readable format
		$seconds = absint( $result );

		if ( $seconds < HOUR_IN_SECONDS ) {
			$value = round( $seconds / MINUTE_IN_SECONDS );

			return $value . 'm';
		} elseif ( $seconds < DAY_IN_SECONDS ) {
			$value = round( $seconds / HOUR_IN_SECONDS );

			return $value . 'h';
		} elseif ( $seconds < WEEK_IN_SECONDS ) {
			$value = round( $seconds / DAY_IN_SECONDS );

			return $value . 'd';
		} elseif ( $seconds < MONTH_IN_SECONDS ) {
			$value = round( $seconds / WEEK_IN_SECONDS );

			return $value . 'w';
		} else {
			$value = round( $seconds / MONTH_IN_SECONDS );

			return $value . 'mo';
		}
	}

}