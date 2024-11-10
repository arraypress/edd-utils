<?php
/**
 * Log Utilities for Easy Digital Downloads (EDD)
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

class Log {

	/**
	 * Check if the log exists in the database.
	 *
	 * @param int $log_id The ID of the log to check.
	 *
	 * @return bool True if the log exists, false otherwise.
	 */
	public static function exists( int $log_id ): bool {
		return Exists::row( 'edd_logs', 'id', $log_id );
	}

	/**
	 * Get a specific field from a log.
	 *
	 * @param int    $log_id The log ID.
	 * @param string $field  The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $log_id, string $field ) {
		// Bail if no log ID was passed.
		if ( empty( $log_id ) ) {
			return null;
		}

		// Get the log object
		$log = edd_get_log( $log_id );

		// If log doesn't exist, return null
		if ( ! $log ) {
			return null;
		}

		// First, check if it's a property of the log object
		if ( isset( $log->$field ) ) {
			return $log->$field;
		}

		// If not found in log object, check log meta
		$meta_value = edd_get_log_meta( $log_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

}