<?php
/**
 * Checkout Utility Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Utils
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Checkout;

use EDD_Customer;

class Checkout {

	/**
	 * Get the customer during checkout, attempting multiple lookup methods.
	 *
	 * @param string|null $email Email address to check if user lookup fails.
	 *
	 * @return EDD_Customer|null Customer object if found, null otherwise.
	 */
	public static function get_customer( ?string $email = null ): ?EDD_Customer {
		// First try to get customer by user ID if logged in
		if ( is_user_logged_in() ) {
			$customer = edd_get_customer_by( 'user_id', get_current_user_id() );
			if ( $customer instanceof EDD_Customer ) {
				return $customer;
			}
		}

		// Try to find customer by email if provided
		if ( ! empty( $email ) && is_email( $email ) ) {
			$customer = edd_get_customer_by( 'email', trim( $email ) );
			if ( $customer instanceof EDD_Customer ) {
				return $customer;
			}
		}

		return null;
	}

	/**
	 * Get the customer ID during checkout, attempting multiple lookup methods.
	 *
	 * @param string|null $email Email address to check if user lookup fails.
	 *
	 * @return int|null Customer ID if found, null otherwise.
	 */
	public static function get_customer_id( ?string $email = null ): ?int {
		$customer = self::get_customer( $email );

		return $customer instanceof EDD_Customer ? $customer->id : null;
	}

}