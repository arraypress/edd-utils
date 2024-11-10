<?php
/**
 * Core Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides core functionality and helper methods for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

trait Generate {

	/**
	 * Generates a unique identifier for a product based on its ID and optional price ID.
	 *
	 * @param int      $product_id The ID of the product.
	 * @param int|null $price_id   The ID of the price variation, or null if there is no variation.
	 *
	 * @return string The unique identifier for the product.
	 */
	function generate_identifier( int $product_id, ?int $price_id = null ): string {
		return $product_id . ( isset( $price_id ) ? '_' . $price_id : '' );
	}

}