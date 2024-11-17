<?php
/**
 * Discount Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * Provides methods for handling order discounts and adjustments.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Adjustments {

	/**
	 * Query for order adjustment items.
	 *
	 * @param int         $order_id The ID of the order.
	 * @param int|null    $type_id  Optional. The ID of the type.
	 * @param string|null $type     Optional. The type of adjustment (default is 'discount').
	 *
	 * @return array Retrieved order adjustment items or empty array if none found.
	 */
	public static function get_adjustments( int $order_id, ?int $type_id = null, ?string $type = 'discount' ): array {
		$args = [
			'number'      => 9999999,
			'object_id'   => $order_id,
			'object_type' => 'order',
			'order'       => 'DESC'
		];

		if ( $type_id !== null ) {
			$args['type_id'] = $type_id;
		}

		if ( $type !== null ) {
			$args['type'] = $type;
		}

		$order_adjustments = edd_get_order_adjustments( $args );

		return ! empty( $order_adjustments ) ? $order_adjustments : [];
	}

	/**
	 * Get total adjustments amount.
	 *
	 * @param int         $order_id     The ID of the order.
	 * @param int|null    $type_id      Optional. The ID of the type.
	 * @param string|null $type         Optional. The type of adjustment (default is 'discount').
	 * @param string      $amount_field The amount field to sum (subtotal, tax, or total).
	 *
	 * @return float Total adjustment amount.
	 */
	public static function get_adjustments_total( int $order_id, ?int $type_id = null, ?string $type = 'discount', string $amount_field = 'total' ): float {
		if ( ! in_array( $amount_field, [ 'subtotal', 'tax', 'total' ], true ) ) {
			$amount_field = 'total';
		}

		$adjustments = self::get_adjustments( $order_id, $type_id, $type );

		return array_reduce( $adjustments, function ( $total, $adjustment ) use ( $amount_field ) {
			return $total + (float) $adjustment->$amount_field;
		}, 0.0 );
	}

	/**
	 * Check if an order has a specific type of adjustment.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $type     The type of adjustment to check for.
	 *
	 * @return bool True if order has adjustment type, false otherwise.
	 */
	public static function has_adjustment_type( int $order_id, string $type ): bool {
		$adjustments = edd_get_order_adjustments( [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'type'        => $type,
			'number'      => 1
		] );

		return ! empty( $adjustments );
	}

	/**
	 * Get an adjustment amount based on specified criteria.
	 *
	 * @param int         $order_id     The ID of the order.
	 * @param string      $order        The sort order ('ASC' or 'DESC').
	 * @param string|null $type         Optional. The type of adjustment.
	 * @param string      $amount_field The amount field to compare (subtotal, tax, or total).
	 *
	 * @return float|null Adjustment amount or null if none found.
	 */
	protected static function get_adjustment_amount( int $order_id, string $order = 'DESC', ?string $type = 'discount', string $amount_field = 'total' ): ?float {
		if ( ! in_array( $amount_field, [ 'subtotal', 'tax', 'total' ], true ) ) {
			$amount_field = 'total';
		}

		$args = [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'number'      => 1,
			'order'       => $order,
			'orderby'     => $amount_field
		];

		if ( $type !== null ) {
			$args['type'] = $type;
		}

		$adjustments = edd_get_order_adjustments( $args );

		return ! empty( $adjustments ) ? (float) $adjustments[0]->$amount_field : null;
	}

	/**
	 * Get the highest adjustment amount for an order.
	 *
	 * @param int         $order_id     The ID of the order.
	 * @param string|null $type         Optional. The type of adjustment.
	 * @param string      $amount_field The amount field to compare (subtotal, tax, or total).
	 *
	 * @return float|null Largest adjustment amount or null if none found.
	 */
	public static function get_highest_adjustment_amount( int $order_id, ?string $type = 'discount', string $amount_field = 'total' ): ?float {
		return self::get_adjustment_amount( $order_id, 'DESC', $type, $amount_field );
	}

	/**
	 * Get the lowest adjustment amount for an order.
	 *
	 * @param int         $order_id     The ID of the order.
	 * @param string|null $type         Optional. The type of adjustment.
	 * @param string      $amount_field The amount field to compare (subtotal, tax, or total).
	 *
	 * @return float|null Smallest adjustment amount or null if none found.
	 */
	public static function get_lowest_adjustment_amount( int $order_id, ?string $type = 'discount', string $amount_field = 'total' ): ?float {
		return self::get_adjustment_amount( $order_id, 'ASC', $type, $amount_field );
	}

}