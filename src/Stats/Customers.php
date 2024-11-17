<?php
/**
 * Customer Statistics for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Stats
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Stats;

use ArrayPress\EDD\Date\Generate;
use EDD\Stats;
use Exception;

class Customers {

	/**
	 * Retrieve the most valuable customers based on their total spending.
	 *
	 * @param string|null $start_date    Start date for the query (optional).
	 * @param string|null $end_date      End date for the query (optional).
	 * @param bool        $exclude_taxes Whether to exclude taxes from the total. Default false.
	 * @param string      $currency      Currency to filter by. Default empty string.
	 * @param int         $limit         Number of customers to retrieve. Default 5.
	 *
	 * @return array An array of customer data.
	 * @throws Exception
	 */
	public static function get_most_valuable( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, string $currency = '', int $limit = 5 ): array {
		$args = [
			'exclude_taxes' => $exclude_taxes,
			'currency'      => $currency,
			'number'        => $limit,
			'output'        => 'customers',
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats = new Stats( $args );

		return $stats->get_most_valuable_customers();
	}

	/**
	 * Get IDs of most valuable customers.
	 *
	 * @param string|null $start_date    Start date for the query (optional).
	 * @param string|null $end_date      End date for the query (optional).
	 * @param bool        $exclude_taxes Whether to exclude taxes from the total. Default false.
	 * @param string      $currency      Currency to filter by. Default empty string.
	 * @param int         $limit         Number of customer IDs to retrieve. Default 5.
	 *
	 * @return array Array of customer IDs
	 * @throws Exception
	 */
	public static function get_most_valuable_ids( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, string $currency = '', int $limit = 5 ): array {
		$customers = self::get_most_valuable( $start_date, $end_date, $exclude_taxes, $currency, $limit );

		return array_map( static fn( $customer ) => $customer->customer_id, $customers );
	}

	/**
	 * Get emails of most valuable customers.
	 *
	 * @param string|null $start_date    Start date for the query (optional).
	 * @param string|null $end_date      End date for the query (optional).
	 * @param bool        $exclude_taxes Whether to exclude taxes from the total. Default false.
	 * @param string      $currency      Currency to filter by. Default empty string.
	 * @param int         $limit         Number of customer emails to retrieve. Default 5.
	 *
	 * @return array Array of customer emails
	 * @throws Exception
	 */
	public static function get_most_valuable_emails( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, string $currency = '', int $limit = 5 ): array {
		$customers = self::get_most_valuable( $start_date, $end_date, $exclude_taxes, $currency, $limit );

		return array_map( static fn( $customer ) => $customer->object->email, $customers );
	}

	/**
	 * Get names of most valuable customers.
	 *
	 * @param string|null $start_date    Start date for the query (optional).
	 * @param string|null $end_date      End date for the query (optional).
	 * @param bool        $exclude_taxes Whether to exclude taxes from the total. Default false.
	 * @param string      $currency      Currency to filter by. Default empty string.
	 * @param int         $limit         Number of customer emails to retrieve. Default 5.
	 *
	 * @return array Array of customer names
	 * @throws Exception
	 */
	public static function get_most_valuable_names( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, string $currency = '', int $limit = 5 ): array {
		$customers = self::get_most_valuable( $start_date, $end_date, $exclude_taxes, $currency, $limit );

		return array_map( static fn( $customer ) => $customer->object->name, $customers );
	}

	/**
	 * Get the average customer lifetime value.
	 *
	 * @param string|null $start_date    Start date for the query (optional).
	 * @param string|null $end_date      End date for the query (optional).
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false.
	 *
	 * @return float The average customer lifetime value.
	 * @throws Exception
	 */
	public static function get_average_lifetime_value( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false ): float {
		$args = [
			'exclude_taxes' => $exclude_taxes,
			'function'      => 'AVG',
			'output'        => 'raw',
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats = new Stats( $args );

		return (float) $stats->get_customer_lifetime_value();
	}

	/**
	 * Get the average number of lifetime sales per customer.
	 *
	 * @param string|null $start_date Start date for the query (optional).
	 * @param string|null $end_date   End date for the query (optional).
	 *
	 * @return int The average number of lifetime sales per customer.
	 * @throws Exception
	 */
	public static function get_average_lifetime_sales( ?string $start_date = null, ?string $end_date = null ): int {
		$args = [
			'function' => 'AVG',
			'output'   => 'raw',
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats = new Stats( $args );

		return (int) $stats->get_customer_order_count();
	}

	/**
	 * Calculate customer retention rate.
	 *
	 * @param string|null $start_date Start date for the query (optional).
	 * @param string|null $end_date   End date for the query (optional).
	 *
	 * @return float Customer retention rate as a percentage.
	 * @throws Exception
	 */
	public static function calculate_retention_rate( ?string $start_date = null, ?string $end_date = null ): float {

		// Merge date parameters if they are provided
		$args = array_merge( [], Generate::date_params( $start_date, $end_date ) );

		$stats              = new Stats( $args );
		$total_customers    = $stats->get_customer_count();
		$retained_customers = $stats->get_customer_count( [ 'purchase_count' => true ] );

		if ( $total_customers === 0 ) {
			return 0.0;
		}

		return ( $retained_customers / $total_customers ) * 100;
	}

	/**
	 * Get the average number of unique IP addresses per customer for file downloads.
	 *
	 * @return float The average number of unique IP addresses per customer for file downloads.
	 */
	public static function get_average_ip_count(): float {
		global $wpdb;

		$sql_query = "
	            SELECT customer_id, COUNT(DISTINCT ip) as ip_count
	            FROM {$wpdb->prefix}edd_logs_file_downloads
	            WHERE customer_id != 0
	            GROUP BY customer_id
	        ";

		$results = $wpdb->get_results( $sql_query );

		if ( empty( $results ) ) {
			return 0.0;
		}

		$total_ips      = array_sum( array_column( $results, 'ip_count' ) );
		$customer_count = count( $results );

		return $total_ips / $customer_count;
	}

}