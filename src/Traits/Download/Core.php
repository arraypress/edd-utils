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

use EDD_Download;

trait Core {

	/**
	 * Get and validate a download object.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null Download object or null if invalid
	 */
	protected static function get_validated( int $download_id = 0 ): ?EDD_Download {
		if ( empty( $download_id ) ) {
			$download_id = get_the_ID();
		}

		$download = edd_get_download( $download_id );

		return ( $download instanceof EDD_Download ) ? $download : null;
	}

	/**
	 * Get the download type.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return string|false The product type or false if invalid
	 */
	public static function get_type( int $download_id = 0 ) {
		$download = self::get_validated( $download_id );

		return $download ? $download->get_type() : false;
	}

	/**
	 * Get the "Add to Cart" URL for a download.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional price ID for variable-priced downloads
	 *
	 * @return string|false URL or false if invalid
	 */
	public static function get_add_to_cart_url( int $download_id = 0, ?int $price_id = null ) {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return false;
		}

		$url_args = [
			'edd_action'  => 'add_to_cart',
			'download_id' => $download->ID,
		];

		if ( $price_id !== null ) {
			$url_args['price_id'] = absint( $price_id );
		}

		return add_query_arg( $url_args, edd_get_checkout_uri() );
	}

}