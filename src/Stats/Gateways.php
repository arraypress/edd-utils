<?php
/**
 * Gateway Statistics for Easy Digital Downloads (EDD)
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
use ArrayPress\EDD\Gateways\Gateway;
use EDD\Stats;
use Exception;

class Gateways {

	/**
	 * Get the most popular payment gateways by order count.
	 *
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 * @param bool        $formatted  Whether to return formatted amounts. Default false
	 * @param int         $limit      Number of gateways to return. Default 5
	 *
	 * @return array List of gateways with their order counts
	 * @throws Exception
	 */
	public static function get_most_popular( ?string $start_date = null, ?string $end_date = null, bool $formatted = false, int $limit = 5 ): array {
		$args = [
			'output'   => 'raw',
			'grouped'  => true,
			'function' => 'COUNT'
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats   = new Stats( $args );
		$results = $stats->get_gateway_sales();

		// Sort by total orders in descending order
		usort( $results, static function ( $a, $b ) {
			return $b->total - $a->total;
		} );

		// Format if needed
		if ( $formatted ) {
			array_walk( $results, static function( &$item ) {
				$item->total = edd_format_amount( $item->total, true );
			} );
		}

		// Limit results
		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get the highest earning payment gateways.
	 *
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false
	 * @param bool        $formatted     Whether to return formatted amounts. Default false
	 * @param int         $limit         Number of gateways to return. Default 5
	 *
	 * @return array List of gateways with their earnings
	 * @throws Exception
	 */
	public static function get_highest_earning( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, bool $formatted = false, int $limit = 5 ): array {
		$args = [
			'output'        => 'raw',
			'grouped'       => true,
			'exclude_taxes' => $exclude_taxes
		];

		// Merge date parameters if they are provided
		$args = array_merge( $args, Generate::date_params( $start_date, $end_date ) );

		$stats   = new Stats( $args );
		$results = $stats->get_gateway_earnings();

		// Sort by earnings in descending order
		usort( $results, static function ( $a, $b ) {
			return $b->earnings - $a->earnings;
		} );

		// Format if needed
		if ( $formatted ) {
			array_walk( $results, static function( &$item ) {
				$item->earnings = edd_currency_filter( edd_format_amount( $item->earnings ) );
			} );
		}

		// Limit results
		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get gateways with the lowest refund rates.
	 *
	 * @param string|null $start_date Start date for the query (optional)
	 * @param string|null $end_date   End date for the query (optional)
	 * @param int         $limit      Number of gateways to return. Default 5
	 *
	 * @return array List of gateways with their refund rates
	 * @throws Exception
	 */
	public static function get_lowest_refund_rates( ?string $start_date = null, ?string $end_date = null, int $limit = 5 ): array {
		$gateways = edd_get_payment_gateways();
		$results  = [];

		foreach ( $gateways as $id => $gateway ) {
			$refund_rate = Gateway::get_refund_rate( $id, $start_date, $end_date );
			if ( $refund_rate !== null ) {
				$results[] = (object) [
					'gateway'     => $id,
					'name'        => $gateway['admin_label'],
					'refund_rate' => $refund_rate
				];
			}
		}

//		var_dump( $results );

		// Sort by refund rate in ascending order
		usort( $results, function ( $a, $b ) {
			return $a->refund_rate <=> $b->refund_rate;
		} );

		// Limit results
		return array_slice( $results, 0, $limit );
	}

	/**
	 * Get gateways with the highest average order value.
	 *
	 * @param string|null $start_date    Start date for the query (optional)
	 * @param string|null $end_date      End date for the query (optional)
	 * @param bool        $exclude_taxes Whether to exclude taxes from calculations. Default false
	 * @param int         $limit         Number of gateways to return. Default 5
	 *
	 * @return array List of gateways with their average order values
	 * @throws Exception
	 */
	public static function get_highest_average_order_value( ?string $start_date = null, ?string $end_date = null, bool $exclude_taxes = false, int $limit = 5 ): array {
		$gateways = edd_get_payment_gateways();
		$results  = [];

		foreach ( $gateways as $id => $gateway ) {
			$average = Gateway::get_average_order_value( $id, $start_date, $end_date, $exclude_taxes, false );
			if ( $average !== false ) {
				$results[] = (object) [
					'gateway' => $id,
					'name'    => $gateway['admin_label'],
					'average' => $average,
				];
			}
		}

		// Sort by average order value in descending order
		usort( $results, static function ( $a, $b ) {
			return $b->average <=> $a->average;
		} );

		// Limit results
		return array_slice( $results, 0, $limit );
	}

}