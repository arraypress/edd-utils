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

use ArrayPress\EDD\Stats\Dates;


class Commissions {

	/**
	 * Get total commission earnings.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 *
	 * @return float Total commission earnings.
	 */
	public static function get_earnings( array $args = [], ?string $period = 'all_time' ): float {
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
	public static function get_count( array $args = [], ?string $period = 'all_time' ): int {
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
	public static function get_average( array $args = [], ?string $period = 'all_time' ): float {
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
	public static function get_vendor_average( array $args = [], ?string $period = 'all_time' ): float {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return 0.0;
		}

		$query_args = self::parse_args( $args, $period );

		return (float) edd_commissions()->commissions_db->avg( 'amount', $query_args, 'user_id' );
	}

	/**
	 * Get top earning users by commission amounts.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of users to return. Default 10.
	 *
	 * @return array Array of user IDs and their earnings.
	 */
	public static function get_top_earners( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return [];
		}

		$query_args = self::parse_args( $args, $period );

		// Get sum grouped by user_id
		$results = edd_commissions()->commissions_db->sum_group(
			'amount',
			$query_args
		);

		// Sort by earnings (second column in results)
		usort( $results, function ( $a, $b ) {
			return $b[0] <=> $a[0];
		} );

		// Limit and format results
		$top_earners = [];
		foreach ( array_slice( $results, 0, $limit ) as $result ) {
			$top_earners[] = [
				'user_id'  => (int) $result[1],
				'earnings' => (float) $result[0]
			];
		}

		return $top_earners;
	}

	/**
	 * Get user IDs of top earners.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of users to return. Default 10.
	 *
	 * @return array Array of user IDs.
	 */
	public static function get_top_earner_ids( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		return array_column( self::get_top_earners( $args, $period, $limit ), 'user_id' );
	}

	/**
	 * Get top selling users by commission count.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of users to return. Default 10.
	 *
	 * @return array Array of user IDs and their sales count.
	 */
	public static function get_top_sellers( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return [];
		}

		$query_args = self::parse_args( $args, $period );

		// Get count grouped by user_id
		$results = edd_commissions()->commissions_db->count_group(
			$query_args
		);

		// Sort by count (first column in results)
		usort( $results, function ( $a, $b ) {
			return $b[0] <=> $a[0];
		} );

		// Limit and format results
		$top_sellers = [];
		foreach ( array_slice( $results, 0, $limit ) as $result ) {
			$top_sellers[] = [
				'user_id' => (int) $result[1],
				'count'   => (int) $result[0]
			];
		}

		return $top_sellers;
	}

	/**
	 * Get user IDs of top sellers.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of users to return. Default 10.
	 *
	 * @return array Array of user IDs.
	 */
	public static function get_top_seller_ids( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		return array_column( self::get_top_sellers( $args, $period, $limit ), 'user_id' );
	}

	/**
	 * Get top products by commission amounts.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of products to return. Default 10.
	 *
	 * @return array Array of product IDs, titles and their commission amounts.
	 */
	public static function get_top_products( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return [];
		}

		$query_args = self::parse_args( $args, $period );

		// Get sum grouped by download_id
		$results = edd_commissions()->commissions_db->sum_group(
			'amount',
			$query_args,
			'download_id',
			'download_id'
		);

		// Sort by commission amounts
		usort( $results, function ( $a, $b ) {
			return $b[0] <=> $a[0];
		} );

		// Limit and format results
		$top_products = [];
		foreach ( array_slice( $results, 0, $limit ) as $result ) {
			$product_id = (int) $result[1];
			$download   = edd_get_download( $product_id );

			$top_products[] = [
				'product_id' => $product_id,
				'title'      => $download ? $download->get_name() : '',
				'amount'     => (float) $result[0]
			];
		}

		return $top_products;
	}

	/**
	 * Get product IDs with highest commission amounts.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of products to return. Default 10.
	 *
	 * @return array Array of product IDs.
	 */
	public static function get_top_product_ids( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		return array_column( self::get_top_products( $args, $period, $limit ), 'product_id' );
	}

	/**
	 * Get top products by commission count.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of products to return. Default 10.
	 *
	 * @return array Array of product IDs, titles and their commission counts.
	 */
	public static function get_top_selling_products( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		if ( ! function_exists( 'edd_commissions' ) ) {
			return [];
		}

		$query_args = self::parse_args( $args, $period );

		// Get count grouped by download_id
		$results = edd_commissions()->commissions_db->count_group(
			$query_args,
			'download_id',
			'download_id'
		);

		// Sort by count
		usort( $results, function ( $a, $b ) {
			return $b[0] <=> $a[0];
		} );

		// Limit and format results
		$top_selling = [];
		foreach ( array_slice( $results, 0, $limit ) as $result ) {
			$product_id = (int) $result[1];
			$download   = edd_get_download( $product_id );

			$top_selling[] = [
				'product_id' => $product_id,
				'title'      => $download ? $download->get_name() : '',
				'count'      => (int) $result[0]
			];
		}

		return $top_selling;
	}

	/**
	 * Get product IDs with highest commission count.
	 *
	 * @param array       $args   Optional. Query arguments.
	 * @param string|null $period Optional. Date range period.
	 * @param int         $limit  Optional. Number of products to return. Default 10.
	 *
	 * @return array Array of product IDs.
	 */
	public static function get_top_selling_product_ids( array $args = [], ?string $period = 'all_time', int $limit = 10 ): array {
		return array_column( self::get_top_selling_products( $args, $period, $limit ), 'product_id' );
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
		if ( ! Dates::is_valid_period( $period ) ) {
			return $args;
		}

		$dates = Dates::get_dates( $period );
		if ( ! $dates ) {
			return $args;
		}

		$date_args = array(
			'date' => array(
				'start' => $dates['start'],
				'end'   => $dates['end'],
			)
		);

		return wp_parse_args( $args, $date_args );
	}

}