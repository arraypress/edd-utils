<?php
/**
 * Discount Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides discount operations for EDD customers.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\EDD\Customers\Customer;

trait Discounts {

	/**
	 * Get all discount IDs used by a customer across their order history.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return array|null Array of discount IDs, or null if customer ID is invalid.
	 */
	public static function get_discount_ids( int $customer_id, array $query_args = [] ): ?array {
		return self::get_discounts( $customer_id, false, $query_args );
	}

	/**
	 * Retrieve all discount IDs used by a customer across their order history.
	 *
	 * @param int   $customer_id    The ID of the customer.
	 * @param bool  $return_objects Whether to return discount objects instead of just IDs.
	 * @param array $query_args     Optional. Additional arguments to customize the orders query.
	 *
	 * @return array|null Array of discount IDs or objects, or null if customer ID is invalid.
	 */
	public static function get_discounts( int $customer_id, bool $return_objects = false, array $query_args = [] ): ?array {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$query_args = wp_parse_args( $query_args, [
			'customer_id' => $customer->id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'fields'      => 'id',
			'number'      => 999999999
		] );

		// Get all order IDs for the customer
		$order_ids = edd_get_orders( $query_args );

		if ( empty( $order_ids ) ) {
			return [];
		}

		// Get all discount adjustments for these orders
		$discounts = edd_get_order_adjustments( [
			'object_id__in' => $order_ids,
			'object_type'   => 'order',
			'type'          => 'discount',
			'fields'        => 'type_id',
			'number'        => 999999999
		] );

		if ( empty( $discounts ) ) {
			return [];
		}

		// Remove duplicates
		$discount_ids = array_unique( array_filter( $discounts ) );

		if ( $return_objects ) {
			return array_map( 'edd_get_discount', $discount_ids );
		}

		return $discount_ids;
	}

	/**
	 * Get the total amount saved through discounts for a customer.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param bool  $formatted   Whether to return a formatted string or raw value.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return float|string|null Total savings amount, formatted string if requested, or null if customer ID is invalid.
	 */
	public static function get_discount_total_savings( int $customer_id, bool $formatted = false, array $query_args = [] ) {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$query_args = wp_parse_args( $query_args, [
			'customer_id' => $customer->id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'fields'      => 'id',
			'number'      => 999999999
		] );

		// Get all order IDs for the customer
		$order_ids = edd_get_orders( $query_args );

		if ( empty( $order_ids ) ) {
			return 0.00;
		}

		// Get all discount adjustments for these orders
		$adjustments = edd_get_order_adjustments( [
			'object_id__in' => $order_ids,
			'object_type'   => 'order',
			'type'          => 'discount',
			'number'        => 999999999
		] );

		if ( empty( $adjustments ) ) {
			return 0.00;
		}

		// Calculate total savings accounting for rate
		$total_savings = 0.00;
		foreach ( $adjustments as $adjustment ) {
			$total_savings += abs( $adjustment->total * $adjustment->rate );
		}

		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $total_savings ) );
		}

		return $total_savings;
	}

}