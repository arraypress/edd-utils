<?php
/**
 * Item Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * Provides methods for handling order items and their properties.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

use ArrayPress\Utils\Terms\Terms;
use EDD\Database\Queries\Order_Item;

trait Products {

	/**
	 * Retrieve a count of products in an order based on a callback.
	 *
	 * @param int      $order_id       The order ID.
	 * @param callable $check_callback The callback to check if the item is valid.
	 *
	 * @return int|null The calculated product count or null if no items.
	 */
	public static function get_product_count_by_callback( int $order_id, callable $check_callback ): ?int {
		if ( ! $order_id || ! is_callable( $check_callback ) ) {
			return null;
		}

		$count       = 0;
		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 99999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		foreach ( $order_items as $order_item ) {
			if ( $check_callback( $order_item->product_id ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Retrieve a count of products in an order based on a meta key.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $meta_key The meta key to check for each product.
	 *
	 * @return int|null The calculated product count or null if no items.
	 */
	public static function get_product_count_by_meta( int $order_id, string $meta_key ): ?int {
		if ( ! $order_id || ! $meta_key ) {
			return null;
		}

		$count       = 0;
		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 99999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		foreach ( $order_items as $order_item ) {
			if ( get_post_meta( $order_item->product_id, $meta_key, true ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Retrieve the total value for products in an order based on a callback.
	 *
	 * @param int      $order_id       The order ID.
	 * @param callable $check_callback The callback to check if the item is valid.
	 * @param callable $total_callback The callback to calculate the total value.
	 * @param bool     $use_price_id   Whether to pass the price ID to the total callback.
	 *
	 * @return int|null The total product value or null if no items.
	 */
	public static function get_product_total_by_callbacks( int $order_id, callable $check_callback, callable $total_callback, bool $use_price_id = false ): ?int {
		if ( ! $order_id || ! is_callable( $check_callback ) || ! is_callable( $total_callback ) ) {
			return null;
		}

		$total       = 0;
		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 99999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		foreach ( $order_items as $order_item ) {
			if ( $check_callback( $order_item->product_id ) ) {
				if ( $use_price_id ) {
					$total += $total_callback( $order_item->product_id, $order_item->price_id );
				} else {
					$total += $total_callback( $order_item->product_id );
				}
			}
		}

		return $total;
	}

	/**
	 * Get the unique author IDs from the order items.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array An array of unique author IDs.
	 */
	public static function get_product_authors( int $order_id ): array {
		if ( empty( $order_id ) ) {
			return [];
		}

		$order_items = edd_get_order_items( [
			'order_id'      => $order_id,
			'no_found_rows' => true,
			'number'        => 99999,
			'status__in'    => edd_get_deliverable_order_item_statuses(),
			'fields'        => 'product_id'
		] );

		$author_ids = [];

		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $product_id ) {
				$author_id = get_post_field( 'post_author', $product_id );
				if ( ! empty( $author_id ) ) {
					$author_ids[] = $author_id;
				}
			}
			$author_ids = array_unique( $author_ids );
		}

		return $author_ids;
	}

	/**
	 * Retrieve an array of product IDs from an order.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array|null Array of product IDs or null if none found.
	 */
	public static function get_product_ids( int $order_id ): ?array {
		if ( ! $order_id ) {
			return null;
		}

		$order_items = edd_get_order_items( [
			'order_id'      => $order_id,
			'orderby'       => 'cart_index',
			'order'         => 'ASC',
			'no_found_rows' => true,
			'status__in'    => edd_get_deliverable_order_item_statuses(),
			'number'        => 999999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		$product_ids = array_map( function ( $order_item ) {
			return $order_item->product_id;
		}, $order_items );

		return array_unique( $product_ids ) ?: null;
	}

	/**
	 * Retrieve a flat array with unique concatenated product IDs and price IDs.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array|null The flat array of unique concatenated product IDs and price IDs.
	 */
	public static function get_product_ids_concatenated( int $order_id ): ?array {
		if ( ! $order_id ) {
			return null;
		}

		$order_items = edd_get_order_items( [
			'order_id'      => $order_id,
			'orderby'       => 'cart_index',
			'order'         => 'ASC',
			'no_found_rows' => true,
			'status__in'    => edd_get_deliverable_order_item_statuses(),
			'number'        => 999999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		$product_ids = [];

		foreach ( $order_items as $order_item ) {
			$concatenated_id = (string) $order_item->product_id;
			if ( ! is_null( $order_item->price_id ) ) {
				$concatenated_id .= '_' . $order_item->price_id;
			}
			$product_ids[] = $concatenated_id;
		}

		return array_unique( $product_ids ) ?: null;
	}

	/**
	 * Get the total earnings for order items.
	 *
	 * @param int    $order_id  The ID of the order.
	 * @param bool   $formatted Whether to format the earnings as a display amount.
	 * @param string $meta_key  Optional. The meta key to include in the meta query.
	 *
	 * @return float|string The total earnings.
	 */
	public static function get_product_total( int $order_id, bool $formatted = false, string $meta_key = '' ) {
		if ( empty( $order_id ) ) {
			return 0.00;
		}

		$args = [
			'number'   => - 1,
			'order_id' => $order_id,
			'fields'   => 'total',
		];

		if ( ! empty( $meta_key ) ) {
			$args['meta_query'] = [
				[
					'key'     => $meta_key,
					'value'   => '',
					'compare' => '!=',
				],
			];
		}

		$order_items = new Order_Item( $args );

		// Use array_reduce for a more functional approach to sum the totals
		$earnings = $order_items->items
			? array_reduce(
				$order_items->items,
				fn( $carry, $item ) => $carry + (float) $item,
				0.00
			)
			: 0.00;

		if ( $formatted ) {
			$order    = edd_get_order( $order_id );
			$currency = $order ? $order->currency : edd_get_currency();

			return edd_currency_filter(
				edd_format_amount( $earnings ),
				$currency
			);
		}

		return $earnings;
	}

}