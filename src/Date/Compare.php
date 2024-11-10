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

use Exception;

class Compare {

	/**
	 * Compare two dates.
	 *
	 * @param string $date1    The first date.
	 * @param string $date2    The second date (or 'now' if not provided).
	 * @param string $operator The comparison operator ('>', '<', '>=', '<=', '==', '!=').
	 * @param string $timezone The timezone to use for comparison.
	 *
	 * @return bool The result of the comparison.
	 * @throws Exception
	 */
	public static function dates( string $date1, string $date2 = 'now', string $operator = '==', string $timezone = 'UTC' ): bool {
		$timezone   = $timezone ?: Timezone::get_id();
		$date_time1 = Common::parse_date( $date1, $timezone );
		$date_time2 = Common::parse_date( $date2, $timezone );

		switch ( $operator ) {
			case '<':
				return $date_time1->lt( $date_time2 );
			case '<=':
				return $date_time1->lte( $date_time2 );
			case '>':
				return $date_time1->gt( $date_time2 );
			case '>=':
				return $date_time1->gte( $date_time2 );
			case '==':
			case '=':
				return $date_time1->eq( $date_time2 );
			case '!=':
				return $date_time1->ne( $date_time2 );
			default:
				return false;
		}
	}

	/**
	 * Check if a date is within the EDD store's business hours.
	 *
	 * @param string   $date         The date to check. Default is 'now'.
	 * @param string   $timezone     The timezone to use. Default is empty string (uses store's timezone).
	 * @param int|null $opening_time Optional. Opening time in 24-hour format (0-23). Default is null (uses store
	 *                               setting).
	 * @param int|null $closing_time Optional. Closing time in 24-hour format (0-23). Default is null (uses store
	 *                               setting).
	 *
	 * @return bool True if within business hours, false otherwise.
	 * @throws Exception If the date is invalid or if opening/closing times are invalid.
	 */
	public static function is_within_business_hours( string $date = 'now', string $timezone = 'UTC', ?int $opening_time = null, ?int $closing_time = null ): bool {
		$timezone = $timezone ?: Timezone::get_id();
		$date     = Common::parse_date( $date, $timezone );

		// If opening and closing times are not provided, use default or stored values
		if ( $opening_time === null || $closing_time === null ) {
			$opening_time = $opening_time ?? 9; // Default 9 AM if not provided
			$closing_time = $closing_time ?? 17; // Default 5 PM if not provided
		}

		// Validate opening and closing times
		if ( $opening_time < 0 || $opening_time > 23 || $closing_time < 0 || $closing_time > 23 ) {
			throw new Exception( 'Invalid opening or closing time. Must be between 0 and 23.' );
		}

		$hour = (int) $date->format( 'G' );

		// Handle cases where closing time is on the next day (e.g., 22:00 to 06:00)
		if ( $closing_time <= $opening_time ) {
			return $hour >= $opening_time || $hour < $closing_time;
		}

		return $hour >= $opening_time && $hour < $closing_time;
	}

}