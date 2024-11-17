<?php
/**
 * Core Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * Provides core functionality for order operations.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Fields {

	/**
	 * Get a specific field from the order.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $field    The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $order_id, string $field ) {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		// First, check if it's a property of the order object
		if ( isset( $order->$field ) ) {
			return $order->$field;
		}

		// If not found in order object, check order meta
		$meta_value = edd_get_order_meta( $order_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		return null;
	}

	/**
	 * Retrieve the payment key for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The payment key or null if not found.
	 */
	public static function get_payment_key( int $order_id ): ?string {
		$payment_key = self::get_field( $order_id, 'payment_key' );

		return ! empty( $payment_key ) ? $payment_key : null;
	}

	/**
	 * Retrieves the username associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The username or null if not found.
	 */
	public static function get_username( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		return edd_email_tag_username( $order_id ) ?: null;
	}

	/**
	 * Retrieve the first name of the customer associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The first name or null if not found.
	 */
	public static function get_first_name( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		return edd_email_tag_first_name( $order_id ) ?: null;
	}

	/**
	 * Retrieve the full name of the customer associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The full name or null if not found.
	 */
	public static function get_full_name( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		return edd_email_tag_fullname( $order_id ) ?: null;
	}

	/**
	 * Retrieve the email address of the customer associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The email address or null if not found.
	 */
	public static function get_email( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		return edd_email_tag_user_email( $order_id ) ?: null;
	}

	/**
	 * Get a specific amount field from an order with optional formatting.
	 *
	 * @param int    $order_id  The ID of the order.
	 * @param string $field     The field to retrieve (subtotal, discount, tax, total, rate).
	 * @param bool   $formatted Whether to format the amount according to currency settings.
	 *
	 * @return string|float|null The amount, or null if not found.
	 */
	public static function get_amount_field( int $order_id, string $field, bool $formatted = false ) {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		// Get the value using dynamic property access
		$amount = $order->$field;
		if ( null === $amount ) {
			return null;
		}

		// Return formatted or raw amount
		if ( $formatted ) {
			return edd_currency_filter(
				edd_format_amount( $amount ),
				$order->currency
			);
		}

		return (float) $amount;
	}

}