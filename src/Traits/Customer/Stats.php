<?php
/**
 * Order Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides order-related operations for EDD customers, handling purchase counts,
 * values, and order velocity calculations.
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

trait Stats {

	/**
	 * Retrieve the number of orders made by a customer.
	 *
	 * @param int  $customer_id The ID of the customer.
	 * @param bool $formatted   Whether to return a formatted string or raw count.
	 *
	 * @return int|string|null The number of orders as an integer or formatted string, or null on failure.
	 */
	public static function get_purchase_count( int $customer_id, bool $formatted = false ) {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$count = $customer->purchase_count;

		if ( ! $formatted ) {
			return $count;
		} else {
			return number_format_i18n( $count );
		}
	}

	/**
	 * Retrieve the total amount spent by a customer.
	 *
	 * @param int  $customer_id The ID of the customer.
	 * @param bool $formatted   Whether to return a formatted string or raw value.
	 *
	 * @return float|string|null The total amount spent as a float or formatted string, or null on failure.
	 */
	public static function get_purchase_value( int $customer_id, bool $formatted = false ) {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$total_spent = $customer->purchase_value;

		if ( ! $formatted ) {
			return $total_spent;
		} else {
			return edd_currency_filter( edd_format_amount( $total_spent ) );
		}
	}

}