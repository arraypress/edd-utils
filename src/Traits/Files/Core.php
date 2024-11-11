<?php
/**
 * File Metadata Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling file metadata operations in EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Files;

use ArrayPress\Utils\Common\File;
use ArrayPress\Utils\Common\MIME;
use ArrayPress\EDD\Downloads\Download;
use EDD_Download;

trait Core {

	/**
	 * Retrieves metadata for a specific file associated with a download product.
	 *
	 * @param int $download_id The unique identifier for the downloadable product.
	 * @param int $file_id     The specific file ID within the download to query.
	 *
	 * @return array|string|false The file's metadata if found, an empty string if the file key does not exist,
	 *                            and false if no download ID was passed.
	 */
	public static function get_file( int $download_id, int $file_id ): ?string {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		// Fetch downloadable files.
		$download_files = edd_get_download_files( $download_id );

		$retval = null;

		if ( ! empty( $download_files ) && isset( $download_files[ $file_id ] ) ) {
			$retval = $download_files[ $file_id ];
		}

		// Filter & return.
		return apply_filters( 'edd_get_download_file', $retval, $download_id, $file_id );
	}

	/**
	 * Retrieves the specified field value for a download file in Easy Digital Downloads.
	 *
	 * @param int    $download_id The unique identifier for the downloadable product.
	 * @param int    $file_id     The specific file ID within the download to query.
	 * @param string $field       The field whose value is being requested (e.g., 'name', 'file').
	 *
	 * @return mixed The value of the requested field if it exists, null otherwise.
	 */
	public static function get_file_field( int $download_id, int $file_id, string $field ) {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		// Fetch downloadable files.
		$download_files = edd_get_download_files( $download_id );

		$retval = null;

		if ( ! empty( $download_files ) && is_array( $download_files ) ) {
			if ( isset( $download_files[ $file_id ][ $field ] ) ) {
				$retval = $download_files[ $file_id ][ $field ];
			}
		}

		// Filter & return.
		return apply_filters( 'edd_get_download_file_field', $retval, $download_id, $file_id, $field );
	}

	/**
	 * Retrieves the name of a file associated with a download in Easy Digital Downloads.
	 *
	 * @param int $download_id The ID of the downloadable product.
	 * @param int $file_id     The specific file ID within the download to query.
	 *
	 * @return string|false The name of the file if available, or false if the download ID is invalid.
	 */
	public static function get_file_name( int $download_id, int $file_id ) {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		// Fetch downloadable files.
		$download_files = edd_get_download_files( $download_id );

		$retval = '';

		if ( ! empty( $download_files ) && isset( $download_files[ $file_id ] ) ) {
			$retval = ! empty( $download_files[ $file_id ]['name'] )
				? $download_files[ $file_id ]['name']
				: edd_get_file_name( $download_files[ $file_id ] );
		}

		// Filter & return.
		return apply_filters( 'edd_get_download_file_name', $retval, $download_id, $file_id );
	}

	/**
	 * Retrieve the file extension of a download file.
	 *
	 * @param int $download_id The ID of the download.
	 * @param int $file_id     The ID of the file.
	 *
	 * @return string|null The file extension or null if no download ID was passed or file not found.
	 */
	public static function get_file_extension( int $download_id = 0, int $file_id = 0 ): ?string {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$files = edd_get_download_files( $download_id );

		if ( empty( $files ) || ! isset( $files[ $file_id ] ) ) {
			return null;
		}

		$file      = $files[ $file_id ];
		$file_path = $file['file'] ?? '';

		$extension = File::get_extension( $file_path );

		return $extension !== '' ? $extension : null;
	}

	/**
	 * Retrieve the file type of download file.
	 *
	 * @param int $download_id The ID of the download.
	 * @param int $file_id     The ID of the file.
	 *
	 * @return string|null The file type or null if no download ID was passed, file not found, or type couldn't be
	 *                     determined.
	 */
	public static function get_file_type( int $download_id = 0, int $file_id = 0 ): ?string {
		$download = Download::get_validated( $download_id );
		if ( ! $download ) {
			return null;
		}

		$files = edd_get_download_files( $download_id );

		if ( empty( $files ) || ! isset( $files[ $file_id ] ) ) {
			return null;
		}

		$file      = $files[ $file_id ];
		$file_path = $file['file'] ?? '';

		$mime_type = File::get_mime_type( $file_path );

		// If mime type couldn't be determined, return null
		if ( $mime_type === false ) {
			return null;
		}

		return MIME::get_general_type( $mime_type );
	}

}