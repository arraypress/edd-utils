<?php
/**
 * Distinct Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

trait Distinct {

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

}