<?php
/**
 * Core Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides core customer-related operations and field access methods.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\Utils\Database\Exists;
use ArrayPress\Utils\Users\User as CoreUser;
use ArrayPress\Utils\Common\Cache;
use WP_User;
use EDD_Customer;

trait Core {

	/**
	 * Check if the customer exists in the database.
	 *
	 * @param int $customer_id The ID of the customer to check.
	 *
	 * @return bool True if the customer exists, false otherwise.
	 */
	public static function exists( int $customer_id ): bool {
		return Exists::row( 'edd_customers', 'id', $customer_id );
	}

	/**
	 * Get a customer by ID.
	 *
	 * @param int $customer_id Customer ID.
	 *
	 * @return EDD_Customer|null Customer object if successful, null otherwise.
	 * @since 1.0.0
	 *
	 */
	public static function get( int $customer_id ): ?EDD_Customer {
		// Validate input
		if ( empty( $customer_id ) ) {
			return null;
		}

		// Get the customer using EDD's function
		$customer = edd_get_customer( $customer_id );

		// Return null if not a valid customer object
		return ( $customer instanceof EDD_Customer ) ? $customer : null;
	}

	/**
	 * Get a customer by email address.
	 *
	 * @param string $email The email address to search for.
	 *
	 * @return EDD_Customer|null Customer object if successful, null otherwise.
	 */
	public static function get_by_email( string $email ): ?EDD_Customer {
		// Validate email
		if ( empty( $email ) || ! is_email( $email ) ) {
			return null;
		}

		// Get customer using EDD's function
		$customer = edd_get_customer_by( 'email', trim( $email ) );

		// Return null instead of false for consistency
		return ( $customer instanceof EDD_Customer ) ? $customer : null;
	}

	/**
	 * Retrieves the customer object by user ID, email, or falls back to the current user ID.
	 *
	 * This function attempts to retrieve a customer object from the Easy Digital Downloads database
	 * using the provided user identifier, which can be either a numeric user ID or an email address.
	 * If no identifier is provided, the function will default to using the current user's ID.
	 * It first checks if the identifier is a number and assumes it's a user ID, otherwise, it checks
	 * if it's a valid email. If the identifier is invalid or if no user is found, the function returns false.
	 *
	 * @param int|string|null $user_identifier Optional. The user ID or email address used to retrieve the customer.
	 *                                         Defaults to null, which triggers fallback to the current user ID.
	 *
	 * @return mixed False if no valid customer is found, the customer object on success.
	 * @since 1.0
	 *
	 */
	public static function get_by_identifier( $user_identifier = null ) {

		// Fallback to the current user ID if no identifier is provided.
		if ( empty( $user_identifier ) ) {
			$user_identifier = get_current_user_id();
		}

		// Return false if no valid user identifier is present.
		if ( empty( $user_identifier ) ) {
			return false;
		}

		if ( is_numeric( $user_identifier ) ) {
			$customer = edd_get_customer_by( 'user_id', $user_identifier );
		} elseif ( is_email( $user_identifier ) ) {
			$customer = edd_get_customer_by( 'email', $user_identifier );
		} else {
			return false;
		}

		return $customer;
	}

	/**
	 * Get the customer by user ID.
	 *
	 * @param int $user_id The WordPress user ID of the customer.
	 *
	 * @return EDD_Customer|null Customer object if successful, null otherwise.
	 */
	public static function get_by_user_id( int $user_id = 0 ): ?EDD_Customer {
		$user_id = CoreUser::get_validate_user_id( $user_id );
		if ( $user_id === null ) {
			return null;
		}

		$customer = edd_get_customer_by( 'user_id', $user_id );

		return $customer !== false ? $customer : null;
	}

	/**
	 * Get the customer ID associated with a user ID.
	 *
	 * @param int  $user_id   The user ID.
	 * @param bool $use_cache Whether to use cache for the lookup. Default true.
	 *
	 * @return int|null The customer ID or null if the customer ID is not found.
	 */
	public static function get_id_by_user_id( int $user_id = 0, bool $use_cache = true ): ?int {
		$user_id = CoreUser::get_validate_user_id( $user_id );
		if ( $user_id === null ) {
			return null;
		}

		// Define the query function
		$query_func = function () use ( $user_id ) {
			global $wpdb;

			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$wpdb->prefix}{$wpdb->edd_customers} WHERE user_id = %d LIMIT 1;",
					$user_id
				)
			);
		};

		// Generate a cache key for the query result.
		$cache_key = Cache::generate_key( 'edd_customer_id_by_user_id', $user_id );

		// Use the Cache::remember method if caching is enabled, otherwise execute the query directly
		$customer_id = $use_cache
			? Cache::remember( $cache_key, $query_func, HOUR_IN_SECONDS )
			: $query_func();

		// Return the customer ID or null if the customer ID is not found.
		return $customer_id ? intval( $customer_id ) : null;
	}

	/**
	 * Retrieves the user object associated with a given customer ID.
	 *
	 * This function looks up the user linked to a specific customer by their ID.
	 * It performs checks to ensure that a valid customer ID is provided and that
	 * the corresponding user actually exists in the database. If the customer does
	 * not exist or the user data is not found, the function will return null.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return WP_User|null A WP_User object on success, or null on failure.
	 */
	public static function get_user( int $customer_id ): ?WP_User {
		$customer = self::get_validated( $customer_id );
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
	 * Checks if a user has a role.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $role        The role.
	 *
	 * @return bool|null True if the user has the role, false if they do not, null if the customer does not exist or is
	 *                   invalid.
	 */
	public static function has_user_role( int $customer_id, string $role ): ?bool {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$user = get_userdata( $customer->user_id );

		if ( ! $user || ! $user->exists() ) {
			return null;
		}

		return in_array( trim( $role ), $user->roles, true );
	}

	/** Helper Methods ******************************************************/

	/**
	 * Get and validate a customer object.
	 *
	 * @param int $customer_id Customer ID
	 *
	 * @return EDD_Customer|null Customer object or null if invalid
	 */
	protected static function get_validated( int $customer_id = 0 ): ?EDD_Customer {
		// Bail if no customer ID was passed
		if ( empty( $customer_id ) ) {
			return null;
		}

		$customer = edd_get_customer( $customer_id );

		// Return null if not a valid EDD_Customer object
		return ( $customer instanceof EDD_Customer ) ? $customer : null;
	}

}