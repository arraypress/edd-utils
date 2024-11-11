<?php
/**
 * Product Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides product-related operations for EDD customers, handling purchased products,
 * bundles, and related term operations.
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
use WP_User;

trait Recurring {
	use Core;

	/**
	 * Checks if the given customer has an active subscription.
	 *
	 * This function determines whether a customer has any active subscriptions.
	 * If the EDD Recurring add-on is not active, or if the customer does not exist,
	 * the function will return false.
	 *
	 * @param int $customer_id The ID of the customer to check for active subscriptions.
	 *
	 * @return bool True if the customer has an active subscription, false otherwise.
	 */
	public static function has_active_subscription( int $customer_id = 0 ): bool {
		if ( ! function_exists( 'EDD_Recurring' ) ) {
			return false;
		}

		$user = self::get_user( $customer_id );

		if ( empty( $user ) ) {
			return false;
		}

		$subscriber = new \EDD_Recurring_Subscriber( $user->ID, true );

		return (bool) $subscriber->has_active_subscription();
	}

	/**
	 * Get all subscriptions for a customer.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $status      Optional. Filter by subscription status. Default 'all'.
	 *                            Accepts 'active', 'expired', 'cancelled', 'pending', 'failing', 'completed' or 'all'.
	 *
	 * @return array|null Array of subscription objects or null if no subscriptions found.
	 */
	public static function get_subscriptions( int $customer_id = 0, string $status = 'all' ): ?array {
		if ( ! function_exists( 'EDD_Recurring' ) ) {
			return null;
		}

		$user = self::get_user( $customer_id );
		if ( empty( $user ) ) {
			return null;
		}

		$subscriber = new \EDD_Recurring_Subscriber( $user->ID, true );
		$args       = [];

		if ( 'all' !== $status ) {
			$args['status'] = $status;
		}

		$subscriptions = $subscriber->get_subscriptions( $args );

		return ! empty( $subscriptions ) ? $subscriptions : null;
	}

	/**
	 * Get the count of customer subscriptions.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $status      Optional. Filter by subscription status. Default 'all'.
	 *
	 * @return int The number of subscriptions.
	 */
	public static function get_subscription_count( int $customer_id = 0, string $status = 'all' ): int {
		$subscriptions = self::get_subscriptions( $customer_id, $status );

		return $subscriptions ? count( $subscriptions ) : 0;
	}

	/**
	 * Get customer's subscription by product ID.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param int    $product_id  The ID of the product.
	 * @param string $status      Optional. Filter by subscription status. Default 'active'.
	 *
	 * @return object|null Subscription object if found, null otherwise.
	 */
	public static function get_subscription_by_product( int $customer_id, int $product_id, string $status = 'active' ): ?object {
		$subscriptions = self::get_subscriptions( $customer_id, $status );

		if ( ! $subscriptions ) {
			return null;
		}

		foreach ( $subscriptions as $subscription ) {
			if ( (int) $subscription->product_id === $product_id ) {
				return $subscription;
			}
		}

		return null;
	}

	/**
	 * Get the total spent on subscriptions by a customer.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $status      Optional. Filter by subscription status. Default 'all'.
	 * @param bool   $formatted   Optional. Whether to return a formatted amount. Default false.
	 *
	 * @return string|float|null Formatted string if $formatted is true, float if false, null if no subscriptions.
	 */
	public static function get_subscription_total( int $customer_id, string $status = 'all', bool $formatted = false ) {
		$subscriptions = self::get_subscriptions( $customer_id, $status );

		if ( ! $subscriptions ) {
			return null;
		}

		$total = 0.00;
		foreach ( $subscriptions as $subscription ) {
			$total += (float) $subscription->initial_amount;
			$total += (float) $subscription->recurring_amount * $subscription->get_total_payments();
		}

		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $total ) );
		}

		return $total;
	}

	/**
	 * Get customer's next renewal date.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $format      Optional. Date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|null Formatted date of next renewal or null if no active subscriptions.
	 */
	public static function get_next_renewal_date( int $customer_id, string $format = 'Y-m-d H:i:s' ): ?string {
		$subscriptions = self::get_subscriptions( $customer_id, 'active' );

		if ( ! $subscriptions ) {
			return null;
		}

		$next_renewal = null;
		foreach ( $subscriptions as $subscription ) {
			$expiration = $subscription->get_expiration();
			if ( ! $next_renewal || $expiration < $next_renewal ) {
				$next_renewal = $expiration;
			}
		}

		return $next_renewal ? date( $format, $next_renewal ) : null;
	}

	/**
	 * Check if customer has a subscription for a specific product.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param int    $product_id  The ID of the product.
	 * @param string $status      Optional. Subscription status to check. Default 'active'.
	 *
	 * @return bool True if customer has subscription for product, false otherwise.
	 */
	public static function has_product_subscription( int $customer_id, int $product_id, string $status = 'active' ): bool {
		return self::get_subscription_by_product( $customer_id, $product_id, $status ) !== null;
	}

}