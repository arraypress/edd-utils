<?php
/**
 * API Request Log Utilities for Easy Digital Downloads (EDD)
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

class APIRequestLog {

	/**
	 * Check if the API request log exists in the database.
	 *
	 * @param int $api_request_log_id The ID of the API request log to check.
	 *
	 * @return bool True if the API request log exists, false otherwise.
	 */
	public static function exists( int $api_request_log_id ): bool {
		return Exists::row( 'edd_logs_api_requests', 'id', $api_request_log_id );
	}

	/**
	 * Get a specific field from an API request log.
	 *
	 * @param int    $api_request_log_id The API request log ID.
	 * @param string $field              The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $api_request_log_id, string $field ) {
		// Bail if no API request log ID was passed.
		if ( empty( $api_request_log_id ) ) {
			return null;
		}

		// Get the API request log object
		$api_request_log = edd_get_api_request_log( $api_request_log_id );

		// If API request log doesn't exist, return null
		if ( ! $api_request_log ) {
			return null;
		}

		// First, check if it's a property of the API request log object
		if ( isset( $api_request_log->$field ) ) {
			return $api_request_log->$field;
		}

		// If not found in API request log object, check API request log meta
		$meta_value = edd_get_api_request_log_meta( $api_request_log_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

}