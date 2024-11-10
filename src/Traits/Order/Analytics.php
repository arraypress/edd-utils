<?php
/**
 * Category Operations Trait for Easy Digital Downloads (EDD) Orders
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

use ArrayPress\EDD\Downloads\Downloads;

trait Analytics {

	/**
	 * Check if an order contains popular products.
	 *
	 * @param int    $order_id  The order ID to check.
	 * @param string $criterion The criterion for comparison ('earnings' or 'sales').
	 *
	 * @return bool True if the order contains popular products, false otherwise.
	 */
	public static function contains_popular_product( int $order_id, string $criterion = 'earnings' ): bool {
		$order_items = edd_get_order_items( [
			'order_id'       => $order_id,
			'status__not_in' => edd_get_deliverable_order_item_statuses(),
			'number'         => 99999,
		] );

		if ( empty( $order_items ) ) {
			return false;
		}

		$order_item_ids = wp_list_pluck( $order_items, 'product_id' );
		$popular_ids    = Downloads::get_top_products( 10, $criterion );

		return ! empty( array_intersect( $order_item_ids, $popular_ids ) );
	}

}