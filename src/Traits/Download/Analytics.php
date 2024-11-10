<?php
/**
 * Analytics Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides analytics and performance-related functionality for EDD downloads.
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
Use ArrayPress\EDD\Downloads\Downloads;

trait Analytics {

	/**
	 * Required trait method for getting validated download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null
	 */
	abstract protected static function get_validated( int $download_id = 0 ): ?EDD_Download;

	/**
	 * Check if a product is among the highest earning products.
	 *
	 * @param int $download_id Download ID
	 * @param int $limit       Number of top products to check against
	 *
	 * @return bool True if product is a top earner
	 */
	public static function is_top_earner( int $download_id, int $limit = 10 ): bool {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return false;
		}

		$top_earners = Downloads::get_highest_earning( $limit );

		return in_array( $download_id, $top_earners, true );
	}

	/**
	 * Check if a product is among the highest selling products.
	 *
	 * @param int $download_id Download ID
	 * @param int $limit       Number of top products to check against
	 *
	 * @return bool True if product is a top seller
	 */
	public static function is_top_seller( int $download_id, int $limit = 10 ): bool {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return false;
		}

		$top_sellers = Downloads::get_highest_selling( $limit );

		return in_array( $download_id, $top_sellers, true );
	}

}