<?php
/**
 * Category Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * Provides methods for determining order types and categories.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Conditions {

	/**
	 * Check if the order is a all access pass order.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is an all access pass order, false otherwise.
	 */
	public static function is_all_access( int $order_id ): bool {
		return (bool) edd_get_order_meta( $order_id, '_edd_aa_active_ids', true );
	}

	/**
	 * Check if the order is free (total amount is 0).
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is free, false otherwise.
	 */
	public static function is_free( int $order_id ): bool {
		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		return 0 == $order->total;
	}

	/**
	 * Check if this is the customer's first order.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if this is the customer's first order, false otherwise.
	 */
	public static function is_first_order( int $order_id ): bool {
		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		// Get all completed orders for this customer
		$orders = edd_get_orders( [
			'customer_id' => $order->customer_id,
			'status'      => ['complete'],
			'number'      => 2, // We only need to check if there's more than one
			'orderby'     => 'date_created',
			'order'       => 'ASC' // Oldest first
		] );

		// If this is the only order or the first order chronologically
		return ! empty( $orders ) && $orders[0]->id === $order_id;
	}

}