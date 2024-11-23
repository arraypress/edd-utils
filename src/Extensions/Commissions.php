<?php
/**
 * Commission Operations Class for Easy Digital Downloads (EDD)
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Extensions;

// Include required files
require_once EDD_PLUGIN_DIR . 'includes/reports/reports-functions.php';

class Commissions {

	/**
	 * Get total commission earnings for a date range.
	 *
	 * @param string $range Date range (today, this_month, last_month, etc)
	 * @param array  $args  Optional. Additional query arguments.
	 *
	 * @return float Total commission earnings.
	 */
	public static function get_earnings( string $range = 'this_month', array $args = array() ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$dates = \EDD\Reports\parse_dates_for_range( $range );

		$query_args = array(
			'date' => array(
				'start' => $dates['start']->copy()->format( 'mysql' ),
				'end'   => $dates['end']->copy()->format( 'mysql' ),
			)
		);

		$query_args = wp_parse_args( $args, $query_args );

		return (float) edd_commissions()->commissions_db->sum( 'amount', $query_args );
	}

	/**
	 * Get commission count for a date range.
	 *
	 * @param string $range Date range (today, this_month, last_month, etc)
	 * @param array  $args  Optional. Additional query arguments.
	 *
	 * @return int Number of commissions.
	 */
	public static function get_count( string $range = 'this_month', array $args = array() ): int {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0;
		}

		$dates = \EDD\Reports\parse_dates_for_range( $range );

		$query_args = array(
			'count'      => true,
			'date_query' => array(
				'after'     => $dates['start']->copy()->format( 'mysql' ),
				'before'    => $dates['end']->copy()->format( 'mysql' ),
				'inclusive' => true,
			)
		);

		$query_args = wp_parse_args( $args, $query_args );

		return (int) eddc_get_commissions( $query_args );
	}

	/**
	 * Get average commission amount for a date range.
	 *
	 * @param string $range Date range (today, this_month, last_month, etc)
	 * @param array  $args  Optional. Additional query arguments.
	 *
	 * @return float Average commission amount.
	 */
	public static function get_average( string $range = 'this_month', array $args = array() ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$dates = \EDD\Reports\parse_dates_for_range( $range );

		$query_args = array(
			'date' => array(
				'start' => $dates['start']->copy()->format( 'mysql' ),
				'end'   => $dates['end']->copy()->format( 'mysql' ),
			)
		);

		$query_args = wp_parse_args( $args, $query_args );

		return (float) edd_commissions()->commissions_db->avg( 'amount', $query_args );
	}

	/**
	 * Get average commission per vendor for a date range.
	 *
	 * @param string $range Date range (today, this_month, last_month, etc)
	 * @param array  $args  Optional. Additional query arguments.
	 *
	 * @return float Average commission per vendor.
	 */
	public static function get_vendor_average( string $range = 'this_month', array $args = array() ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$dates = \EDD\Reports\parse_dates_for_range( $range );

		$query_args = array(
			'date' => array(
				'start' => $dates['start']->copy()->format( 'mysql' ),
				'end'   => $dates['end']->copy()->format( 'mysql' ),
			)
		);

		$query_args = wp_parse_args( $args, $query_args );

		return (float) edd_commissions()->commissions_db->avg( 'amount', $query_args, 'user_id' );
	}

}