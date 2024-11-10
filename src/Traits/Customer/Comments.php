<?php
/**
 * Comment Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides comment operations for EDD customers.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use WP_User;

trait Comments {

	/**
	 * Required trait method for getting user.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return WP_User|null
	 */
	abstract protected static function get_user( int $customer_id ): ?WP_User;

	/**
	 * Retrieves all comments for a given customer.
	 *
	 * @param int   $customer_id The customer ID.
	 * @param array $args        Optional. Additional arguments to customize the `get_comments` query.
	 *
	 * @return array|null An array of comments or null if the customer ID is invalid.
	 */
	public static function get_comments( int $customer_id, array $args = [] ): ?array {
		// Bail if no customer ID was passed.
		if ( empty( $customer_id ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );

		// Bail if no user was found.
		if ( empty( $user ) ) {
			return null;
		}

		$default_args = [
			'user_id' => $user->ID,
			'status'  => 'approve',
			'type'    => '',
		];

		$query_args = wp_parse_args( $args, $default_args );

		// Retrieve the comments
		return get_comments( $query_args );
	}

	/**
	 * Get the customer comment count.
	 *
	 * @param int $customer_id The customer ID to lookup.
	 *
	 * @return int|null The total comments or null if the customer ID is invalid.
	 */
	public static function get_comment_count( int $customer_id ): ?int {
		// Bail if no customer ID was passed.
		if ( empty( $customer_id ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );

		// Bail if no user was found.
		if ( empty( $user ) ) {
			return null;
		}

		// Count the comments
		$comment_count = get_comments( [
			'user_id' => $user->ID,
			'count'   => true,
			'status'  => 'approve',
			'type'    => ''
		] );

		return absint( $comment_count );
	}

}