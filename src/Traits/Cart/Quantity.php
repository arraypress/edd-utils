<?php
/**
 * Product Type Operations Trait for Easy Digital Downloads (EDD)
 *
 * This trait provides methods for handling specific product type operations in the EDD cart.
 *
 * @package       ArrayPress\EDD\Traits\Cart
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Cart;

trait Quantity {

	/**
	 * Retrieve the count of products in the cart based on product type.
	 *
	 * @param string $type The product type to validate against.
	 *
	 * @return int The count of products in the cart matching the specified type.
	 */
	public static function get_count_by_type( string $type ): int {
		$count      = 0;
		$cart_items = edd_get_cart_contents();

		if ( empty( $cart_items ) ) {
			return $count;
		}

		$type = strtolower( $type );

		foreach ( $cart_items as $cart_item ) {
			$download = edd_get_download( $cart_item['id'] );

			if ( $download && strtolower( $download->get_type() ) === $type ) {
				$count ++;
			}
		}

		return absint( $count );
	}

	/**
	 * Retrieve the count of bundle products in the cart.
	 *
	 * @return int The count of bundle products in the cart.
	 */
	public static function get_bundle_count(): int {
		return self::get_count_by_type( 'bundle' );
	}

	/**
	 * Retrieve the count of service products in the cart.
	 *
	 * @return int The count of service products in the cart.
	 */
	public static function get_service_count(): int {
		return self::get_count_by_type( 'service' );
	}

	/**
	 * Retrieve the count of all access products in the cart.
	 *
	 * @return int The count of all access products in the cart.
	 */
	public static function get_all_access_count(): int {
		return self::get_count_by_type( 'all_access' );
	}

	/**
	 * Retrieve the count of recurring products (subscriptions) in the cart.
	 *
	 * @return int The count of recurring products in the cart.
	 */
	public static function get_recurring_count(): int {
		$count      = 0;
		$cart_items = edd_get_cart_content_details();

		if ( empty( $cart_items ) ) {
			return $count;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( isset( $cart_item['item_number']['options']['recurring'] ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Get the count of license products in the cart.
	 *
	 * @return int The count of license products in the cart.
	 */
	public static function get_licensing_count(): int {
		if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
			return 0;
		}

		$cart_items = edd_get_cart_contents();
		$count      = 0;

		if ( empty( $cart_items ) ) {
			return $count;
		}

		foreach ( $cart_items as $item ) {
			$download_id = $item['id'];
			$download    = new \EDD_SL_Download( $download_id );
			if ( $download->licensing_enabled() ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Get the count of license renewals in the cart.
	 *
	 * @return int The count of license renewals in the cart.
	 */
	public static function get_license_renewal_count(): int {
		if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
			return 0;
		}

		$cart_items = edd_get_cart_contents();
		$count      = 0;

		if ( empty( $cart_items ) ) {
			return $count;
		}

		foreach ( $cart_items as $item ) {
			$options = $item['options'] ?? array();
			if ( ! empty( $options['is_renewal'] ) && isset( $options['license_id'] ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Retrieve a count of products in the cart based on a callback.
	 *
	 * @param callable $check_callback The callback to check if the item is valid.
	 *
	 * @return int|null The calculated product count or null if no items.
	 */
	public static function get_count_by_callback( callable $check_callback ): ?int {
		if ( ! is_callable( $check_callback ) ) {
			return null;
		}

		$count      = 0;
		$cart_items = edd_get_cart_contents();

		if ( ! $cart_items ) {
			return null;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( $check_callback( $cart_item['id'] ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Retrieve a count of products in the cart based on a meta key.
	 *
	 * @param string $meta_key The meta key to check for each product.
	 *
	 * @return int|null The calculated product count or null if no items.
	 */
	public static function get_count_by_meta( string $meta_key ): ?int {
		if ( ! $meta_key ) {
			return null;
		}

		$count      = 0;
		$cart_items = edd_get_cart_contents();

		if ( ! $cart_items ) {
			return null;
		}

		foreach ( $cart_items as $cart_item ) {
			if ( get_post_meta( $cart_item['id'], $meta_key, true ) ) {
				$count ++;
			}
		}

		return $count;
	}

}