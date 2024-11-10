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

use ArrayPress\EDD\Date\Generate;
use EDD\Stats;
use Exception;

class Discounts {

	/**
	 * Get the most popular discounts based on usage count.
	 *
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 * @param int         $limit      Number of discounts to return. Default 5
	 *
	 * @return array List of discounts with their usage counts
	 * @throws Exception
	 */
	public static function get_most_popular( ?string $start_date = null, ?string $end_date = null, int $limit = 5 ): array {
		$args = array_merge(
			[
				'number'  => $limit,
				'output'  => 'raw',
				'grouped' => true
			],
			Generate::date_params( $start_date, $end_date )
		);

		$stats   = new Stats( $args );
		$results = $stats->get_most_popular_discounts();

		// Sort by count in descending order
		usort( $results, static function ( $a, $b ) {
			return $b->count - $a->count;
		} );

		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get discounts with the highest savings amount.
	 *
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations
	 * @param bool        $formatted     Whether to format the amounts
	 * @param int         $limit         Number of discounts to return. Default 5
	 *
	 * @return array List of discounts with their savings amounts
	 * @throws Exception
	 */
	public static function get_highest_savings( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, bool $formatted = false, int $limit = 5 ): array {
		$discounts = edd_get_discounts( array( 'number' => 99999 ) );
		$results   = array();

		foreach ( $discounts as $discount ) {
			$args = array_merge(
				[
					'discount_code' => $discount->code,
					'exclude_taxes' => $exclude_taxes,
					'output'        => 'raw'
				],
				Generate::date_params( $start_date, $end_date )
			);

			$stats  = new Stats( $args );
			$amount = $stats->get_discount_savings();

			if ( $amount > 0 ) {
				$results[] = (object) array(
					'discount_id' => $discount->id,
					'code'        => $discount->code,
					'name'        => $discount->name,
					'savings'     => $amount,  // Keep raw amount here
					'object'      => $discount
				);
			}
		}

		// Sort by savings amount in descending order
		usort( $results, static function ( $a, $b ) {
			return $b->savings <=> $a->savings;
		} );

		// Slice the top results and format if necessary
		$top_results = array_slice( $results, 0, $limit );

		// Apply formatting if requested
		if ( $formatted ) {
			foreach ( $top_results as $result ) {
				$result->savings = edd_currency_filter( edd_format_amount( $result->savings ) );
			}
		}

		return $top_results;
	}


	/**
	 * Get the average savings amount per order with discounts.
	 *
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations
	 * @param bool        $formatted     Whether to format the amount
	 *
	 * @return string|float Average savings amount
	 * @throws Exception
	 */
	public static function get_average_savings( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, bool $formatted = false ) {
		$args = array_merge(
			[
				'exclude_taxes' => $exclude_taxes,
				'output'        => $formatted ? 'formatted' : 'raw'
			],
			Generate::date_params( $start_date, $end_date )
		);

		$stats  = new Stats( $args );

		return $stats->get_average_discount_amount();
	}


	/**
	 * Get the ratio of orders with discounts to orders without discounts.
	 *
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 *
	 * @return string Ratio in the format "X:Y"
	 * @throws Exception
	 */
	public static function get_usage_ratio( ?string $start_date = null, ?string $end_date = null ): string {
		$args = array_merge(
			[ 'output' => 'raw' ],
			Generate::date_params( $start_date, $end_date )
		);

		$stats = new Stats( $args );

		return $stats->get_ratio_of_discounted_orders();
	}

	/**
	 * Get total savings across all discounts.
	 *
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations
	 * @param bool        $formatted     Whether to format the amount
	 *
	 * @return string|float Total savings amount
	 * @throws Exception
	 */
	public static function get_total_savings( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, bool $formatted = false ) {
		$args = array_merge(
			[
				'exclude_taxes' => $exclude_taxes,
				'output'        => $formatted ? 'formatted' : 'raw'
			],
			Generate::date_params( $start_date, $end_date )
		);

		$stats  = new Stats( $args );

		return $stats->get_discount_savings();
	}

}