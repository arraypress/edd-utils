<?php
/**
 * Core Cart Operations Trait for Easy Digital Downloads (EDD)
 *
 * This trait provides methods for handling general cart item operations in the EDD cart.
 *
 * @package       ArrayPress\EDD\Cart\Traits
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Cart;

trait Core {

	/**
	 * Check if the cart contains a specific key/value pair.
	 *
	 * @param string $key   The key to check.
	 * @param mixed  $value The value to check.
	 *
	 * @return bool True if the cart contains the key/value pair, false otherwise.
	 */
	public static function contains_key_value_match( string $key, $value ): bool {
		$cart_items = edd_get_cart_content_details();

		if ( empty( $cart_items ) ) {
			return false;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( isset( $cart_item[ $key ] ) && $cart_item[ $key ] == $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve a specific value from a cart item based on a given key.
	 *
	 * @param string   $key        The key to retrieve the value for.
	 * @param int|null $item_index The index of the cart item to check (optional).
	 *
	 * @return mixed|null The value if found, or null if not found.
	 */
	public static function get_item_value( string $key, int $item_index = null ) {
		$cart_items = edd_get_cart_content_details();

		if ( empty( $cart_items ) ) {
			return null;
		}

		if ( $item_index !== null ) {
			return $cart_items[ $item_index ][ $key ] ?? null;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( isset( $cart_item[ $key ] ) ) {
				return $cart_item[ $key ];
			}
		}

		return null;
	}

	/**
	 * Retrieve the cart index based on product_id and an optional price_id.
	 *
	 * @param int      $product_id The product ID to search for.
	 * @param int|null $price_id   The optional price ID to search for.
	 *
	 * @return int|null The cart index if found, or null if not found.
	 */
	public static function get_index( int $product_id, ?int $price_id = null ): ?int {
		$cart_items = edd_get_cart_content_details();

		if ( empty( $cart_items ) ) {
			return null;
		}

		foreach ( $cart_items as $index => $cart_item ) {
			if ( $cart_item['id'] === $product_id ) {
				if ( $price_id === null ||
				     ( isset( $cart_item['item_number']['options']['price_id'] ) &&
				       $cart_item['item_number']['options']['price_id'] === $price_id ) ) {
					return $index;
				}
			}
		}

		return null;
	}

}