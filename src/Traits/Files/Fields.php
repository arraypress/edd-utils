<?php
/**
 * File Identifiers Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling file identifiers, keys, and related operations in EDD downloads.
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
use EDD_Download;

trait Fields {

	/**
	 * Required trait method for getting validated download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null
	 */
	abstract protected static function get_validated( int $download_id = 0 ): ?EDD_Download;

	/**
	 * Get file ID based on field comparison (highest or lowest).
	 *
	 * @param int    $download_id Download ID. If 0, attempts to get the current post ID.
	 * @param string $field       The field to compare for determining the price.
	 * @param string $type        The type of comparison ('min' or 'max').
	 *
	 * @return int|null File ID, null if download does not exist or has no files.
	 */
	private static function get_file_id_by_field( int $download_id = 0, string $field = '', string $type = 'min' ): ?int {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$download_files = edd_get_download_files( $download->ID );
		if ( empty( $download_files ) ) {
			return null;
		}

		$list_handler = new ListHandler( $download_files );
		$key          = $list_handler->search( $field, $type );

		return $key !== false ? absint( $key ) : null;
	}

	/**
	 * Retrieves the ID for the file with the lowest field value.
	 *
	 * @param int    $download_id Download ID. If 0, attempts to get the current post ID.
	 * @param string $field       The field to compare for determining the lowest value.
	 *
	 * @return int|null File ID, null if download does not exist or has no files.
	 */
	public static function get_file_lowest_id( int $download_id = 0, string $field = '' ): ?int {
		return self::get_file_id_by_field( $download_id, $field, 'min' );
	}

	/**
	 * Retrieves the ID for the file with the highest field value.
	 *
	 * @param int    $download_id Download ID. If 0, attempts to get the current post ID.
	 * @param string $field       The field to compare for determining the highest value.
	 *
	 * @return int|null File ID, null if download does not exist or has no files.
	 */
	public static function get_file_highest_id( int $download_id = 0, string $field = '' ): ?int {
		return self::get_file_id_by_field( $download_id, $field, 'max' );
	}

}