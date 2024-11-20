<?php
/**
 * Review Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides review-related operations for EDD customers.
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

trait Reviews {
	use Core;

	/**
	 * Retrieves all reviews for a given customer.
	 *
	 * @param int   $customer_id The customer ID.
	 * @param array $args        Optional. Additional arguments to customize the `get_comments` query.
	 *
	 * @return array|null An array of reviews or null if the customer ID is invalid.
	 */
	public static function get_reviews( int $customer_id, array $args = [] ): ?array {
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
			'type'    => 'edd_review',
		];

		$query_args = wp_parse_args( $args, $default_args );

		// Retrieve the reviews
		return get_comments( $query_args );
	}

	/**
	 * Get the customer review count.
	 *
	 * @param int $customer_id The customer ID to lookup.
	 *
	 * @return int|null The total reviews or null if the customer ID is invalid.
	 */
	public static function get_review_count( int $customer_id ): ?int {
		// Bail if no customer ID was passed.
		if ( empty( $customer_id ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );

		// Bail if no user was found.
		if ( empty( $user ) ) {
			return null;
		}

		// Count the reviews
		$review_count = get_comments( [
			'user_id' => $user->ID,
			'count'   => true,
			'status'  => 'approve',
			'type'    => 'edd_review'
		] );

		return absint( $review_count );
	}

	/**
	 * Get the average rating for a customer's reviews.
	 *
	 * @param int $customer_id The customer ID to lookup.
	 *
	 * @return float|null The average rating or null if invalid customer.
	 */
	public static function get_average_rating( int $customer_id ): ?float {
		if ( empty( $customer_id ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );
		if ( empty( $user ) ) {
			return null;
		}

		$reviews = get_comments( [
			'user_id' => $user->ID,
			'status'  => 'approve',
			'type'    => 'edd_review'
		] );

		if ( empty( $reviews ) ) {
			return 0.0;
		}

		$total = 0;
		foreach ( $reviews as $review ) {
			$rating = get_comment_meta( $review->comment_ID, 'rating', true );
			$total  += (float) $rating;
		}

		return round( $total / count( $reviews ), 2 );
	}

	/**
	 * Get all review ratings indexed by post ID.
	 *
	 * @param int $customer_id The customer ID to lookup.
	 *
	 * @return array|null Array of post_id => rating or null if invalid customer.
	 */
	public static function get_review_ratings_by_post( int $customer_id ): ?array {
		if ( empty( $customer_id ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );
		if ( empty( $user ) ) {
			return null;
		}

		$reviews = get_comments( [
			'user_id' => $user->ID,
			'status'  => 'approve',
			'type'    => 'edd_review'
		] );

		if ( empty( $reviews ) ) {
			return [];
		}

		$ratings = [];
		foreach ( $reviews as $review ) {
			$rating                              = get_comment_meta( $review->comment_ID, 'rating', true );
			$ratings[ $review->comment_post_ID ] = (float) $rating;
		}

		return $ratings;
	}

}