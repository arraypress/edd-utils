<?php
/**
 * Terms Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides term-related operations for customer products.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use EDD_Customer;
use ArrayPress\Utils\Terms\Terms as CoreTerms;


trait Terms {
	use Core;
	use Products;

	/**
	 * Retrieve terms associated with a customer's purchased products.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $taxonomy    The taxonomy to retrieve terms from.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return array|null Array of term objects or null if no items found.
	 */
	public static function get_terms( int $customer_id, string $taxonomy = 'download_category', array $status = [] ): ?array {
		// Validate customer and taxonomy
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		// Get product IDs
		$product_ids = self::get_product_ids( $customer_id, $status );
		if ( empty( $product_ids ) ) {
			return null;
		}

		// Get all terms for all products
		$terms = [];
		foreach ( $product_ids as $product_id ) {
			$product_terms = get_the_terms( $product_id, $taxonomy );
			if ( $product_terms && ! is_wp_error( $product_terms ) ) {
				$terms = array_merge( $terms, $product_terms );
			}
		}

		// Remove duplicate terms
		$unique_terms = [];
		foreach ( $terms as $term ) {
			$unique_terms[ $term->term_id ] = $term;
		}

		return ! empty( $unique_terms ) ? array_values( $unique_terms ) : null;
	}

	/**
	 * Retrieve term IDs associated with a customer's purchased products.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $taxonomy    The taxonomy to retrieve terms from.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return array|null Array of term IDs or null if no items found.
	 */
	public static function get_term_ids( int $customer_id, string $taxonomy = 'download_category', array $status = [] ): ?array {
		$terms = self::get_terms( $customer_id, $taxonomy, $status );

		if ( null === $terms ) {
			return null;
		}

		return array_map( 'absint', wp_list_pluck( $terms, 'term_id' ) );
	}

	/**
	 * Check if a customer has purchased products with a specific term.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param mixed  $term        The term to check for (ID, name, or slug).
	 * @param string $taxonomy    The taxonomy to check the term in.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return bool True if the term is found, false otherwise.
	 */
	public static function has_term( int $customer_id, $term, string $taxonomy = 'download_category', array $status = [] ): bool {
		$term_ids = self::get_term_ids( $customer_id, $taxonomy, $status );

		if ( null === $term_ids ) {
			return false;
		}

		return CoreTerms::exists_in( [ $term ], $term_ids, $taxonomy, false );
	}

	/**
	 * Check if a customer has purchased products with specified terms.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param array  $terms       Array of terms to check for (IDs, names, or slugs).
	 * @param string $taxonomy    The taxonomy to check terms in.
	 * @param bool   $match_all   Whether all terms must be present (true) or any term (false).
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return bool True if specified terms are found according to match_all parameter.
	 */
	public static function has_terms( int $customer_id, array $terms, string $taxonomy = 'download_category', bool $match_all = true, array $status = [] ): bool {
		$term_ids = self::get_term_ids( $customer_id, $taxonomy, $status );

		if ( null === $term_ids ) {
			return false;
		}

		return CoreTerms::exists_in( $terms, $term_ids, $taxonomy, $match_all );
	}

	/**
	 * Get term slugs for customer's purchased products.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $taxonomy    The taxonomy to retrieve terms from.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return array|null Array of term slugs or null if no items found.
	 */
	public static function get_term_slugs( int $customer_id, string $taxonomy = 'download_category', array $status = [] ): ?array {
		$terms = self::get_terms( $customer_id, $taxonomy, $status );

		if ( null === $terms ) {
			return null;
		}

		return wp_list_pluck( $terms, 'slug' );
	}

	/**
	 * Get term names for customer's purchased products.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $taxonomy    The taxonomy to retrieve terms from.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return array|null Array of term names or null if no items found.
	 */
	public static function get_term_names( int $customer_id, string $taxonomy = 'download_category', array $status = [] ): ?array {
		$terms = self::get_terms( $customer_id, $taxonomy, $status );

		if ( null === $terms ) {
			return null;
		}

		return wp_list_pluck( $terms, 'name' );
	}

	/**
	 * Count terms associated with a customer's purchased products.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $taxonomy    The taxonomy to count terms from.
	 * @param array  $status      Optional. Array of order statuses to consider.
	 *
	 * @return int Number of unique terms.
	 */
	public static function count_terms( int $customer_id, string $taxonomy = 'download_category', array $status = [] ): int {
		$terms = self::get_terms( $customer_id, $taxonomy, $status );

		return $terms ? count( $terms ) : 0;
	}

}