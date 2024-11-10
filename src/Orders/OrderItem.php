<?php
/**
 * Order Item Utilities for Easy Digital Downloads (EDD)
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

class OrderItem {

	/**
	 * Check if the order item exists in the database.
	 *
	 * @param int $order_item_id The ID of the order item to check.
	 *
	 * @return bool True if the order item exists, false otherwise.
	 */
	public static function exists( int $order_item_id ): bool {
		return Exists::row( 'edd_order_items', 'id', $order_item_id );
	}

	/**
	 * Get a specific field from an order item object.
	 *
	 * @param int    $order_item_id The order item ID.
	 * @param string $field         The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $order_item_id, string $field ) {
		// Bail if no log ID was passed.
		if ( empty( $order_item_id ) ) {
			return null;
		}

		// Get the log object
		$order_item = edd_get_order_item( $order_item_id );

		// If log doesn't exist, return null
		if ( ! $order_item ) {
			return null;
		}

		// First, check if it's a property of the log object
		if ( isset( $order_item->$field ) ) {
			return $order_item->$field;
		}

		// If not found in log object, check log meta
		$meta_value = edd_get_order_item_meta( $order_item_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

	/**
	 * Get an order item by cart index.
	 *
	 * @param int $order_id   The order ID
	 * @param int $cart_index The cart index of the order item to retrieve
	 *
	 * @return object|false Order item object if found, false if not found
	 */
	public static function get_by_cart_index( int $order_id, int $cart_index ) {
		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'cart_index' => $cart_index,
			'number'     => 1
		] );

		return ! empty( $order_items ) ? current( $order_items ) : false;
	}

	/**
	 * Get an order item by product details.
	 *
	 * @param int      $order_id   The order ID
	 * @param int      $product_id The product ID to find
	 * @param int|null $price_id   Optional. The price ID to match
	 *
	 * @return object|false Order item object if found, false if not found
	 */
	public static function get_by_product( int $order_id, int $product_id, ?int $price_id = null ) {
		$args = [
			'order_id'   => $order_id,
			'product_id' => $product_id,
			'number'     => 1
		];

		if ( $price_id !== null ) {
			$args['price_id'] = $price_id;
		}

		$order_items = edd_get_order_items( $args );

		return ! empty( $order_items ) ? current( $order_items ) : false;
	}

}