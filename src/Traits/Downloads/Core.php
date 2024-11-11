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
use ArrayPress\Utils\Common\Sanitize;

trait Core {

	/**
	 * Get the total count of download posts.
	 *
	 * @param string|array $status Optional. Post status or array of post statuses. Default 'any'.
	 *
	 * @return int The total count of download posts.
	 */
	public static function get_count( string $status = 'any' ): int {
		$counts = wp_count_posts( 'download' );

		if ( $status === 'any' ) {
			return array_sum( (array) $counts );
		}

		if ( is_string( $status ) ) {
			return (int) ( $counts->$status ?? 0 );
		}

		return array_sum( array_intersect_key(
			(array) $counts,
			array_flip( $status )
		) );
	}

	/**
	 * Calculate the total price of specified downloads.
	 *
	 * @param array      $download_ids Array of download IDs
	 * @param array|null $price_ids    Optional. Associative array of download_id => price_id pairs
	 *
	 * @return float Total price of all downloads
	 */
	public static function calculate_total_price( array $download_ids, ?array $price_ids = null ): float {
		if ( empty( $download_ids ) ) {
			return 0.00;
		}

		return array_reduce( $download_ids, function ( $sum, $download_id ) use ( $price_ids ) {
			return $sum + Download::get_price(
					$download_id,
					$price_ids[ $download_id ] ?? null
				);
		}, 0.00 );
	}

}