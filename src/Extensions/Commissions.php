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
	 * Valid time periods for date filtering
	 *
	 * @var array
	 */
	private static array $periods = [
		'all_time',
		'today',
		'yesterday',
		'this_week',
		'last_week',
		'last_30_days',
		'this_month',
		'last_month',
		'this_quarter',
		'last_quarter',
		'this_year',
		'last_year'
	];

	/**
	 * Get total commission earnings.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 *
	 * @return float Total commission earnings.
	 */
	public static function get_earnings( array $args = array(), ?string $period = 'this_month' ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$query_args = self::parse_args( $args, $period );

		return (float) edd_commissions()->commissions_db->sum( 'amount', $query_args );
	}

	/**
	 * Get commission count.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 *
	 * @return int Number of commissions.
	 */
	public static function get_count( array $args = array(), ?string $period = 'this_month' ): int {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0;
		}

		$query_args          = self::parse_args( $args, $period );
		$query_args['count'] = true;

		return (int) eddc_get_commissions( $query_args );
	}

	/**
	 * Get average commission amount.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 *
	 * @return float Average commission amount.
	 */
	public static function get_average( array $args = array(), ?string $period = 'this_month' ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$query_args = self::parse_args( $args, $period );

		return (float) edd_commissions()->commissions_db->avg( 'amount', $query_args );
	}

	/**
	 * Get average commission per vendor.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 *
	 * @return float Average commission per vendor.
	 */
	public static function get_vendor_average( array $args = array(), ?string $period = 'this_month' ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$query_args = self::parse_args( $args, $period );

		return (float) edd_commissions()->commissions_db->avg( 'amount', $query_args, 'user_id' );
	}

	/**
	 * Parse and merge query arguments with date period if applicable.
	 *
	 * @param array       $args   Query arguments.
	 * @param string|null $period Date range period.
	 *
	 * @return array Parsed query arguments.
	 */
	private static function parse_args( array $args, ?string $period ): array {
		if ( null === $period || 'all_time' === $period || ! in_array( $period, self::$periods, true ) ) {
			return $args;
		}

		$dates = \EDD\Reports\parse_dates_for_range( $period );

		$date_args = array(
			'date' => array(
				'start' => $dates['start']->copy()->format( 'mysql' ),
				'end'   => $dates['end']->copy()->format( 'mysql' ),
			)
		);

		return wp_parse_args( $args, $date_args );
	}

	/**
	 * Get valid time periods.
	 *
	 * @return array Array of valid time periods.
	 */
	public static function get_periods(): array {
		return self::$periods;
	}

}