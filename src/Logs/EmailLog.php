<?php
/**
 * Email Log Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Logs;

use EDD\Database\Queries\LogEmail;
use ArrayPress\Utils\Database\Exists;

class EmailLog {

	/**
	 * Check if the email log exists in the database.
	 *
	 * @param int $email_log_id The ID of the email log to check.
	 *
	 * @return bool True if the email log exists, false otherwise.
	 */
	public static function exists( int $email_log_id ): bool {
		return Exists::row( 'edd_logs_emails', 'id', $email_log_id );
	}

	/**
	 * Get a field from an email log object.
	 *
	 * @param int    $email_log_id Email Log ID.
	 * @param string $field        Field to retrieve from object.
	 *
	 * @return mixed|null Null if email log does not exist. Value of log field if exists.
	 */
	public static function get_field( int $email_log_id = 0, string $field = '' ) {
		$email_log = self::get( $email_log_id );

		return $email_log->{$field} ?? null;
	}

	/**
	 * Add an email log.
	 *
	 * @param array $data Array of email log data.
	 *
	 * @return int|false ID of inserted email log, false on error.
	 */
	public static function add( array $data = [] ) {
		$email_logs = new LogEmail();

		return $email_logs->add_item( $data );
	}

	/**
	 * Delete an email log.
	 *
	 * @param int $email_log_id Email log ID.
	 *
	 * @return int|false `1` if the log was deleted successfully, false on error.
	 */
	public static function delete( int $email_log_id = 0 ) {
		$email_logs = new LogEmail();

		return $email_logs->delete_item( $email_log_id );
	}

	/**
	 * Update an email log.
	 *
	 * @param int   $email_log_id Email log ID.
	 * @param array $data         Array of email log data to update.
	 *
	 * @return int|false Number of rows updated if successful, false otherwise.
	 */
	public static function update( int $email_log_id = 0, array $data = [] ) {
		$email_logs = new LogEmail();

		return $email_logs->update_item( $email_log_id, $data );
	}

	/**
	 * Get an email log by ID.
	 *
	 * @param int $email_log_id Email log ID.
	 *
	 * @return object|false Email log object if successful, false otherwise.
	 */
	public static function get( int $email_log_id = 0 ) {
		$email_logs = new LogEmail();

		return $email_logs->get_item( $email_log_id );
	}

	/**
	 * Get an email log by a specific field value.
	 *
	 * @param string $field Database table field.
	 * @param string $value Value of the row.
	 *
	 * @return object|false Email log object if successful, false otherwise.
	 */
	public static function get_by( string $field = '', string $value = '' ) {
		$email_logs = new LogEmail();

		return $email_logs->get_item_by( $field, $value );
	}

	/**
	 * Query for email logs.
	 *
	 * @param array $args Arguments for the query.
	 *
	 * @return array Array of email log objects.
	 */
	public static function get_logs( array $args = [] ): array {
		$r          = wp_parse_args( $args, [ 'number' => 30 ] );
		$email_logs = new LogEmail();

		return $email_logs->query( $r );
	}

	/**
	 * Count email logs.
	 *
	 * @param array $args Arguments for the query.
	 *
	 * @return int Number of email logs returned based on query arguments passed.
	 */
	public static function count( array $args = [] ): int {
		$r          = wp_parse_args( $args, [ 'count' => true ] );
		$email_logs = new LogEmail( $r );

		return absint( $email_logs->found_items );
	}

}