<?php
/**
 * Order Statistics for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Stats
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Stats;

use ArrayPress\Utils\Database\Generate;
use EDD\Stats;

class Orders {

	/**
	 * Get the most popular countries by order count.
	 *
	 * @param array $args   Optional. Additional arguments for the query.
	 * @param int   $number Optional. Number of countries to return. Default 10.
	 *
	 * @return array List of countries with their order counts.
	 */
	public static function get_most_popular_countries( array $args = [], int $number = 10 ): array {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = Generate::placeholders( $args['status'] );

		$sql = $wpdb->prepare(
			"SELECT oa.country, COUNT(DISTINCT o.id) as count
            FROM {$wpdb->prefix}edd_order_addresses oa
            INNER JOIN {$wpdb->prefix}edd_orders o ON o.id = oa.order_id
            WHERE o.type = %s 
            AND o.status IN ($status_placeholder)
            AND oa.country != ''
            GROUP BY oa.country
            ORDER BY count DESC
            LIMIT %d",
			array_merge(
				[ $args['type'] ],
				$args['status'],
				[ $number ]
			)
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return [];
		}

		// Convert country codes to names
		$countries = edd_get_country_list();
		foreach ( $results as $result ) {
			$result->country_name = $countries[ $result->country ] ?? $result->country;
			$result->count        = absint( $result->count );
		}

		return $results;
	}

	/**
	 * Get the most popular payment gateways by order count.
	 *
	 * @param array $args   Optional. Additional arguments for the query.
	 * @param int   $number Optional. Number of gateways to return. Default 10.
	 *
	 * @return array List of payment gateways with their order counts.
	 */
	public static function get_most_popular_gateways( array $args = [], int $number = 10 ): array {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = Generate::placeholders( $args['status'] );

		$sql = $wpdb->prepare(
			"SELECT gateway, COUNT(id) as count
            FROM {$wpdb->prefix}edd_orders
            WHERE type = %s 
            AND status IN ($status_placeholder)
            AND gateway != ''
            GROUP BY gateway
            ORDER BY count DESC
            LIMIT %d",
			array_merge(
				[ $args['type'] ],
				$args['status'],
				[ $number ]
			)
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return [];
		}

		// Get gateway labels
		$gateways = edd_get_payment_gateways();
		foreach ( $results as $result ) {
			$result->gateway_label = $gateways[ $result->gateway ]['admin_label'] ?? $result->gateway;
			$result->count         = absint( $result->count );
		}

		return $results;
	}

	/**
	 * Get the most popular months for sales by order count.
	 *
	 * @param array $args   Optional. Additional arguments for the query.
	 * @param int   $number Optional. Number of months to return. Default 12.
	 *
	 * @return array Array of months with their order counts.
	 */
	public static function get_most_popular_months( array $args = [], int $number = 12 ): array {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = Generate::placeholders( $args['status'] );

		$sql = $wpdb->prepare(
			"SELECT 
                MONTH(date_created) as month,
                MONTHNAME(date_created) as month_name,
                COUNT(id) as count
            FROM {$wpdb->prefix}edd_orders
            WHERE type = %s 
            AND status IN ($status_placeholder)
            GROUP BY MONTH(date_created)
            ORDER BY count DESC
            LIMIT %d",
			array_merge(
				[ $args['type'] ],
				$args['status'],
				[ $number ]
			)
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return [];
		}

		// Format the results
		foreach ( $results as $result ) {
			$result->month = absint( $result->month );
			$result->count = absint( $result->count );
		}

		return $results;
	}

	/**
	 * Get the most popular email domains from the orders.
	 *
	 * @param array $args   Optional. Additional arguments for the query.
	 * @param int   $number Optional. Number of domains to return. Default 10.
	 *
	 * @return array List of email domains with their counts.
	 */
	public static function get_most_popular_email_domains( array $args = [], int $number = 10 ): array {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = Generate::placeholders( $args['status'] );

		$sql = $wpdb->prepare(
			"SELECT 
                SUBSTRING_INDEX(email, '@', -1) as domain,
                COUNT(*) as count
            FROM {$wpdb->prefix}edd_orders
            WHERE type = %s 
            AND status IN ($status_placeholder)
            AND email LIKE %s
            GROUP BY domain
            ORDER BY count DESC
            LIMIT %d",
			array_merge(
				[ $args['type'] ],
				$args['status'],
				[ '%@%' ],
				[ $number ]
			)
		);

		$results = $wpdb->get_results( $sql );

		if ( empty( $results ) ) {
			return [];
		}

		foreach ( $results as $result ) {
			$result->count = absint( $result->count );
		}

		return $results;
	}

	/**
	 * Retrieves all distinct currencies from the EDD orders table.
	 *
	 * @param bool $use_cache Optional. Whether to use cached results. Default true.
	 *
	 * @return array An array of distinct currencies.
	 */
	public static function get_distinct_currencies( bool $use_cache = true ): array {
		global $wpdb;

		$transient_key = 'edd_distinct_currencies';

		if ( $use_cache ) {
			$cached_currencies = get_transient( $transient_key );
			if ( false !== $cached_currencies ) {
				return $cached_currencies;
			}
		}

		$sql = $wpdb->prepare(
			"SELECT DISTINCT currency 
            FROM {$wpdb->prefix}edd_orders 
            WHERE currency != %s 
            ORDER BY currency ASC",
			''
		);

		$results = $wpdb->get_col( $sql );

		if ( ! empty( $results ) && $use_cache ) {
			set_transient( $transient_key, $results, HOUR_IN_SECONDS );
		}

		return $results ?: [];
	}

	/**
	 * Retrieves all distinct payment gateways from the EDD orders table.
	 *
	 * @param bool $use_cache Optional. Whether to use cached results. Default true.
	 *
	 * @return array An array of distinct payment gateways.
	 */
	public static function get_distinct_gateways( bool $use_cache = true ): array {
		global $wpdb;

		$transient_key = 'edd_distinct_payment_gateways';

		if ( $use_cache ) {
			$cached_gateways = get_transient( $transient_key );
			if ( false !== $cached_gateways ) {
				return $cached_gateways;
			}
		}

		$sql = $wpdb->prepare(
			"SELECT DISTINCT gateway 
            FROM {$wpdb->prefix}edd_orders 
            WHERE gateway != %s 
            ORDER BY gateway ASC",
			''
		);

		$results = $wpdb->get_col( $sql );

		if ( ! empty( $results ) && $use_cache ) {
			set_transient( $transient_key, $results, HOUR_IN_SECONDS );
		}

		return $results ?: [];
	}

	/**
	 * Retrieves all distinct email domains from the EDD orders.
	 *
	 * @param array $args      Optional. Additional arguments for the query.
	 * @param bool  $use_cache Optional. Whether to use cached results. Default true.
	 *
	 * @return array An array of distinct email domains.
	 */
	public static function get_distinct_email_domains( array $args = [], bool $use_cache = true ): array {
		global $wpdb;

		$transient_key = 'edd_distinct_email_domains';

		if ( $use_cache ) {
			$cached_domains = get_transient( $transient_key );
			if ( false !== $cached_domains ) {
				return $cached_domains;
			}
		}

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT DISTINCT 
                SUBSTRING_INDEX(email, '@', -1) as domain
            FROM {$wpdb->prefix}edd_orders
            WHERE type = %s 
            AND status IN ($status_placeholder)
            AND email != ''
            AND email LIKE %s
            ORDER BY domain ASC",
			array_merge(
				[ $args['type'] ],
				$args['status'],
				[ '%@%' ] // Ensure email contains @ symbol
			)
		);

		$results = $wpdb->get_col( $sql );

		if ( ! empty( $results ) && $use_cache ) {
			set_transient( $transient_key, $results, HOUR_IN_SECONDS );
		}

		return $results ?: [];
	}

	/**
	 * Get the total store earnings from the database or transient.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total store earnings, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_earnings( array $args = [] ): float {
		$defaults = [
			'currency'     => 'converted',
			'revenue_type' => 'gross',
			'status'       => \edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure output is always 'raw'
		$args['output']   = 'raw';
		$args['function'] = 'SUM';

		$stats = self::get_stats( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get the total store net earnings from the database.
	 *
	 * This function forces the revenue_type to 'net' and does not allow it to be overridden.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total store net earnings, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_net_earnings( array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the total tax amount from the database.
	 *
	 * This function forces the column to 'tax'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total tax amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_tax( array $args = [] ): float {
		$args['column'] = 'tax';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the total discount amount from the database.
	 *
	 * This function forces the column to 'discount'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total discount amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_discount_amount( array $args = [] ): float {
		$args['column'] = 'discount';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the average order value from the database or transient.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average order value, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_earnings( array $args = [] ): float {
		$defaults = [
			'currency'     => 'converted',
			'revenue_type' => 'gross',
			'status'       => \edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure output is always 'raw'
		$args['output']   = 'raw';
		$args['function'] = 'AVG';
		$args['relative'] = false;

		$stats = self::get_stats( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get the average net order value from the database or transient.
	 *
	 * This function forces the revenue_type to 'net' and does not allow it to be overridden.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average net order value, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_net_average_earnings( array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average tax amount from the database or transient.
	 *
	 * This function forces the column to 'tax'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average tax amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_tax_amount( array $args = [] ): float {
		$args['column'] = 'tax';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average discount amount from the database or transient.
	 *
	 * This function forces the column to 'discount'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average discount amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_discount_amount( array $args = [] ): float {
		$args['column'] = 'discount';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average subtotal amount from the database or transient.
	 *
	 * This function forces the column to 'subtotal'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average subtotal amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_subtotal_amount( array $args = [] ): float {
		$args['column'] = 'subtotal';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get average spending per customer.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average amount spent per customer.
	 */
	public static function get_average_customer_spend( array $args = [] ): float {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => \edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT AVG(total_spent) as avg_spend
            FROM (
                SELECT customer_id, SUM(total) as total_spent
                FROM {$wpdb->prefix}edd_orders
                WHERE type = %s 
                AND status IN ($status_placeholder)
                AND customer_id != 0
                GROUP BY customer_id
            ) as customer_totals",
			array_merge(
				[ $args['type'] ],
				$args['status']
			)
		);

		$result = $wpdb->get_var( $sql );

		return is_null( $result ) ? 0.0 : round( (float) $result, 2 );
	}

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

	/**
	 * Get Stats instance with given arguments.
	 *
	 * @param array $args Stats arguments
	 *
	 * @return Stats
	 */
	protected static function get_stats( array $args = [] ): ?Stats {
		return new Stats( $args );
	}

}