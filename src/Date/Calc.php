<?php
/**
 * Date Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Date;

use ArrayPress\Utils\Common\Date as DateUtils;
use Exception;

class Calc {

	/**
	 * Valid period types and their variations
	 */
	private const PERIOD_TYPES = [
		'hour'    => [ 'h', 'hour', 'hours', 'hr', 'hrs' ],
		'day'     => [ 'd', 'day', 'days' ],
		'week'    => [ 'w', 'week', 'weeks', 'wk', 'wks' ],
		'month'   => [ 'm', 'month', 'months', 'mo', 'mos' ],
		'quarter' => [ 'q', 'quarter', 'quarters', 'qtr', 'qtrs' ],
		'year'    => [ 'y', 'year', 'years', 'yr', 'yrs' ]
	];

	/**
	 * Normalize period type to standard format.
	 *
	 * @param string $period Period type to normalize.
	 *
	 * @return string Normalized period type or 'day' if not recognized.
	 */
	private static function normalize_period( string $period ): string {
		$period = strtolower( trim( $period ) );
		foreach ( self::PERIOD_TYPES as $standard => $variations ) {
			if ( in_array( $period, $variations, true ) ) {
				return $standard;
			}
		}

		return 'day'; // Default fallback
	}

	/**
	 * Calculate a future date based on length and period.
	 *
	 * @param int    $length   The duration length.
	 * @param string $period   The period type (supports various formats like 'd', 'day', 'days', etc.)
	 * @param string $start    Optional. The start date for calculations. Default 'now'.
	 * @param string $timezone Optional. The timezone for the result.
	 *
	 * @return string The calculated future date.
	 * @throws Exception
	 */
	public static function add_date( int $length, string $period, string $start = '', string $timezone = '' ): string {
		return self::modify_date( $length, $period, $start, $timezone );
	}

	/**
	 * Calculate a past date based on length and period.
	 *
	 * @param int    $length   The duration length.
	 * @param string $period   The period type (supports various formats like 'd', 'day', 'days', etc.)
	 * @param string $start    Optional. The start date for calculations. Default 'now'.
	 * @param string $timezone Optional. The timezone for the result.
	 *
	 * @return string The calculated past date.
	 * @throws Exception
	 */
	public static function subtract_date( int $length, string $period, string $start = '', string $timezone = '' ): string {
		return self::modify_date( - $length, $period, $start, $timezone );
	}

	/**
	 * Modify a date by adding or subtracting a duration.
	 *
	 * @param int    $length   The duration length (positive for future, negative for past).
	 * @param string $period   The period type.
	 * @param string $start    The start date for calculations.
	 * @param string $timezone The timezone for the result.
	 *
	 * @return string The modified date.
	 * @throws Exception
	 */
	private static function modify_date( int $length, string $period, string $start = '', string $timezone = '' ): string {
		$timezone = $timezone ?: Timezone::get_id();
		$date     = Common::parse_date( $start ?: 'now', $timezone );
		$period   = self::normalize_period( $period );

		switch ( $period ) {
			case 'hour':
				$date->addHours( $length );
				break;
			case 'week':
				$date->addWeeks( $length );
				break;
			case 'month':
				$date->addMonths( $length );
				break;
			case 'quarter':
				$date->addMonths( $length * 3 );
				break;
			case 'year':
				$date->addYears( $length );
				break;
			default:
			case 'day':
				$date->addDays( $length );
				break;
		}

		return $date->format( COMMON::DEFAULT_FORMAT );
	}

	/**
	 * Alias for calculate_expiration for backward compatibility.
	 *
	 * @param int    $length   The duration length.
	 * @param string $period   The period type.
	 * @param string $start    The start date for calculations.
	 * @param string $timezone The timezone for the result.
	 *
	 * @return string The calculated expiration date.
	 * @throws Exception
	 */
	public static function expiration( int $length, string $period = 'day', string $start = '', string $timezone = '' ): string {
		return self::add_date( $length, $period, $start, $timezone );
	}

	/**
	 * Get relative date in the past.
	 *
	 * @param int    $length   Number of units to go back.
	 * @param string $period   Period type (e.g., 'days', 'months', 'years').
	 * @param string $timezone Optional. The timezone for the result.
	 *
	 * @return string The calculated past date.
	 * @throws Exception
	 */
	public static function get_past_date( int $length = 0, string $period = 'day', string $timezone = '' ): string {
		return self::subtract_date( $length, $period, 'now', $timezone );
	}

	/**
	 * Get relative date in the future.
	 *
	 * @param int    $length   Number of units to go forward.
	 * @param string $period   Period type (e.g., 'days', 'months', 'years').
	 * @param string $timezone Optional. The timezone for the result.
	 *
	 * @return string The calculated future date.
	 * @throws Exception
	 */
	public static function get_future_date( int $length = 0, string $period = 'day', string $timezone = '' ): string {
		return self::add_date( $length, $period, 'now', $timezone );
	}

}