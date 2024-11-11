<?php
/**
 * Core Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides core functionality for order operations.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

use ArrayPress\Utils\Common\Cache;
use ArrayPress\Utils\Database\Exists;
use EDD_Customer;
use WP_User;
use EDD\Orders\Order;

trait Core {

	/**
	 * Check if the order exists in the database.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order exists, false otherwise.
	 */
	public static function exists( int $order_id ): bool {
		return Exists::row( 'edd_orders', 'id', $order_id );
	}

	/**
	 * Check if an order with the given payment key exists.
	 *
	 * @param string $payment_key The payment key of the order to check.
	 * @param bool   $use_cache   Whether to use cache for the lookup. Default true.
	 *
	 * @return bool True if the order exists, false otherwise.
	 */
	public static function exists_by_payment_key( string $payment_key, bool $use_cache = true ): bool {
		if ( empty( $payment_key ) ) {
			return false;
		}

		$order_id = self::get_id_by_payment_key( $payment_key, $use_cache );

		return $order_id !== null;
	}

	/**
	 * Get the order ID associated with the given payment key.
	 *
	 * @param string $payment_key The payment key of the order to retrieve.
	 * @param bool   $use_cache   Whether to use cache for the lookup. Default true.
	 *
	 * @return int|null The order ID if found, null otherwise.
	 */
	public static function get_id_by_payment_key( string $payment_key, bool $use_cache = true ): ?int {
		if ( empty( $payment_key ) ) {
			return null;
		}

		// Generate a cache key
		$cache_key = Cache::generate_key( 'edd_order_id_by_payment_key', $payment_key );

		// Use the remember method to get cached value or compute if not exists
		$order_id = Cache::remember(
			$cache_key,
			function () use ( $payment_key ) {
				global $wpdb;

				return $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$wpdb->edd_orders} WHERE payment_key = %s LIMIT 1",
					$payment_key
				) );
			},
			$use_cache ? HOUR_IN_SECONDS : 0
		);

		return $order_id ? (int) $order_id : null;
	}

	/**
	 * Retrieves the customer object associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return EDD_Customer|null The customer object or null if not found.
	 */
	public static function get_customer( int $order_id ): ?EDD_Customer {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		$customer = edd_get_customer( $order->customer_id );
		if ( ! $customer ) {
			return null;
		}

		return $customer;
	}

	/**
	 * Retrieves the user object associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return WP_User|null The user object or null if not found.
	 */
	public static function get_user( int $order_id ): ?WP_User {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		$user = get_userdata( $order->user_id );
		if ( ! $user || ! $user->exists() ) {
			return null;
		}

		return $user;
	}

	/** Helper Methods ******************************************************/

	/**
	 * Get and validate an order object.
	 *
	 * @param int $order_id Order ID
	 *
	 * @return Order|null Order object or null if invalid
	 */
	protected static function get_validated( int $order_id = 0 ): ?Order {
		// Bail if no order ID was passed
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );

		// Return null if not a valid EDD\Orders\Order object
		return ( $order instanceof Order ) ? $order : null;
	}

}