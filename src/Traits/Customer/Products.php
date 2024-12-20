<?php
/**
 * Product Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides product-related operations for EDD customers, handling purchased products,
 * bundles, and related term operations.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\EDD\Customers\Customer;

trait Products {

	/**
	 * Retrieve the product IDs (and optionally price IDs) of the customer in an array.
	 *
	 * @param int   $customer_id       The ID of the customer whose product IDs are being retrieved.
	 * @param array $status            Optional. The statuses of orders to consider when retrieving IDs.
	 * @param bool  $include_price_ids Optional. Whether to include price IDs in the result. Default false.
	 *
	 * @return array|null An array of unique product IDs (or product and price ID combinations) for the customer, or
	 *                    null if an error occurred.
	 */
	public static function get_product_ids( int $customer_id, array $status = [], bool $include_price_ids = false ): ?array {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		// Get order IDs
		$order_ids = $customer->get_order_ids( $status );

		if ( empty( $order_ids ) ) {
			return [];
		}

		$count_args = [
			'order_id__in' => $order_ids,
			'status__in'   => edd_get_deliverable_order_item_statuses(),
		];

		// Get total orders.
		$count = edd_count_order_items( $count_args );

		// Define fields to retrieve based on whether price IDs should be included
		$fields = $include_price_ids ? [ 'product_id', 'price_id' ] : 'product_id';

		// Get product IDs and price IDs (if requested) from order items.
		$order_items = edd_get_order_items( [
			'order_id__in'  => $order_ids,
			'fields'        => $fields,
			'no_found_rows' => true,
			'number'        => $count
		] );

		// Ensure $order_items is iterable
		if ( ! is_array( $order_items ) || empty( $order_items ) ) {
			return [];
		}

		// Extract product IDs and price IDs (if requested), combine them, and remove duplicates
		$result_ids = array_map( function ( $item ) use ( $include_price_ids ) {
			if ( $include_price_ids ) {
				$product_id = absint( $item['product_id'] );
				$price_id   = isset( $item['price_id'] ) ? absint( $item['price_id'] ) : null;

				return $price_id !== null ? "{$product_id}_{$price_id}" : (string) $product_id;
			} else {
				return (string) absint( $item );
			}
		}, $order_items );

		return array_unique( $result_ids );
	}

}