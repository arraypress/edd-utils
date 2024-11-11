<?php
/**
 * File Price Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling file price operations in EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Files;

use EDD\Utils\ListHandler;
use ArrayPress\EDD\Downloads\Download;
use EDD_Download;

trait Price {

	/**
	 * Retrieve the download file price ID for this variable product.
	 *
	 * @param int $download_id Download ID.
	 * @param int $file_key    File key.
	 *
	 * @return int|null File price ID, null if download was not found or has no variable prices.
	 */
	public static function get_file_price_id( int $download_id = 0, int $file_key = 0 ): ?int {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		if ( ! edd_has_variable_prices( $download_id ) ) {
			return null;
		}

		$price = edd_get_file_price_condition( $download_id, $file_key );

		if ( ! is_numeric( $price ) ) {
			$price = self::get_default_variable_price( $download_id );
		}

		return $price !== false ? (int) $price : null;
	}

	/**
	 * Get file amount based on field comparison (highest or lowest).
	 *
	 * @param int    $download_id  Download ID. If 0, attempts to get the current post ID.
	 * @param string $field        The field to compare for determining the amount.
	 * @param string $amount_field The field containing the amount value. Default 'amount'.
	 * @param string $type         The type of comparison ('min' or 'max').
	 *
	 * @return float|null Amount value, null if download does not exist, has no files, or no amount field set.
	 */
	private static function get_file_amount_by_field(
		int $download_id = 0,
		string $field = '',
		string $amount_field = 'amount',
		string $type = 'min'
	): ?float {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$download_files = edd_get_download_files( $download->ID );
		if ( empty( $download_files ) ) {
			return null;
		}

		$list_handler = new ListHandler( $download_files );
		$key          = $list_handler->search( $field, $type );

		if ( $key !== false && isset( $download_files[ $key ][ $amount_field ] ) ) {
			return floatval( $download_files[ $key ][ $amount_field ] );
		}

		return null;
	}

	/**
	 * Retrieves the lowest amount for a variable priced download.
	 *
	 * @param int    $download_id  Download ID. If 0, attempts to get the current post ID.
	 * @param string $field        The field to compare for determining the lowest amount.
	 * @param string $amount_field The field containing the amount value. Default 'amount'.
	 *
	 * @return float|null Lowest amount, null if download does not exist, has no files, or no amount field set.
	 *
	 * @example
	 * ```php
	 * // Get lowest amount using default amount field
	 * $amount = Download::get_file_lowest_amount(123);
	 * // Returns: 9.99
	 *
	 * // Get lowest amount using custom amount field
	 * $amount = Download::get_file_lowest_amount(123, 'price', 'custom_amount');
	 * // Returns: 4.99
	 * ```
	 */
	public static function get_file_lowest_amount(
		int $download_id = 0,
		string $field = '',
		string $amount_field = 'amount'
	): ?float {
		return self::get_file_amount_by_field( $download_id, $field, $amount_field, 'min' );
	}

	/**
	 * Retrieves the highest amount for a variable priced download.
	 *
	 * @param int    $download_id  Download ID. If 0, attempts to get the current post ID.
	 * @param string $field        The field to compare for determining the highest amount.
	 * @param string $amount_field The field containing the amount value. Default 'amount'.
	 *
	 * @return float|null Highest amount, null if download does not exist, has no files, or no amount field set.
	 *
	 * @example
	 * ```php
	 * // Get highest amount using default amount field
	 * $amount = Download::get_file_highest_amount(123);
	 * // Returns: 29.99
	 *
	 * // Get highest amount using custom amount field
	 * $amount = Download::get_file_highest_amount(123, 'price', 'premium_price');
	 * // Returns: 49.99
	 * ```
	 */
	public static function get_file_highest_amount(
		int $download_id = 0,
		string $field = '',
		string $amount_field = 'amount'
	): ?float {
		return self::get_file_amount_by_field( $download_id, $field, $amount_field, 'max' );
	}

	/**
	 * Get default variable price for a download.
	 *
	 * @param int $download_id Download ID.
	 *
	 * @return int|false Default price ID or false if not found.
	 */
	private static function get_default_variable_price( int $download_id ) {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return false;
		}

		$prices = $download->get_prices();
		if ( empty( $prices ) ) {
			return false;
		}

		// Return the ID of first price in the array
		return (int) key( $prices );
	}

}