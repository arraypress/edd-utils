<?php
/**
 * Status Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides status-related operations for customer records.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use EDD_Customer;

trait Status {

	/**
	 * Get and validate a customer object.
	 *
	 * @param int $customer_id Customer ID
	 *
	 * @return EDD_Customer|null Customer object or null if invalid
	 */
	abstract protected static function get_validated( int $customer_id = 0 ): ?EDD_Customer;

	/**
	 * Get a specific field from the customer.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $field       The field name.
	 *
	 * @return mixed The field value or null if not found.
	 */
	abstract protected static function get_field( int $customer_id, string $field );

	/**
	 * Valid customer statuses.
	 *
	 * @return array
	 */
	public static function get_valid_statuses(): array {
		return array(
			'active',
			'inactive',
			'pending',
			'blocked',
			'suspended'
		);
	}

	/**
	 * Update a customer's status.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $status      The new status to set for the customer. Default 'active'.
	 *
	 * @return bool True if the status was updated successfully, false otherwise.
	 */
	public static function set_status( int $customer_id = 0, string $status = 'active' ): bool {
		// Validate customer
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return false;
		}

		// Sanitize and validate status
		$status = trim( strtolower( $status ) );
		if ( ! in_array( $status, self::get_valid_statuses(), true ) ) {
			return false;
		}

		// Prepare the data for update
		$data = array(
			'status' => $status
		);

		// Update the customer
		$updated = edd_update_customer( $customer_id, $data );

		return $updated !== false;
	}

	/**
	 * Get the status of a specified customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return string|null The status of the customer as a string, or null if no status is found.
	 */
	public static function get_status( int $customer_id = 0 ): ?string {
		return self::get_field( $customer_id, 'status' );
	}

	/**
	 * Check if a customer status is valid.
	 *
	 * @param string $status The status to check.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_status( string $status ): bool {
		return in_array( strtolower( trim( $status ) ), self::get_valid_statuses(), true );
	}

	/**
	 * Check if a customer is active.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public static function is_active( int $customer_id ): bool {
		return self::get_status( $customer_id ) === 'active';
	}

	/**
	 * Check if a customer is inactive.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if inactive, false otherwise.
	 */
	public static function is_inactive( int $customer_id ): bool {
		return self::get_status( $customer_id ) === 'inactive';
	}

	/**
	 * Set a customer as active.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if the status was updated successfully, false otherwise.
	 */
	public static function activate( int $customer_id ): bool {
		return self::set_status( $customer_id, 'active' );
	}

	/**
	 * Set a customer as inactive.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if the status was updated successfully, false otherwise.
	 */
	public static function deactivate( int $customer_id ): bool {
		return self::set_status( $customer_id, 'inactive' );
	}

}