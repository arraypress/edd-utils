<?php
/**
 * Order Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides order-related functionality for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use EDD_Download;
use EDD\Database\Queries\Order_Item;

trait Orders {
	use Core;

	/**
	 * Get order IDs for purchases of a download.
	 *
	 * @param int    $download_id Optional. Download ID. Will use current post ID if not provided.
	 * @param string $status      Optional. Order status to filter by. Default 'complete'.
	 *
	 * @return array Array of order IDs
	 */
	public static function get_order_ids( int $download_id = 0, string $status = 'complete' ): array {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return [];
		}

		$count = edd_count_order_items( [ 'product_id' => $download->ID ] );
		$ids   = edd_get_order_items( [
			'product_id'    => $download->ID,
			'status'        => $status,
			'number'        => $count,
			'fields'        => 'order_id',
			'no_found_rows' => true
		] );

		return array_values( array_map( 'absint', $ids ) );
	}

	/**
	 * Get the first purchase date for a download.
	 *
	 * @param int $download_id Optional. Download ID. Will use current post ID if not provided.
	 *
	 * @return string|null First purchase date or null if never purchased
	 */
	public static function get_first_purchase_date( int $download_id = 0 ): ?string {
		return self::get_purchase_date( $download_id, 'first' );
	}

	/**
	 * Get the last purchase date for a download.
	 *
	 * @param int $download_id Optional. Download ID. Will use current post ID if not provided.
	 *
	 * @return string|null Last purchase date or null if never purchased
	 */
	public static function get_last_purchase_date( int $download_id = 0 ): ?string {
		return self::get_purchase_date( $download_id );
	}

	/**
	 * Get the purchase date for a download based on specified criteria.
	 *
	 * @param int    $download_id Optional. Download ID. Will use current post ID if not provided.
	 * @param string $type        Optional. Type of date to retrieve ('first' or 'last'). Default 'last'.
	 *
	 * @return string|null Purchase date or null if never purchased
	 */
	public static function get_purchase_date( int $download_id = 0, string $type = 'last' ): ?string {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$order = $type === 'first' ? 'ASC' : 'DESC';

		$order_items = edd_get_order_items( [
			'number'     => 1,
			'product_id' => $download->ID,
			'type'       => 'download',
			'order'      => $order
		] );

		return ! empty( $order_items ) && isset( $order_items[0] )
			? $order_items[0]->date_created
			: null;
	}

	/**
	 * Get suggested product IDs based on order history.
	 *
	 * Returns an array of product IDs that other customers have purchased along with
	 * this product, excluding products the specified customer already owns.
	 *
	 * @param int $download_id Optional. Download ID. Will use current post ID if not provided.
	 * @param int $customer_id Optional. Customer ID to exclude their purchased products.
	 * @param int $limit       Optional. Number of suggestions to return. Default 5.
	 *
	 * @return array Array of suggested product IDs, ordered by popularity
	 */
	public static function get_recommendations( int $download_id = 0, int $customer_id = 0, int $limit = 5 ): array {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return [];
		}

		// Get order IDs where this product was purchased
		$order_ids = self::get_order_ids( $download->ID );
		if ( empty( $order_ids ) ) {
			return [];
		}

		// Start with excluding the current product
		$exclusions = [ $download->ID ];

		// Add customer's purchases to exclusions if customer ID provided
		if ( $customer_id > 0 ) {
			$customer = edd_get_customer( $customer_id );
			if ( $customer && ! empty( $customer->email ) ) {
				$customer_purchases = edd_get_users_purchased_products( $customer->email );
				if ( ! empty( $customer_purchases ) ) {
					$purchase_ids = wp_list_pluck( $customer_purchases, 'ID' );
					$exclusions   = array_unique( array_merge( $exclusions, $purchase_ids ) );
				}
			}
		}

		// Get suggestions with counts
		$query = new Order_Item( [
			'order_id__in'       => $order_ids,
			'product_id__not_in' => $exclusions,
			'status__in'         => edd_get_deliverable_order_item_statuses(),
			'number'             => 9999999,
			'orderby'            => 'count',
			'groupby'            => 'product_id'
		] );

		$counts = $query->get_results();

		// Extract product IDs ordered by count
		$suggestion_ids = [];
		if ( ! empty( $counts ) ) {
			foreach ( $counts as $count ) {
				if ( isset( $count->product_id ) ) {
					$suggestion_ids[] = absint( $count->product_id );
					if ( count( $suggestion_ids ) >= $limit ) {
						break;
					}
				}
			}
		}

		return $suggestion_ids;
	}

}