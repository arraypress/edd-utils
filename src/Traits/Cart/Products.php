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

trait Products {

	/**
	 * Get product IDs in the cart, optionally concatenated with price IDs if they are not null or not set.
	 *
	 * @param bool $include_price_ids Whether to include price IDs in the returned array. Default false.
	 *
	 * @return array Concatenated product ID and price ID strings, or just product IDs.
	 */
	public static function get_product_ids( bool $include_price_ids = false ): array {
		$cart_items  = edd_get_cart_contents();
		$product_ids = [];

		if ( empty( $cart_items ) ) {
			return $product_ids;
		}

		foreach ( $cart_items as $index => $cart_item ) {
			$product_id = $cart_item['id'];
			$price_id   = $cart_item['options']['price_id'] ?? null;

			if ( $include_price_ids && $price_id !== null ) {
				$product_ids[ $index ] = $product_id . '_' . $price_id;
			} else {
				$product_ids[ $index ] = (string) $product_id;
			}
		}

		return array_unique( $product_ids );
	}

	/**
	 * Check if a specific product ID is in the cart, optionally checking for a specific price ID.
	 *
	 * @param int      $product_id The product ID to check for.
	 * @param int|null $price_id   Optional. The price ID to check for.
	 *
	 * @return bool True if the product (and optionally price ID) is in the cart, false otherwise.
	 */
	public static function has_product( int $product_id, ?int $price_id = null ): bool {
		$cart_items = edd_get_cart_contents();

		if ( empty( $cart_items ) ) {
			return false;
		}

		foreach ( $cart_items as $item ) {
			if ( absint( $item['id'] ) === $product_id ) {
				// If price_id is not specified, we've found a match
				if ( $price_id === null ) {
					return true;
				}
				// If price_id is specified, check if it matches
				if ( isset( $item['options']['price_id'] ) && $item['options']['price_id'] === $price_id ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if specific product IDs are in the cart, optionally checking for specific price IDs.
	 *
	 * @param array $products  An array of product IDs to check for, or an array of arrays containing
	 *                         product ID and price ID pairs.
	 * @param bool  $match_all Whether all specified products must be in the cart (true) or any of them (false).
	 *
	 * @return bool True if the products are in the cart according to the matching criteria, false otherwise.
	 */
	public static function has_products( array $products, bool $match_all = true ): bool {
		$cart_items = edd_get_cart_contents();

		if ( empty( $cart_items ) || empty( $products ) ) {
			return false;
		}

		$found_count = 0;

		foreach ( $products as $product ) {
			$product_id = is_array( $product ) ? $product[0] : $product;
			$price_id   = is_array( $product ) && isset( $product[1] ) ? $product[1] : null;

			if ( self::has_product( $product_id, $price_id ) ) {
				$found_count ++;

				// If we're not matching all, and we found one, we can return true immediately
				if ( ! $match_all ) {
					return true;
				}
			} elseif ( $match_all ) {
				// If we're matching all and one is not found, we can return false immediately
				return false;
			}
		}

		// If we're matching all, all products must have been found
		return $match_all && $found_count === count( $products );
	}

	/**
	 * Get cart items filtered by a callback function.
	 *
	 * @param callable|null $callback Optional callback function that receives product_id, price_id, and item array
	 *                                and should return boolean to determine if item should be included.
	 *
	 * @return array Cart items with cart index as key and full item array as value
	 */
	public static function get_products_by_callback( callable $callback ): array {
		$cart_items = edd_get_cart_contents();
		$items      = [];

		if ( empty( $cart_items ) ) {
			return [];
		}

		foreach ( $cart_items as $cart_key => $item ) {
			$product_id = $item['id'] ?? null;
			$price_id   = $item['options']['price_id'] ?? null;

			if ( $callback( $product_id, $price_id, $item ) ) {
				$items[ $cart_key ] = $item;
			}
		}

		return $items;
	}

	/**
	 * Retrieve meta values for products in the cart for a specific meta key.
	 *
	 * @param string $meta_key The meta key to retrieve values for.
	 *
	 * @return array<int,mixed> Array of cart index => meta value pairs, or empty array if no matches.
	 */
	public static function get_product_meta_values( string $meta_key ): array {
		if ( ! $meta_key ) {
			return [];
		}

		$cart_items  = edd_get_cart_contents();
		$meta_values = [];

		if ( empty( $cart_items ) ) {
			return [];
		}

		foreach ( $cart_items as $cart_index => $cart_item ) {
			$meta_value = get_post_meta( $cart_item['id'], $meta_key, true );
			if ( $meta_value !== '' && $meta_value !== false ) {
				$meta_values[ $cart_index ] = $meta_value;
			}
		}

		return $meta_values;
	}

}