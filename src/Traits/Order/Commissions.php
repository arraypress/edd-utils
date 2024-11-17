<?php
/**
 * Commission Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * Provides methods for handling EDD Commissions functionality.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Commissions {

	/**
	 * Get the total commission earnings for an order.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $args     Additional query arguments.
	 *
	 * @return float|null The total commission earnings, or null if EDD Commissions is not active.
	 */
	public static function get_commission_earnings( int $order_id, array $args = [] ): ?float {
		if ( ! function_exists( 'edd_commissions' ) || empty( $order_id ) ) {
			return null;
		}

		$default_args = [
			'status'     => [ 'unpaid', 'paid' ],
			'payment_id' => $order_id,
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return edd_commissions()->commissions_db->sum( 'amount', $args );
	}

	/**
	 * Get the count of commissions for an order.
	 *
	 * @param int   $order_id   The ID of the order.
	 * @param array $query_args Additional query arguments.
	 *
	 * @return int|null The count of commissions, or null if EDD Commissions is not active.
	 */
	public static function get_commissions_count( int $order_id, array $query_args = [] ): ?int {
		if ( ! function_exists( 'edd_commissions' ) || empty( $order_id ) ) {
			return null;
		}

		$default_args = [
			'status'     => [ 'unpaid', 'paid' ],
			'payment_id' => $order_id,
			'number'     => - 1,
		];

		$query_args = wp_parse_args( $query_args, $default_args );

		return edd_commissions()->commissions_db->count( $query_args );
	}

	/**
	 * Get the commissions for an order.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $args     Additional query arguments.
	 *
	 * @return array|null An array of commissions, or null if EDD Commissions is not active.
	 */
	public static function get_commissions( int $order_id, array $args = [] ): ?array {
		if ( ! function_exists( 'edd_commissions' ) || empty( $order_id ) ) {
			return null;
		}

		$default_args = [
			'status'     => [ 'unpaid', 'paid' ],
			'payment_id' => $order_id,
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return eddc_get_commissions( $args );
	}

	/**
	 * Get commission rates for products in an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return array Array of product IDs and their commission rates.
	 */
	public static function get_commission_rates( int $order_id ): array {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return [];
		}

		$order_items = edd_get_order_items( [
			'order_id' => $order_id,
			'number'   => 999999,
		] );

		if ( empty( $order_items ) ) {
			return [];
		}

		$rates = [];
		foreach ( $order_items as $item ) {
			$product_rate = get_post_meta( $item->product_id, '_edd_commission_rate', true );
			if ( $product_rate !== '' ) {
				$rates[ $item->product_id ] = floatval( $product_rate );
			}
		}

		return $rates;
	}

	/**
	 * Get unpaid commissions for an order.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $args     Additional query arguments.
	 *
	 * @return array|null Array of unpaid commissions, or null if EDD Commissions is not active.
	 */
	public static function get_unpaid_commissions( int $order_id, array $args = [] ): ?array {
		$args['status'] = 'unpaid';

		return self::get_commissions( $order_id, $args );
	}

	/**
	 * Get paid commissions for an order.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $args     Additional query arguments.
	 *
	 * @return array|null Array of paid commissions, or null if EDD Commissions is not active.
	 */
	public static function get_paid_commissions( int $order_id, array $args = [] ): ?array {
		$args['status'] = 'paid';

		return self::get_commissions( $order_id, $args );
	}

	/**
	 * Get commission recipients for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return array Array of user IDs who receive commissions for the order.
	 */
	public static function get_commission_recipients( int $order_id ): array {
		$commissions = self::get_commissions( $order_id );
		if ( empty( $commissions ) ) {
			return [];
		}

		return array_unique( array_map( function ( $commission ) {
			return $commission->user_id;
		}, $commissions ) );
	}

	/**
	 * Check if an order has any commissions.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order has commissions, false otherwise.
	 */
	public static function has_commissions( int $order_id ): bool {
		return (bool) self::get_commissions_count( $order_id, [ 'number' => 1 ] );
	}

	/**
	 * Check if an order has any unpaid commissions.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order has unpaid commissions, false otherwise.
	 */
	public static function has_unpaid_commissions( int $order_id ): bool {
		return (bool) self::get_commissions_count( $order_id, [
			'number' => 1,
			'status' => 'unpaid'
		] );
	}

	/**
	 * Get commission status for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null Returns 'paid', 'unpaid', 'partial', or null if no commissions.
	 */
	public static function get_commission_status( int $order_id ): ?string {
		$total_count = self::get_commissions_count( $order_id );
		if ( empty( $total_count ) ) {
			return null;
		}

		$paid_count = self::get_commissions_count( $order_id, [ 'status' => 'paid' ] );

		if ( $paid_count === 0 ) {
			return 'unpaid';
		} elseif ( $paid_count === $total_count ) {
			return 'paid';
		}

		return 'partial';
	}

	/**
	 * Get commission total by status.
	 *
	 * @param int    $order_id  The ID of the order.
	 * @param string $status    The commission status to total.
	 * @param bool   $formatted Whether to return a formatted amount.
	 *
	 * @return float|string|null The commission total or null if EDD Commissions is not active.
	 */
	public static function get_commission_total_by_status(
		int $order_id,
		string $status = 'paid',
		bool $formatted = false
	) {
		$args  = [ 'status' => $status ];
		$total = self::get_commission_earnings( $order_id, $args );

		if ( $total === null ) {
			return null;
		}

		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $total ) );
		}

		return $total;
	}

}