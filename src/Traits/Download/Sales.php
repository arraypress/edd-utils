<?php
/**
 * Sales Operations Class for Easy Digital Downloads (EDD)
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use ArrayPress\EDD\Stats\Dates;
use EDD\Stats;

trait Sales {

	/**
	 * Meta key for storing sales stats
	 *
	 * @var string
	 */
	protected static string $earnings_meta_key = 'edd_product_sales_stats';

	/**
	 * Get sales count for a product.
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return int Total number of sales for the product.
	 */
	public static function get_sales( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): int {
		if ( empty( $product_id ) ) {
			return 0;
		}

		$stats = new Stats();

		$query_args               = Dates::parse_args( $args, $period );
		$query_args['product_id'] = $product_id;
		$query_args['price_id']   = $price_id;

		return (int) $stats->get_order_item_count( $query_args );
	}

	/**
	 * Get average daily sales.
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return float Average sales per day.
	 */
	public static function get_average_daily_sales( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['function'] = 'AVG';

		return (float) self::get_sales( $product_id, $price_id, $period, $args );
	}

	/**
	 * Get net sales (includes refunds).
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return int Net number of sales.
	 */
	public static function get_net_sales( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): int {
		$args['revenue_type'] = 'net';

		return self::get_sales( $product_id, $price_id, $period, $args );
	}

	/**
	 * Process and cache all period stats for a product.
	 *
	 * @param int      $product_id The product ID
	 * @param int|null $price_id   Optional. The price ID. Default null.
	 * @param array    $args       Optional. Additional query arguments.
	 *
	 * @return array Array of stats for all periods.
	 */
	public static function process_stats( int $product_id, ?int $price_id = null, array $args = [] ): array {
		if ( empty( $product_id ) ) {
			return [];
		}

		$meta_key = self::$earnings_meta_key;
		if ( ! is_null( $price_id ) ) {
			$meta_key .= '_' . $price_id;
		}

		$stats = array();

		// Get all available periods
		foreach ( Dates::get_periods() as $period ) {
			$stats[ $period ] = array(
				'sales'               => self::get_sales( $product_id, $price_id, $period, $args ),
				'net_sales'           => self::get_net_sales( $product_id, $price_id, $period, $args ),
				'average_daily_sales' => self::get_average_daily_sales( $product_id, $price_id, $period, $args ),
				'generated'           => time()
			);
		}

		// Store the stats in post meta
		update_post_meta( $product_id, $meta_key, $stats );

		return $stats;
	}

	/**
	 * Get cached stats for a product.
	 *
	 * @param int      $product_id The product ID
	 * @param int|null $price_id   Optional. The price ID. Default null.
	 * @param string   $period     Optional. Specific period to retrieve. Default returns all periods.
	 * @param bool     $force      Optional. Force regeneration of stats. Default false.
	 *
	 * @return array Array of cached stats
	 */
	public static function get_cached_stats( int $product_id, ?int $price_id = null, ?string $period = null, bool $force = false ): array {
		if ( empty( $product_id ) ) {
			return [];
		}

		$meta_key = self::$earnings_meta_key;
		if ( ! is_null( $price_id ) ) {
			$meta_key .= '_' . $price_id;
		}

		// Get cached stats
		$stats = get_post_meta( $product_id, $meta_key, true );

		// If no stats exist, force is true, or stats are older than 24 hours, regenerate them
		if ( empty( $stats ) || $force || ! isset( $stats['all_time']['generated'] ) || ( time() - $stats['all_time']['generated'] ) > DAY_IN_SECONDS ) {
			$stats = self::process_stats( $product_id, $price_id );
		}

		// Return specific period if requested
		if ( ! is_null( $period ) && isset( $stats[ $period ] ) ) {
			return $stats[ $period ];
		}

		return $stats;
	}

	/**
	 * Clear cached stats for a product.
	 *
	 * @param int      $product_id The product ID
	 * @param int|null $price_id   Optional. The price ID. Default null.
	 *
	 * @return bool Whether the meta was successfully deleted.
	 */
	public static function clear_cached_stats( int $product_id, ?int $price_id = null ): bool {
		if ( empty( $product_id ) ) {
			return false;
		}

		$meta_key = self::$earnings_meta_key;
		if ( ! is_null( $price_id ) ) {
			$meta_key .= '_' . $price_id;
		}

		return delete_post_meta( $product_id, $meta_key );
	}

}