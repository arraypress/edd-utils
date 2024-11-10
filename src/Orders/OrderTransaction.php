<?php
/**
 * Order Transaction Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Orders;

use ArrayPress\Utils\Database\Exists;

class OrderTransaction {

	/**
	 * Check if the order transaction exists in the database.
	 *
	 * @param int $order_transaction_id The ID of the order transaction to check.
	 *
	 * @return bool True if the order transaction exists, false otherwise.
	 */
	public static function exists( int $order_transaction_id ): bool {
		return Exists::row( 'edd_order_transactions', 'id', $order_transaction_id );
	}

	/**
	 * Get a field from an order transaction object.
	 *
	 * @param int    $order_transaction_id Order Transaction ID. Default `0`.
	 * @param string $field                Field to retrieve from object. Default empty.
	 *
	 * @return mixed Null if order transaction does not exist. Value of Order Transaction if exists.
	 */
	public static function get_field( int $order_transaction_id = 0, string $field = '' ) {
		$order_transaction = edd_get_order_transaction( $order_transaction_id );

		// Check that field exists.
		return $order_transaction->{$field} ?? null;
	}

}