<?php
/**
 * Customer Email Address Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Customers;

use ArrayPress\Utils\Database\Exists;

class CustomerEmailAddress {

	/**
	 * Check if the customer email address exists in the database.
	 *
	 * @param int $customer_email_address_id The ID of the customer email address to check.
	 *
	 * @return bool True if the customer email address exists, false otherwise.
	 */
	public static function exists( int $customer_email_address_id ): bool {
		return Exists::row( 'edd_customer_addresses', 'id', $customer_email_address_id );
	}

	/**
	 * Get a field from a customer email address object.
	 *
	 * @param int    $customer_email_address_id Customer Email Address ID. Default `0`.
	 * @param string $field                     Field to retrieve from object. Default empty.
	 *
	 * @return mixed Null if customer email address does not exist. Value of Customer Email Address if exists.
	 */
	public static function get_field( int $customer_email_address_id = 0, string $field = '' ) {
		$customer_email_address = edd_get_customer_email_address( $customer_email_address_id );

		// Check that field exists.
		return $customer_email_address->{$field} ?? null;
	}

}