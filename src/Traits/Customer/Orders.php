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

use ArrayPress\EDD\Date\Calc;
use ArrayPress\EDD\Date\Common;
use ArrayPress\EDD\Date\Generate;
use ArrayPress\Utils\Common\Cache;
use EDD_Customer;

trait Orders {
	use Core;

	/**
	 * Retrieve the number of orders made by a customer.
	 *
	 * @param int  $customer_id The ID of the customer.
	 * @param bool $formatted   Whether to return a formatted string or raw count.
	 *
	 * @return int|string|null The number of orders as an integer or formatted string, or null on failure.
	 */
	public static function get_purchase_count( int $customer_id, bool $formatted = false ) {
		$customer = self::get_validated( $customer_id );
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
		$customer = self::get_validated( $customer_id );
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

	/**
	 * Check if a customer has any orders based on their customer ID.
	 *
	 * @param int $customer_id The customer ID to check.
	 *
	 * @return bool Whether the customer has any orders.
	 */
	public static function has_orders( int $customer_id ): bool {
		if ( empty( $customer_id ) ) {
			return false;
		}

		$cache_key = Cache::generate_key( 'edd_customer_has_orders', $customer_id );

		return Cache::remember( $cache_key, function () use ( $customer_id ) {
			$query_args = array(
				'customer_id' => $customer_id,
				'status__in'  => edd_get_complete_order_statuses(),
				'type'        => 'sale',
				'number'      => 1,
			);

			return ! empty( edd_get_orders( $query_args ) );
		}, HOUR_IN_SECONDS );
	}

	/**
	 * Retrieve the average order value for a customer.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param bool  $formatted   Whether to return a formatted string or raw value.
	 * @param array $query_args  Additional query arguments for orders.
	 *
	 * @return float|string|null The average order value, formatted string if requested, or null on failure.
	 */
	public static function get_average_order_value( int $customer_id = 0, bool $formatted = false, array $query_args = array() ) {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$default_args = array(
			'customer_id' => $customer->id,
			'order'       => 'DESC',
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'number'      => 999999999
		);

		$query_args = wp_parse_args( $query_args, $default_args );

		$orders = edd_get_orders( $query_args );

		if ( empty( $orders ) ) {
			return null; // No orders found for the customer.
		}

		// Calculate the total value of all orders
		$total_value = array_sum( wp_list_pluck( $orders, 'total' ) );

		// Calculate the average order value.
		$average_order_value = $total_value / count( $orders );

		// Return the average order value, formatted if requested.
		if ( $formatted ) {
			return edd_currency_filter( edd_format_amount( $average_order_value ) );
		}

		return $average_order_value;
	}

	/**
	 * Retrieves the highest order value for a given customer.
	 *
	 * @param int   $customer_id The ID of the customer whose highest order value is to be retrieved.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return float|null The value of the highest order as a float, or null if no orders are found.
	 */
	public static function get_highest_order_value( int $customer_id, array $query_args = [] ): ?float {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$default_args = [
			'customer_id' => $customer->id,
			'order'       => 'DESC',
			'type'        => 'sale',
			'status__in'  => edd_get_complete_order_statuses(),
			'orderby'     => 'total',
			'fields'      => 'total',
			'number'      => 1,
		];

		$query_args = wp_parse_args( $query_args, $default_args );

		$orders = edd_get_orders( $query_args );

		if ( empty( $orders ) ) {
			return null;
		}

		return floatval( $orders[0] );
	}

	/**
	 * Retrieves the lowest order value for a given customer.
	 *
	 * @param int   $customer_id The ID of the customer whose lowest order value is to be retrieved.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return float|null The value of the lowest order as a float, or null if no orders are found.
	 */
	public static function get_lowest_order_value( int $customer_id, array $query_args = [] ): ?float {
		return self::get_highest_order_value( $customer_id, wp_parse_args( $query_args, [ 'order' => 'ASC' ] ) );
	}

	/**
	 * Retrieve the latest order ID for a given customer.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return int|null The ID of the latest order, or null if no orders are found.
	 */
	public static function get_latest_order_id( int $customer_id, array $query_args = [] ): ?int {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$args = wp_parse_args( $query_args, [
			'customer_id' => $customer->id,
			'order'       => 'DESC',
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'fields'      => 'id',
			'number'      => 1
		] );

		$orders = edd_get_orders( $args );

		return ! empty( $orders ) ? absint( current( $orders ) ) : null;
	}

	/**
	 * Retrieve the oldest order ID for a customer.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $query_args  Optional. Additional arguments to customize the orders query.
	 *
	 * @return int|null The ID of the oldest order, or null if no orders are found.
	 */
	public static function get_oldest_order_id( int $customer_id, array $query_args = [] ): ?int {
		return self::get_latest_order_id( $customer_id, wp_parse_args( $query_args, [ 'order' => 'ASC' ] ) );
	}

	/**
	 * Check the order velocity for a customer.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param int    $count       The number of orders to check for.
	 * @param string $unit        The time unit ('minutes', 'hours', 'days').
	 * @param int    $period      The number of time units to look back.
	 *
	 * @return bool True if the customer has made $count or more orders in the specified time period.
	 * @throws \Exception
	 */
	public static function check_order_velocity( int $customer_id, int $count, string $unit, int $period ): bool {
		if ( empty( $customer_id ) ) {
			return false;
		}

		$end_date   = Common::now();
		$start_date = Calc::expiration( - $period, $unit, $end_date );

		$args = [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'date_query'  => Generate::date_range_query( $start_date, $end_date ),
			'number'      => $count, // We only need to know if there are at least $count orders
		];

		$orders = edd_get_orders( $args );

		return count( $orders ) >= $count;
	}

	/**
	 * Check the order item velocity for a customer.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param int    $count       The number of order items to check for.
	 * @param string $unit        The time unit ('minutes', 'hours', 'days').
	 * @param int    $period      The number of time units to look back.
	 *
	 * @return bool True if the customer has purchased $count or more items in the specified time period.
	 * @throws \Exception
	 */
	public static function check_order_item_velocity( int $customer_id, int $count, string $unit, int $period ): bool {
		if ( empty( $customer_id ) ) {
			return false;
		}

		$end_date   = Common::now();
		$start_date = Calc::expiration( - $period, $unit, $end_date );

		$args = [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'date_query'  => Generate::date_range_query( $start_date, $end_date ),
		];

		$orders = edd_get_orders( $args );

		$item_count = 0;
		foreach ( $orders as $order ) {
			$item_count += edd_count_order_items( [ 'order_id' => $order->id ] );
			if ( $item_count >= $count ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve the count of orders made by a specific customer within a date range.
	 *
	 * @param int    $customer_id    The ID of the customer.
	 * @param string $start_date     Optional. The start date for the date range filter in 'Y-m-d' format.
	 * @param string $end_date       Optional. The end date for the date range filter in 'Y-m-d' format.
	 * @param bool   $convert_to_utc Optional. Whether to convert dates to UTC. Default true.
	 *
	 * @return int|null The number of orders or null if the customer ID is invalid.
	 * @throws \Exception
	 */
	public static function get_order_count_by_date_range(
		int $customer_id,
		string $start_date = '',
		string $end_date = '',
		bool $convert_to_utc = true
	): ?int {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$args = [
			'customer_id' => $customer->id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'number'      => 999999999,
		];

		// Add date query using the Date utility
		$date_query = Generate::date_range_query( $start_date, $end_date, $convert_to_utc );
		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		return edd_count_orders( $args );
	}

	/**
	 * Retrieve total earnings from orders made by a specific customer within a date range.
	 *
	 * @param int    $customer_id    The ID of the customer.
	 * @param string $start_date     Optional. The start date for the date range filter in 'Y-m-d' format.
	 * @param string $end_date       Optional. The end date for the date range filter in 'Y-m-d' format.
	 * @param bool   $convert_to_utc Optional. Whether to convert dates to UTC. Default true.
	 *
	 * @return float|null The total earnings or null if the customer ID is invalid.
	 * @throws \Exception
	 */
	public static function get_order_earnings_by_date_range(
		int $customer_id,
		string $start_date = '',
		string $end_date = '',
		bool $convert_to_utc = true
	): ?float {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		$args = [
			'customer_id' => $customer->id,
			'status__in'  => edd_get_complete_order_statuses(),
			'type'        => 'sale',
			'number'      => - 1,
			'fields'      => 'total',
		];

		// Add date query using the Date utility
		$date_query = Generate::date_range_query( $start_date, $end_date, $convert_to_utc );
		if ( ! empty( $date_query ) ) {
			$args['date_query'] = $date_query;
		}

		$total = edd_get_orders( $args );

		// Ensure the total is a non-negative float
		return max( 0, (float) $total );
	}

	/**
	 * Check if a customer has any orders based on provided email addresses.
	 *
	 * @param array|string $user_emails Array or single email address to check.
	 *
	 * @return bool Whether the customer has any orders, or false if no valid input.
	 */
	public static function has_orders_by_emails( $user_emails ): bool {
		// Ensure $user_emails is an array even if a single email is passed as a string
		$user_emails = (array) $user_emails;

		// Filter out empty emails and trim whitespace
		$user_emails = array_filter( array_map( 'trim', $user_emails ) );

		if ( empty( $user_emails ) ) {
			return false;
		}

		$cache_key = Cache::generate_key( 'edd_customer_has_orders_by_emails', $user_emails );

		return Cache::remember( $cache_key, function () use ( $user_emails ) {
			$customer_ids = array_unique( array_filter( array_map( function ( $email ) {
				$customer = edd_get_customer_by( 'email', $email );

				return $customer ? $customer->id : null;
			}, $user_emails ) ) );

			if ( empty( $customer_ids ) ) {
				return false;
			}

			$query_args = array(
				'customer_id__in' => $customer_ids,
				'status__in'      => edd_get_complete_order_statuses(),
				'type'            => 'sale',
				'number'          => 1,
			);

			return ! empty( edd_get_orders( $query_args ) );
		}, HOUR_IN_SECONDS );
	}

}