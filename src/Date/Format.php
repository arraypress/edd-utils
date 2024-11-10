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

class Format {

	/**
	 * Format a date for HTML datetime attribute with optional time element wrapping.
	 *
	 * @param string $date_string The date to format.
	 * @param bool   $wrap        Whether to wrap in HTML time element. Default false.
	 *
	 * @return string Formatted datetime string or HTML time element.
	 * @throws Exception
	 */
	public static function datetime_attr( string $date_string, bool $wrap = false ): string {
		$datetime = esc_attr( EDD()->utils->date( $date_string, null, true )->toDateTimeString() );

		if ( $wrap ) {
			return sprintf( '<time datetime="%s">', $datetime );
		}

		return $datetime;
	}

	/**
	 * Get date format string.
	 *
	 * @param string $format Shorthand date format string.
	 *
	 * @return string Date format string.
	 */
	public static function get_date_format_string( string $format = 'date' ): string {
		return EDD()->utils->get_date_format_string( $format );
	}

	/**
	 * Format a date for display with timezone abbreviation.
	 *
	 * @param string $date_string The date to format.
	 *
	 * @return string Formatted date string.
	 * @throws Exception
	 */
	public static function date_time_localized( string $date_string ): string {
		return edd_date_i18n( $date_string, 'M. d, Y' ) . '<br>' .
		       edd_date_i18n( strtotime( $date_string ), 'H:i' ) . ' ' .
		       Timezone::get_timezone_abbr();
	}

}