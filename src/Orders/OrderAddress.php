<?php
/**
 * Order Address Utilities for Easy Digital Downloads (EDD)
 *
 * @package       /EDD-Utils
 * @copyright     Copyright 2024,  Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Orders;

use ArrayPress\Utils\Database\Exists;

class OrderAddress {

	/**
	 * Check if the order address exists in the database.
	 *
	 * @param int $order_address_id The ID of the order address to check.
	 *
	 * @return bool True if the order address exists, false otherwise.
	 */
	public static function exists( int $order_address_id ): bool {
		return Exists::row( 'edd_order_addresses', 'id', $order_address_id );
	}

	/**
	 * Get a field from an order address object.
	 *
	 * @param int    $order_address_id Order Address ID. Default `0`.
	 * @param string $field            Field to retrieve from object. Default empty.
	 *
	 * @return mixed Null if order address does not exist. Value of Order Address if exists.
	 */
	public static function get_field( int $order_address_id = 0, string $field = '' ) {
		$order_address = edd_get_order_address( $order_address_id );

		// Check that field exists.
		return $order_address->{$field} ?? null;
	}

}