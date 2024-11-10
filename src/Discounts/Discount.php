<?php
/**
 * Log Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Discounts;

use ArrayPress\Utils\Elements\Element;
use EDD\Stats;

class Discount {

	/**
	 * Create a new discount.
	 *
	 * @param float $amount The discount amount.
	 * @param array $args   Additional arguments for the discount.
	 *
	 * @return int|false The ID of the created discount, or false on failure.
	 */
	public static function create( float $amount, array $args = [] ) {
		$code = self::generate_unique_code();

		$default_args = [
			'name'                => $code,
			'code'                => $code,
			'status'              => 'active',
			'type'                => 'percent',
			'amount'              => $amount,
			'uses'                => 0,
			'max'                 => 0, // 0 for unlimited
			'start_date'          => '', // Current date will be used if empty
			'end_date'            => '',
			'min_price'           => 0,
			'product_requirement' => 'any', // 'any' or 'all'
			'product_ids'         => [], // Array of product IDs
			'excluded_products'   => [], // Array of excluded product IDs
			'is_not_global'       => false,
			'is_single_use'       => false,
			'duration'            => 0, // In days, 0 for no duration
			'categories'          => [],
			'term_condition'      => null
		];

		// Merge custom args with defaults
		$discount_args = wp_parse_args( $args, $default_args );

		// Set the amount
		$discount_args['amount'] = $amount;

		// Handle start date
		if ( empty( $discount_args['start_date'] ) ) {
			$discount_args['start_date'] = edd_get_utc_date_string( current_time( 'mysql' ) );
		}

		// Handle end date based on duration
		if ( $discount_args['duration'] > 0 ) {
			$end_date                  = strtotime( $discount_args['start_date'] . ' + ' . $discount_args['duration'] . ' days' );
			$discount_args['end_date'] = edd_get_utc_date_string( date( 'Y-m-d H:i:s', $end_date ) );
		}

		// Remove duration from args as it's not a valid EDD discount parameter
		unset( $discount_args['duration'] );

		// Create the discount
		$discount_id = edd_add_discount( $discount_args );

		return $discount_id ?: false;
	}

	/**
	 * Get the savings amount associated with a discount.
	 *
	 * @param int  $discount_id The ID of the discount to retrieve savings for.
	 * @param bool $formatted   Whether to format the savings amount as currency (default: true).
	 *
	 * @return string|float|false The savings amount as a formatted currency string or float,
	 *                           or the raw savings amount if $formatted is false.
	 *                           Returns false if the discount is not found.
	 */
	function get_savings( int $discount_id = 0, bool $formatted = true ) {

		// Bail if no discount ID
		if ( empty( $discount_id ) ) {
			return false;
		}

		// Attempt to retrieve the discount.
		$discount = edd_get_discount( $discount_id );

		// Ensure the discount exists.
		if ( empty( $discount ) ) {
			return false;
		}

		$stats = new Stats();
		$total = $stats->get_discount_savings( array(
			'discount_code' => $discount->code
		) );

		return $formatted ? edd_currency_filter( edd_format_amount( $total ) ) : $total;
	}

	/**
	 * Get the total earnings for a specific discount.
	 *
	 * @param int  $discount_id The ID of the discount to get earnings for.
	 * @param bool $formatted   Whether to format the earnings amount as currency (default: true).
	 *
	 * @return string|float|false The earnings amount as a formatted currency string or float,
	 *                           or false if the discount is not found.
	 */
	public static function get_earnings( int $discount_id = 0, bool $formatted = true ) {
		// Bail if no discount ID
		if ( empty( $discount_id ) ) {
			return false;
		}

		// Attempt to retrieve the discount
		$discount = edd_get_discount( $discount_id );

		// Ensure the discount exists
		if ( empty( $discount ) ) {
			return false;
		}

		// Get the completed order statuses
		$completed_statuses = edd_get_complete_order_statuses();

		// Query orders that used this discount
		$args = array(
			'discount_id' => $discount_id,
			'status__in'  => $completed_statuses,
			'fields'      => 'total', // We only need the total field
			'number'      => 9999999,      // Get all orders
		);

		$orders = edd_get_orders( $args );

		// Sum up the totals
		$total = 0;
		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				$total += $order->total;
			}
		}

		// Format if requested
		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $total ) );
		}

		return $total;
	}

	/**
	 * Constructs a URL for accessing a specific discount by its ID on the front end.
	 * This URL can be used to directly apply the discount to a potential purchase.
	 * If the discount ID is invalid or the discount does not exist, the function returns false.
	 *
	 * @param int $discount_id The unique identifier for the discount.
	 *
	 * @return string|false The URL leading to the application of the discount on the site's front end if the discount
	 *                      exists, otherwise false.
	 */
	public static function get_url( int $discount_id = 0 ) {

		// Bail if no discount ID
		if ( empty( $discount_id ) ) {
			return false;
		}

		// Attempt to retrieve the discount.
		$discount = edd_get_discount( $discount_id );

		// Ensure the discount exists.
		if ( empty( $discount ) ) {
			return false;
		}

		// Construct and return the discount application URL.
		return add_query_arg( array( 'discount' => $discount->code ), home_url( '/' ) );
	}

	/**
	 * Generate a link to the discount details or application page.
	 *
	 * @param int    $discount_id The ID of the discount.
	 * @param string $label       The text for the link.
	 *
	 * @return string HTML link to the discount details or application page, or mdash if not available.
	 */
	public static function get_link( int $discount_id = 0, string $label = '' ): ?string {
		if ( empty( $discount_id ) ) {
			return null;
		}

		$discount = edd_get_discount( $discount_id );
		if ( ! $discount ) {
			return null;
		}

		if ( empty( $label ) ) {
			$label = ! empty( $discount->name ) ? $discount->name : $discount->code;
		}

		$url = self::get_url( $discount->id );

		return Element::link( $url, $label );
	}

	/**
	 * Generates an admin URL for editing a specific discount by its ID.
	 * This function constructs the URL needed to access the discount editing page within the WordPress admin area.
	 * If the discount ID is invalid or the discount cannot be found, the function returns false.
	 *
	 * @param int $discount_id The unique identifier for the discount to be edited.
	 *
	 * @return string|false The admin URL for editing the discount if it exists, otherwise false.
	 */
	public static function get_admin_url( int $discount_id = 0 ) {

		// Bail if no discount ID
		if ( empty( $discount_id ) ) {
			return false;
		}

		// Attempt to retrieve the discount.
		$discount = edd_get_discount( $discount_id );

		// Ensure the discount exists.
		if ( empty( $discount ) ) {
			return false;
		}

		// Construct and return the admin URL for editing the discount.
		return edd_get_admin_url( array(
			'page'       => 'edd-discounts',
			'edd-action' => 'edit_discount',
			'discount'   => absint( $discount->id )
		) );
	}

	/**
	 * Generate a link to the discount admin page.
	 *
	 * @param int    $discount_id The ID of the discount.
	 * @param string $label       The text for the link.
	 *
	 * @return string HTML link to the discount admin page, or mdash if not available.
	 */
	public static function get_admin_link( int $discount_id = 0, string $label = '' ): ?string {
		if ( empty( $discount_id ) ) {
			return null;
		}

		$discount = edd_get_discount( $discount_id );
		if ( ! $discount ) {
			return null;
		}

		if ( empty( $label ) ) {
			$label = ! empty( $discount->name ) ? $discount->name : $discount->code;
		}

		$url = self::get_admin_url( $discount->id );

		return Element::link( $url, $label );
	}

	/**
	 * Generate a unique discount code.
	 *
	 * @return string
	 */
	private static function generate_unique_code(): string {
		$code = strtoupper( substr( md5( uniqid( wp_generate_uuid4(), true ) ), 0, 8 ) );

		// Ensure the code is unique
		while ( edd_get_discount_by_code( $code ) ) {
			$code = strtoupper( substr( md5( uniqid( wp_generate_uuid4(), true ) ), 0, 8 ) );
		}

		return $code;
	}

}