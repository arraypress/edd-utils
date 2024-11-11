<?php
/**
 * Core Operations Trait for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Downloads;

use ArrayPress\EDD\Downloads\Download;
use ArrayPress\Utils\Common\Sanitize as CommonSanitize;

trait Sanitize {

	/**
	 * Sanitize and validate a list of product IDs, possibly including price IDs.
	 *
	 * @param array|string|int $products Array of product IDs or single product ID.
	 * @param bool             $validate Optional. Whether to validate products exist. Default true.
	 *
	 * @return array<string> Array of sanitized product identifiers (download_id or download_id_price_id).
	 */
	public static function sanitize( $products, bool $validate = true ): array {
		// Handle various input types
		if ( is_string( $products ) || is_int( $products ) ) {
			$products = [ $products ];
		}

		// Convert to array and ensure we have values
		$products = CommonSanitize::object_ids( $products );
		if ( empty( $products ) ) {
			return [];
		}

		$valid_products = [];

		foreach ( $products as $product ) {
			// Parse product and price IDs
			$parsed = edd_parse_product_dropdown_value( $product );
			if ( empty( $parsed['download_id'] ) ) {
				continue;
			}

			$download_id = (int) $parsed['download_id'];

			// Skip validation if not required
			if ( ! $validate ) {
				if ( isset( $parsed['price_id'] ) ) {
					$valid_products[] = $download_id . '_' . (int) $parsed['price_id'];
				} else {
					$valid_products[] = (string) $download_id;
				}
				continue;
			}

			// Validate download exists
			$download = Download::get_validated( $download_id );
			if ( ! $download ) {
				continue;
			}

			// Handle price IDs if present
			if ( isset( $parsed['price_id'] ) ) {
				$price_id = (int) $parsed['price_id'];
				$prices = $download->get_prices();

				// Validate price ID exists
				if ( is_array( $prices ) && isset( $prices[ $price_id ] ) ) {
					$valid_products[] = $download_id . '_' . $price_id;
				}
			} else {
				$valid_products[] = (string) $download_id;
			}
		}

		return array_unique( $valid_products );
	}

}