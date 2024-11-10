<?php
/**
 * Analytics Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for analyzing popular order metrics like countries,
 * gateways, and months.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

use ArrayPress\Utils\Database\Generate;

trait Analytics {

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

}