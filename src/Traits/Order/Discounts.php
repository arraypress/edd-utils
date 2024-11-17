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

trait Discounts {

	/**
	 * Get all discounts applied to an order.
	 *
	 * @param int   $order_id The order ID to check.
	 * @param array $args     Optional. Additional arguments for the query.
	 *
	 * @return array|null Array of discount adjustments or null if none found.
	 */
	public static function get_discounts( int $order_id, array $args = [] ): ?array {
		if ( empty( $order_id ) ) {
			return null;
		}

		$default_args = [
			'number'      => 999999999,
			'object_id'   => $order_id,
			'object_type' => 'order',
			'type'        => 'discount',
			'order'       => 'DESC'
		];

		// Merge with user provided args, allowing overrides
		$args = wp_parse_args( $args, $default_args );

		// Get the discounts
		$discounts = edd_get_order_adjustments( $args );

		return ! empty( $discounts ) ? $discounts : null;
	}

	/**
	 * Count discounts applied to an order.
	 *
	 * @param int   $order_id The order ID to check.
	 * @param array $args     Optional. Additional arguments for the query.
	 *
	 * @return int Number of discounts.
	 */
	public static function count_discounts( int $order_id, array $args = [] ): int {
		if ( empty( $order_id ) ) {
			return 0;
		}

		$default_args = [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'type'        => 'discount'
		];

		// Merge with user provided args, allowing overrides
		$args = wp_parse_args( $args, $default_args );

		return edd_count_order_adjustments( $args );
	}

	/**
	 * Get total discount amount for an order.
	 *
	 * @param int    $order_id     The order ID to check.
	 * @param string $amount_field The amount field to calculate against. One of 'subtotal', 'tax', or 'total'.
	 * @param bool   $formatted    Whether to format the amount.
	 *
	 * @return float|string The total discount amount.
	 */
	public static function get_total_discount_amount( int $order_id, string $amount_field = 'total', bool $formatted = false ) {
		$default_amount = 0.00;

		// Early return if no order ID or no discounts
		if ( empty( $order_id ) || empty( $discounts = self::get_discounts( $order_id ) ) ) {
			return $formatted
				? edd_currency_filter( edd_format_amount( $default_amount ) )
				: $default_amount;
		}

		// Validate amount field
		if ( ! in_array( $amount_field, [ 'subtotal', 'tax', 'total' ], true ) ) {
			$amount_field = 'total';
		}

		// Calculate total
		$total = array_reduce(
			$discounts,
			fn( $carry, $discount ) => $carry + (float) ( $discount->{$amount_field} ?? 0 ),
			$default_amount
		);

		// Return formatted or raw amount
		if ( ! $formatted ) {
			return $total;
		}

		$order    = edd_get_order( $order_id );
		$currency = $order ? $order->currency : edd_get_currency();

		return edd_currency_filter( edd_format_amount( $total ), $currency );
	}

	/**
	 * Get all discount codes used in an order.
	 *
	 * @param int $order_id The order ID to check.
	 *
	 * @return array Array of discount codes.
	 */
	public static function get_discount_codes( int $order_id ): array {
		if ( empty( $order_id ) ) {
			return [];
		}

		$discounts = self::get_discounts( $order_id );
		if ( empty( $discounts ) ) {
			return [];
		}

		return array_map( function ( $discount ) {
			return $discount->description;
		}, $discounts );
	}

	/**
	 * Get all discount IDs used in an order.
	 *
	 * @param int $order_id The order ID to check.
	 *
	 * @return array Array of discount IDs.
	 */
	public static function get_discount_ids( int $order_id ): array {
		if ( empty( $order_id ) ) {
			return [];
		}

		$discounts = self::get_discounts( $order_id );
		if ( empty( $discounts ) ) {
			return [];
		}

		return array_map( function ( $discount ) {
			return $discount->type_id;
		}, $discounts );
	}

	/**
	 * Check if a specific discount code was used in an order.
	 *
	 * @param int    $order_id      The order ID to check.
	 * @param string $discount_code The discount code to check for.
	 *
	 * @return bool Whether the discount code was used.
	 */
	public static function has_discount_code( int $order_id, string $discount_code ): bool {
		if ( empty( $order_id ) || empty( $discount_code ) ) {
			return false;
		}

		$discount_codes = self::get_discount_codes( $order_id );

		return in_array( $discount_code, $discount_codes, true );
	}

}