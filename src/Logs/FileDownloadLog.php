<?php
/**
 * File Download Log Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Logs;

use ArrayPress\Utils\Database\Exists;

class FileDownloadLog {

	/**
	 * Check if the file download log exists in the database.
	 *
	 * @param int $file_download_log_id The ID of the file download log to check.
	 *
	 * @return bool True if the file download log exists, false otherwise.
	 */
	public static function exists( int $file_download_log_id ): bool {
		return Exists::row( 'edd_logs_file_downloads', 'id', $file_download_log_id );
	}

	/**
	 * Get a specific field from a file download log.
	 *
	 * @param int    $file_download_log_id The file download log ID.
	 * @param string $field                The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $file_download_log_id, string $field ) {
		// Bail if no file download log ID was passed.
		if ( empty( $file_download_log_id ) ) {
			return null;
		}

		// Get the file download log object
		$file_download_log = edd_get_file_download_log( $file_download_log_id );

		// If file download log doesn't exist, return null
		if ( ! $file_download_log ) {
			return null;
		}

		// First, check if it's a property of the file download log object
		if ( isset( $file_download_log->$field ) ) {
			return $file_download_log->$field;
		}

		// If not found in file download log object, check file download log meta
		$meta_value = edd_get_file_download_log_meta( $file_download_log_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

}