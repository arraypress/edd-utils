<?php
/**
 * Generate Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Common;

class Generate {

	/**
	 * Generate a meta key based on a base key and optional price ID.
	 *
	 * @param string   $base_key The base meta key
	 * @param int|null $price_id Optional. The price ID. Default null.
	 *
	 * @return string The generated meta key
	 */
	public static function product_meta_key( string $base_key, ?int $price_id = null ): string {
		if ( ! is_null( $price_id ) ) {
			return $base_key . '_' . $price_id;
		}

		return $base_key;
	}

	/**
	 * Generates a unique identifier for a product based on its ID and optional price ID.
	 *
	 * @param int      $product_id The ID of the product.
	 * @param int|null $price_id   The ID of the price variation, or null if there is no variation.
	 *
	 * @return string The unique identifier for the product.
	 */
	function product_identifier( int $product_id, ?int $price_id = null ): string {
		return $product_id . ( isset( $price_id ) ? '_' . $price_id : '' );
	}

}