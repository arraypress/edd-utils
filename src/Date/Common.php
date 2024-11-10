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
use EDD\Utils\Date;
use Exception;

class Common {

	/**
	 * @var string The default date format used by this class.
	 */
	const DEFAULT_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Get the current time, either in the site's timezone or UTC.
	 *
	 * @param string $type    Optional. Type of time to retrieve. Accepts 'mysql',
	 *                        'timestamp', or PHP date format string. Default 'mysql'.
	 * @param bool   $gmt     Optional. Whether to use GMT timezone. Default false.
	 * @param bool   $edd_tz  Optional. Whether to use EDD's timezone setting. Default true.
	 *
	 * @return int|string Integer if $type is 'timestamp', string otherwise.
	 * @throws Exception
	 */
	public static function get_current_time( string $type = 'mysql', bool $gmt = false, bool $edd_tz = true ) {
		$timezone = $gmt ? 'UTC' : ( $edd_tz ? Timezone::get_id() : wp_timezone_string() );
		$localize = ! $gmt;

		$datetime = self::parse_date( 'now', $timezone, $localize );

		switch ( $type ) {
			case 'timestamp':
				return $datetime->getTimestamp();
			case 'mysql':
				return $datetime->format( self::DEFAULT_FORMAT );
			default:
				return $datetime->format( $type );
		}
	}

	/**
	 * Get the current UTC datetime.
	 *
	 * @param string $format The format to return the date in.
	 *
	 * @return string The current UTC datetime.
	 * @throws Exception
	 */
	public static function now( string $format = self::DEFAULT_FORMAT ): string {
		return EDD()->utils->date()->format( $format );
	}

	/**
	 * Get the current date and time as a formatted string.
	 *
	 * @return string Current date and time string.
	 * @throws Exception
	 */
	public static function get_current_datetime_string(): string {
		return self::parse_date( 'now', null, true )->toDateTimeString();
	}

	/**
	 * Parse a date string into an EDD\Utils\Date object.
	 *
	 * @param string      $date_string Date string to parse.
	 * @param string|null $timezone    Timezone for the parsed date.
	 * @param bool        $localize    Whether to apply the offset in seconds to the generated date.
	 *
	 * @return Date
	 * @throws Exception
	 */
	public static function parse_date( string $date_string = 'now', ?string $timezone = null, bool $localize = false ): Date {
		if ( null === $timezone ) {
			$timezone = $localize ? Timezone::get_id() : 'UTC';
		}

		return EDD()->utils->date( $date_string, $timezone, $localize );
	}

	/**
	 * Convert a date to UTC.
	 *
	 * @param string $date_string The date string to convert.
	 * @param string $format      The format to return the date in.
	 *
	 * @return string UTC date string.
	 * @throws Exception
	 */
	public static function to_utc( string $date_string = 'now', string $format = self::DEFAULT_FORMAT ): string {
		return EDD()->utils->date( $date_string, Timezone::get_id(), true )->get_utc_from_local( $format );
	}

	/**
	 * Convert a UTC date to the local timezone.
	 *
	 * @param string $date_string The UTC date string to convert.
	 * @param string $format      The format to return the date in.
	 *
	 * @return string Local timezone date string.
	 * @throws Exception
	 */
	public static function from_utc( string $date_string, string $format = self::DEFAULT_FORMAT ): string {
		return EDD()->utils->date( $date_string, 'UTC', true )
		                   ->setTimezone( Timezone::get_id() )
		                   ->format( $format );
	}

}