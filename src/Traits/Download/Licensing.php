<?php
/**
 * Licensing Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides licensing and subscription-related functionality for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use ArrayPress\EDD\Downloads\Download;
use EDD_Download;

trait Licensing {

	/**
	 * Get licensing details for a product.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional price ID
	 *
	 * @return array Licensing details
	 */
	public static function get_licensing_details( int $download_id, ?int $price_id = null ): array {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return self::get_default_licensing_details();
		}

		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return self::get_default_licensing_details();
		}

		$licensing   = edd_software_licensing();
		$is_variable = edd_has_variable_prices( $download_id );

		return [
			'licenses'    => (int) ( $is_variable
				? $licensing->get_price_activation_limit( $download_id, $price_id )
				: $licensing->get_price_activation_limit( $download_id ) ),
			'is_lifetime' => $is_variable
				? $licensing->get_price_is_lifetime( $download_id, $price_id )
				: $licensing->get_price_is_lifetime( $download_id )
		];
	}

	/**
	 * Get default licensing details structure.
	 *
	 * @return array Default licensing details
	 */
	private static function get_default_licensing_details(): array {
		return [
			'licenses'    => 0,
			'is_lifetime' => false,
		];
	}

}