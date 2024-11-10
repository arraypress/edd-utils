<?php
/**
 * Notes Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Notes;

use function edd_get_note;
use ArrayPress\Utils\Database\Exists;

class Note {

	/**
	 * Check if the note exists in the database.
	 *
	 * @param int $note_id The ID of the note to check.
	 *
	 * @return bool True if the note exists, false otherwise.
	 */
	public static function exists( int $note_id ): bool {
		return Exists::row( 'edd_notes', 'id', $note_id );
	}

	/**
	 * Get a specific field from a note.
	 *
	 * @param int    $note_id The note ID.
	 * @param string $field   The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $note_id, string $field ) {
		// Bail if no log ID was passed.
		if ( empty( $note_id ) ) {
			return null;
		}

		// Get the log object
		$note = edd_get_note( $note_id );

		// If log doesn't exist, return null
		if ( ! $note ) {
			return null;
		}

		// First, check if it's a property of the log object
		if ( isset( $note->$field ) ) {
			return $note->$field;
		}

		// If not found in log object, check log meta
		$meta_value = edd_get_note_meta( $note_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

}