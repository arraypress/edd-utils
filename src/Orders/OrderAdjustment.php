<?php
/**
 * Order Adjustment Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Orders;

use ArrayPress\Utils\Database\Exists;

class OrderAdjustment {

	/**
	 * Check if the order adjustment exists in the database.
	 *
	 * @param int $order_adjustment_id The ID of the order adjustment to check.
	 *
	 * @return bool True if the order adjustment exists, false otherwise.
	 */
	public static function exists( int $order_adjustment_id ): bool {
		return Exists::row( 'edd_order_adjustments', 'id', $order_adjustment_id );
	}

	/**
	 * Get a specific field from an order adjustment object.
	 *
	 * @param int    $order_adjustment_id The order adjustment ID.
	 * @param string $field               The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $order_adjustment_id, string $field ) {
		// Bail if no log ID was passed.
		if ( empty( $order_adjustment_id ) ) {
			return null;
		}

		// Get the adjustment object
		$order_adjustment = edd_get_order_adjustment( $order_adjustment_id );

		// If log doesn't exist, return null
		if ( ! $order_adjustment ) {
			return null;
		}

		// First, check if it's a property of the adjustment object
		if ( isset( $order_adjustment->$field ) ) {
			return $order_adjustment->$field;
		}

		// If not found in log object, check log meta
		$meta_value = edd_get_order_adjustment_meta( $order_adjustment_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

	/**
	 * Check if a given adjustment is of a specific type.
	 *
	 * @param int    $order_adjustment_id The ID of the adjustment to check.
	 * @param string $type                The expected type of the adjustment. Default empty.
	 *
	 * @return bool True if the adjustment is of the specified type, false otherwise.
	 */
	public static function is_type( int $order_adjustment_id, string $type = '' ): bool {
		$adjustment_type = self::get_field( $order_adjustment_id, 'type' );

		return $adjustment_type && strtolower( $type ) === strtolower( $adjustment_type );
	}

	/**
	 * Query for order adjustment items by type.
	 *
	 * @param int    $type_id The ID of the type.
	 * @param string $type    The type of adjustment (default is 'discount').
	 *
	 * @return object|null Retrieved order adjustment item or null if none found.
	 */
	public static function get_by_type( int $type_id, string $type = 'discount' ): ?object {
		$order_adjustment = current(
			edd_get_order_adjustments( [
				'number'      => 1,
				'type_id'     => $type_id,
				'type'        => $type,
				'object_type' => 'order',
				'order'       => 'DESC'
			] )
		);

		return $order_adjustment !== false ? $order_adjustment : null;
	}

	/**
	 * Get a field value from an order adjustment based on specified criteria.
	 *
	 * @param int    $object_id   The ID of the related object (e.g., order ID).
	 * @param int    $type_id     The ID of the type (e.g., discount ID).
	 * @param string $field       The field to retrieve (default 'total').
	 * @param string $object_type The type of object (default 'order').
	 * @param string $type        The type of adjustment (default 'discount').
	 * @param array  $args        Optional. Additional arguments for the query.
	 *
	 * @return mixed|null The field value or null if not found.
	 */
	public static function get_field_by_object(
		int $object_id,
		int $type_id,
		string $field = 'total',
		string $object_type = 'order',
		string $type = 'discount',
		array $args = []
	) {
		// Default arguments
		$default_args = [
			'number'      => 1,
			'object_id'   => $object_id,
			'object_type' => $object_type,
			'type_id'     => $type_id,
			'type'        => $type,
			'fields'      => $field,
			'order'       => 'DESC'
		];

		// Merge with user provided args, allowing overrides
		$args = wp_parse_args( $args, $default_args );

		// Get the field value
		$value = current( edd_get_order_adjustments( $args ) );

		return $value !== false ? $value : null;
	}

}