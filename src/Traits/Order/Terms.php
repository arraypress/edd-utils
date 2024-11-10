<?php
/**
 * Item Operations Trait for Easy Digital Downloads (EDD) Orders
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

use ArrayPress\Utils\Terms\Terms as CoreTerms;

trait Terms {

	/**
	 * Retrieve unique terms for order items.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $taxonomy The taxonomy to retrieve terms from.
	 *
	 * @return array|null Array of term objects or null if no items found.
	 */
	public static function get_terms( int $order_id, string $taxonomy = 'download_category' ): ?array {
		if ( ! $order_id || ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 99999,
		] );

		if ( ! $order_items ) {
			return null;
		}

		$terms = [];

		foreach ( $order_items as $order_item ) {
			$product_terms = get_the_terms( $order_item->product_id, $taxonomy );
			if ( $product_terms && ! is_wp_error( $product_terms ) ) {
				$terms = array_merge( $terms, $product_terms );
			}
		}

		// Remove duplicate terms by term ID
		$unique_terms = [];
		foreach ( $terms as $term ) {
			$unique_terms[ $term->term_id ] = $term;
		}

		return ! empty( $unique_terms ) ? array_values( $unique_terms ) : null;
	}

	/**
	 * Retrieve unique term IDs for order items.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $taxonomy The taxonomy to retrieve terms from.
	 *
	 * @return array|null The unique term IDs or null if no items found.
	 */
	public static function get_term_ids( int $order_id, string $taxonomy = 'download_category' ): ?array {
		$terms = self::get_terms( $order_id, $taxonomy );

		if ( null === $terms ) {
			return null;
		}

		return array_map( 'absint', wp_list_pluck( $terms, 'term_id' ) );
	}

	/**
	 * Check if an order contains a specific term.
	 *
	 * @param int    $order_id The order ID.
	 * @param mixed  $term     The term to check for (ID, name, or slug).
	 * @param string $taxonomy The taxonomy to check the term in.
	 *
	 * @return bool True if the term is found, false otherwise.
	 */
	public static function has_term( int $order_id, $term, string $taxonomy = 'download_category' ): bool {
		$order_term_ids = self::get_term_ids( $order_id, $taxonomy );

		if ( null === $order_term_ids ) {
			return false;
		}

		return CoreTerms::exists_in( [ $term ], $order_term_ids, $taxonomy, false );
	}

	/**
	 * Check if an order contains all or any of the specified terms.
	 *
	 * @param int    $order_id  The order ID.
	 * @param array  $terms     An array of terms to check for (IDs, names, or slugs).
	 * @param string $taxonomy  The taxonomy to check the terms in.
	 * @param bool   $match_all Whether all terms must be present (true) or any term (false).
	 *
	 * @return bool True if the specified terms are found according to match_all parameter.
	 */
	public static function has_terms( int $order_id, array $terms, string $taxonomy = 'download_category', bool $match_all = true ): bool {
		$order_term_ids = self::get_term_ids( $order_id, $taxonomy );

		if ( null === $order_term_ids ) {
			return false;
		}

		return CoreTerms::exists_in( $terms, $order_term_ids, $taxonomy, $match_all );
	}

}