<?php
/**
 * Product Operations Trait for Easy Digital Downloads (EDD)
 *
 * This trait provides methods for handling product-related operations in the EDD cart.
 *
 * @package       ArrayPress\EDD\Traits\Cart
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Cart;

trait Totals {

	/**
	 * Retrieve the total value for products in the cart based on a callback.
	 *
	 * @param callable $check_callback The callback to check if the item is valid.
	 * @param callable $total_callback The callback to calculate the total value.
	 * @param bool     $use_price_id   Whether to pass the price ID to the total callback.
	 *
	 * @return float|null The total product value or null if no items.
	 */
	public static function get_product_total_by_callbacks( callable $check_callback, callable $total_callback, bool $use_price_id = false ): ?float {
		if ( ! is_callable( $check_callback ) || ! is_callable( $total_callback ) ) {
			return null;
		}

		$total      = 0.0;
		$cart_items = edd_get_cart_content_details();

		if ( ! $cart_items ) {
			return null;
		}

		foreach ( $cart_items as $cart_item ) {
			$price_id = $cart_item['options']['price_id'] ?? null;
			if ( $check_callback( $cart_item['id'] ) ) {
				if ( $use_price_id ) {
					$total += (float) $total_callback( $cart_item['id'], $price_id );
				} else {
					$total += (float) $total_callback( $cart_item['id'] );
				}
			}
		}

		return $total;
	}

	/**
	 * Retrieve the total sum of a specific key for all items in the cart based on a callback.
	 *
	 * @param string        $key      The key to sum up (e.g., 'price', 'subtotal', 'tax').
	 * @param callable|null $callback The callback to check if the item should be included in the total.
	 *
	 * @return float The total sum of the specified key.
	 */
	public static function get_product_total_by_key( string $key, callable $callback = null ): float {
		$total      = 0.0;
		$cart_items = edd_get_cart_content_details();

		if ( empty( $cart_items ) ) {
			return $total;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( isset( $cart_item[ $key ] ) ) {
				if ( is_null( $callback ) || $callback( $cart_item ) ) {
					$total += (float) $cart_item[ $key ];
				}
			}
		}

		return $total;
	}

}