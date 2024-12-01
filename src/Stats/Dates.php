<?php
/**
 * Stats Date Operations Class for Easy Digital Downloads (EDD)
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Stats;

// Include required files
require_once EDD_PLUGIN_DIR . 'includes/reports/reports-functions.php';

use function EDD\Reports\parse_dates_for_range;

class Dates {

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
	 * Parse and merge query arguments with date period if applicable.
	 *
	 * @param array       $args   Query arguments.
	 * @param string|null $period Date range period.
	 *
	 * @return array Parsed query arguments.
	 */
	public static function parse_args( array $args, ?string $period ): array {
		if ( ! self::is_valid_period( $period ) ) {
			return $args;
		}

		$dates = parse_dates_for_range( $period );

		$date_args = array(
			'start' => $dates['start']->format( 'Y-m-d H:i:s' ),
			'end'   => $dates['end']->format( 'Y-m-d H:i:s' ),
		);

		return wp_parse_args( $args, $date_args );
	}

	/**
	 * Check if a period is valid.
	 *
	 * @param string|null $period The period to check.
	 *
	 * @return bool Whether the period is valid.
	 */
	public static function is_valid_period( ?string $period ): bool {
		return ! ( null === $period || 'all_time' === $period || ! in_array( $period, self::$periods, true ) );
	}

	/**
	 * Get start and end dates for a period.
	 *
	 * @param string|null $period The period to get dates for.
	 *
	 * @return array|null Array with start and end dates, or null if invalid period.
	 */
	public static function get_dates( ?string $period ): ?array {
		if ( ! self::is_valid_period( $period ) ) {
			return null;
		}

		$dates = parse_dates_for_range( $period );

		return [
			'start' => $dates['start']->format( 'Y-m-d H:i:s' ),
			'end'   => $dates['end']->format( 'Y-m-d H:i:s' )
		];
	}

	/**
	 * Get all valid time periods.
	 *
	 * @return array Array of valid time periods.
	 */
	public static function get_periods(): array {
		return self::$periods;
	}

	/**
	 * Check if a date falls within a period.
	 *
	 * @param string      $date   The date to check in Y-m-d H:i:s format
	 * @param string|null $period The period to check against
	 *
	 * @return bool Whether the date falls within the period
	 */
	public static function is_date_in_period( string $date, ?string $period ): bool {
		if ( ! self::is_valid_period( $period ) ) {
			return false;
		}

		$dates      = self::get_dates( $period );
		$check_date = strtotime( $date );

		return $check_date >= strtotime( $dates['start'] ) &&
		       $check_date <= strtotime( $dates['end'] );
	}

}