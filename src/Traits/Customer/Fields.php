<?php
/**
 * Base Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides field access methods for customer data.
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
use ArrayPress\Utils\Common\Split;

trait Fields {

	/**
	 * Get a specific field from the customer.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $field       The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	public static function get_field( int $customer_id, string $field ) {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		// First, check if it's a property of the customer object
		if ( isset( $customer->$field ) ) {
			return $customer->$field;
		}

		// If not found in customer object, check customer meta
		$meta_value = edd_get_customer_meta( $customer_id, $field, true );
		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		// If still not found, return null
		return null;
	}

	/**
	 * Get the WordPress user ID associated with a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return int|null The user ID associated with the customer, or null if none found.
	 */
	public static function get_user_id( int $customer_id = 0 ): ?int {
		$user_id = self::get_field( $customer_id, 'user_id' );

		return $user_id !== null ? (int) $user_id : null;
	}

	/**
	 * Get the primary email address of a specified customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return string|null The primary email address of the customer, or null if none found.
	 */
	public static function get_email( int $customer_id = 0 ): ?string {
		return self::get_field( $customer_id, 'email' );
	}

	/**
	 * Retrieve all email addresses associated with a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return array An array of email addresses associated with the customer.
	 */
	public static function get_emails( int $customer_id ): array {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return [];
		}

		return $customer->get_emails();
	}

	/**
	 * Get the name of a specified customer.
	 *
	 * @param int  $customer_id The ID of the customer.
	 * @param bool $split       Whether to split the name into first and last components.
	 *
	 * @return array|string|null The customer name as a string, split name array, or null if none found.
	 */
	public static function get_name( int $customer_id, bool $split = false ) {
		$name = self::get_field( $customer_id, 'name' );

		if ( $split && $name ) {
			return Split::full_name( $name );
		}

		return $name;
	}

	/**
	 * Retrieve the creation date of the specified customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return string|null The creation date of the customer in a string format, or null if no date is found.
	 */
	public static function get_date_created( int $customer_id = 0 ): ?string {
		return self::get_field( $customer_id, 'date_created' );
	}

}