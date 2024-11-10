<?php
/**
 * Term Operations Trait for Easy Digital Downloads (EDD)
 *
 * This trait provides methods for handling term-related operations in the EDD cart.
 *
 * @package       ArrayPress\EDD\Traits\Cart
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Cart;

use ArrayPress\Utils\Terms\Terms as CoreTerms;

trait Terms {

	/**
	 * Retrieve the unique terms for items in the cart, allowing passing in a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to fetch terms from.
	 *
	 * @return array Unique terms.
	 */
	public static function get_terms( string $taxonomy = 'download_category' ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		$cart_items = edd_get_cart_contents();
		$terms      = [];

		if ( empty( $cart_items ) ) {
			return $terms;
		}

		foreach ( $cart_items as $cart_item ) {
			$product_id    = $cart_item['id'];
			$product_terms = get_the_terms( $product_id, $taxonomy );
			if ( $product_terms && ! is_wp_error( $product_terms ) ) {
				$terms = array_merge( $terms, $product_terms );
			}
		}

		// Remove duplicate terms by term ID
		$unique_terms = [];
		foreach ( $terms as $term ) {
			$unique_terms[ $term->term_id ] = $term;
		}

		return array_values( $unique_terms );
	}

	/**
	 * Retrieve the unique term IDs for items in the cart, allowing passing in a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to fetch term IDs from.
	 *
	 * @return array Unique term IDs.
	 */
	public static function get_term_ids( string $taxonomy = 'download_category' ): array {
		$terms = self::get_terms( $taxonomy );

		return array_map( 'absint', wp_list_pluck( $terms, 'term_id' ) );
	}

	/**
	 * Check if the cart contains a specific term.
	 *
	 * @param mixed  $term     The term to check for (ID, name, or slug).
	 * @param string $taxonomy The taxonomy to check the term in.
	 *
	 * @return bool True if the term is found, false otherwise.
	 */
	public static function has_term( $term, string $taxonomy = 'download_category' ): bool {
		$cart_term_ids = self::get_term_ids( $taxonomy );

		return CoreTerms::exists_in( [ $term ], $cart_term_ids, $taxonomy, false );
	}

	/**
	 * Check if the cart contains all or any of the specified terms.
	 *
	 * @param array  $terms     An array of terms to check for (IDs, names, or slugs).
	 * @param string $taxonomy  The taxonomy to check the terms in.
	 * @param bool   $match_all Whether all terms must be present (true) or any term (false).
	 *
	 * @return bool True if the specified terms are found according to match_all parameter.
	 */
	public static function has_terms( array $terms, string $taxonomy = 'download_category', bool $match_all = true ): bool {
		$cart_term_ids = self::get_term_ids( $taxonomy );

		return CoreTerms::exists_in( $terms, $cart_term_ids, $taxonomy, $match_all );
	}

}