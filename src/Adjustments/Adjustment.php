<?php
/**
 * Adjustment Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Adjustments;

use ArrayPress\Utils\Database\Exists;

class Adjustment {

	/**
	 * Check if the adjustment exists in the database.
	 *
	 * @param int $adjustment_id The ID of the adjustment to check.
	 *
	 * @return bool True if the adjustment exists, false otherwise.
	 */
	public static function exists( int $adjustment_id ): bool {
		return Exists::row( 'edd_adjustments', 'id', $adjustment_id );
	}

	/**
	 * Get a specific field from an adjustment.
	 *
	 * @param int    $adjustment_id The adjustment ID.
	 * @param string $field         The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $adjustment_id, string $field ) {
		// Bail if no adjustment ID was passed.
		if ( empty( $adjustment_id ) ) {
			return null;
		}

		// Get the adjustment object
		$adjustment = edd_get_adjustment( $adjustment_id );

		// If adjustment doesn't exist, return null
		if ( ! $adjustment ) {
			return null;
		}

		// First, check if it's a property of the adjustment object
		if ( isset( $adjustment->$field ) ) {
			return $adjustment->$field;
		}

		// If not found in adjustment object, check adjustment meta
		$meta_value = edd_get_adjustment_meta( $adjustment_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

	/**
	 * Check if a given adjustment is of a specific type.
	 *
	 * @param int    $adjustment_id The ID of the adjustment to check.
	 * @param string $type          The expected type of the adjustment. Default empty.
	 *
	 * @return bool True if the adjustment is of the specified type, false otherwise.
	 */
	public static function is_type( int $adjustment_id = 0, string $type = '' ): bool {
		$adjustment_type = self::get_field( $adjustment_id, 'type' );

		return $adjustment_type && strtolower( $type ) === strtolower( $adjustment_type );
	}

}