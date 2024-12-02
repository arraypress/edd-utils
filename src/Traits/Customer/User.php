<?php
/**
 * User Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides user-related operations for EDD customers.
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
use WP_User;

trait User {

	/**
	 * Get the user object associated with a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return WP_User|null A WP_User object on success, or null on failure.
	 */
	public static function get_user( int $customer_id ): ?WP_User {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$user = get_userdata( $customer->user_id );

		if ( ! $user || ! $user->exists() ) {
			return null;
		}

		return $user;
	}

	/**
	 * Check if a customer's user has a specific role.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $role        The role to check for.
	 *
	 * @return bool|null True if the user has the role, false if they don't, null if customer/user invalid.
	 */
	public static function has_role( int $customer_id, string $role ): ?bool {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$user = get_userdata( $customer->user_id );

		if ( ! $user || ! $user->exists() ) {
			return null;
		}

		return in_array( trim( $role ), $user->roles, true );
	}

	/**
	 * Get all roles for a customer's user.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return array|null Array of role strings, or null if customer/user invalid.
	 */
	public static function get_roles( int $customer_id ): ?array {
		$user = self::get_user( $customer_id );

		return $user ? $user->roles : null;
	}

	/**
	 * Check if the customer has a WordPress user account.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return bool Whether the customer has a user account.
	 */
	public static function has_user( int $customer_id ): bool {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return false;
		}

		return ! empty( $customer->user_id );
	}

}