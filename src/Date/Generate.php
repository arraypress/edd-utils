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

class Generate {

	/**
	 * Create a date range query array for EDD queries.
	 *
	 * @param string $start          Optional. The start date for the range filter in 'Y-m-d' format.
	 * @param string $end            Optional. The end date for the range filter in 'Y-m-d' format.
	 * @param bool   $convert_to_utc Optional. Whether to convert dates to UTC. Defaults to true.
	 * @param string $column         Optional. The column to apply the date range to. Default 'date_created'.
	 *
	 * @return array The date range query array.
	 * @throws Exception
	 */
	public static function date_range_query( string $start = '', string $end = '', bool $convert_to_utc = true, string $column = 'date_created' ): array {
		$query = [];

		if ( ! empty( $start ) || ! empty( $end ) ) {
			$query = [
				'relation' => 'AND',
				'column'   => $column,
			];

			if ( ! empty( $start ) ) {
				$query[] = [
					'after'     => $convert_to_utc ? Common::to_utc( $start, 'mysql' ) : $start,
					'inclusive' => true,
				];
			}

			if ( ! empty( $end ) ) {
				$end_with_time = Common::get_current_time( 'Y-m-d 23:59:59' );
				if ( $end !== 'now' ) {
					$end_with_time = EDD()->utils->get_date_string( $end, 23, 59 );
				}
				$query[] = [
					'before'    => $convert_to_utc ? Common::to_utc( $end_with_time, 'mysql' ) : $end_with_time,
					'inclusive' => true,
				];
			}
		}

		return $query;
	}

	/**
	 * Generate start and end date parameters for queries.
	 *
	 * @param string|null $start_date The start date (optional).
	 * @param string|null $end_date   The end date (optional).
	 *
	 * @return array An array with 'start' and 'end' keys, or an empty array if both dates are null.
	 * @throws Exception
	 */
	public static function date_params( ?string $start_date = null, ?string $end_date = null ): array {
		$params = [];

		if ( $start_date !== null ) {
			$params['start'] = Common::to_utc( $start_date );
		}

		if ( $end_date !== null ) {
			$params['end'] = Common::to_utc( $end_date );
		}

		return $params;
	}

	/**
	 * Get date components for a given UTC date string.
	 *
	 * @param string $date_string The UTC date string to process.
	 * @param bool   $detailed    Whether to return detailed components. Default is false.
	 *
	 * @return array An array of date components.
	 * @throws Exception
	 */
	public static function components( string $date_string, bool $detailed = false ): array {
		$date_object = edd_get_edd_timezone_equivalent_date_from_utc( Common::parse_date( $date_string, 'utc' ) );

		if ( ! $detailed ) {
			return [
				'ymd'    => $date_object->format( 'Y-m-d' ),
				'hour'   => $date_object->format( 'H' ),
				'minute' => $date_object->format( 'i' )
			];
		}

		return [
			'ymd'       => $date_object->format( 'Y-m-d' ),
			'year'      => $date_object->format( 'Y' ),
			'month'     => $date_object->format( 'm' ),
			'day'       => $date_object->format( 'd' ),
			'hour'      => $date_object->format( 'H' ),
			'minute'    => $date_object->format( 'i' ),
			'second'    => $date_object->format( 's' ),
			'timestamp' => $date_object->getTimestamp(),
			'timezone'  => $date_object->getTimezone()->getName()
		];
	}

}