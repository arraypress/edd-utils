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

class Timezone {

	/**
	 * Get the timezone ID used by EDD.
	 *
	 * @return string Timezone ID.
	 */
	public static function get_id(): string {
		return EDD()->utils->get_time_zone( true );
	}

	/**
	 * Get the timezone abbreviation used by EDD.
	 *
	 * @return string Timezone abbreviation.
	 * @throws Exception
	 */
	public static function get_timezone_abbr(): string {
		$edd_timezone = self::get_id();
		$date_object  = Common::parse_date( 'now', $edd_timezone, true );

		return $date_object->format( 'T' );
	}

}