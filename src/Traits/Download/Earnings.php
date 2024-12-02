<?php
/**
 * Earnings Operations Class for Easy Digital Downloads (EDD)
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
use ArrayPress\EDD\Stats\Downloads;
use ArrayPress\EDD\Common\Generate;
use EDD\Stats;

trait Earnings {

	/**
	 * Meta key for storing earnings stats
	 *
	 * @var string
	 */
	protected static string $earnings_meta_key = 'edd_product_earnings_stats';

	/**
	 * Get earnings for a product.
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return float Total earnings for the product.
	 */
	public static function get_earnings( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		if ( empty( $product_id ) ) {
			return 0.0;
		}

		$stats = new Stats();

		$query_args               = Dates::parse_args( $args, $period );
		$query_args['product_id'] = $product_id;
		$query_args['price_id']   = $price_id;

		return (float) $stats->get_order_item_earnings( $query_args );
	}

	/**
	 * Get average earnings per day.
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return float Average earnings per day.
	 */
	public static function get_average_daily_earnings( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['function'] = 'AVG';

		return self::get_earnings( $product_id, $price_id, $period, $args );
	}

	/**
	 * Get net earnings (includes refunds).
	 *
	 * @param int         $product_id The product ID
	 * @param int|null    $price_id   Optional. The price ID. Default null.
	 * @param string|null $period     Optional. Date range period. Default 'all_time'.
	 * @param array       $args       Optional. Additional query arguments.
	 *
	 * @return float Net earnings amount.
	 */
	public static function get_net_earnings( int $product_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_earnings( $product_id, $price_id, $period, $args );
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
	public static function process_earnings_stats( int $product_id, ?int $price_id = null, array $args = [] ): array {
		if ( empty( $product_id ) ) {
			return [];
		}

		$meta_key = Generate::product_meta_key( self::$earnings_meta_key, $price_id );

		$stats = [];

		// Get all available periods
		foreach ( Dates::get_periods() as $period ) {
			$stats[ $period ] = array(
				'earnings'               => self::get_earnings( $product_id, $price_id, $period, $args ),
				'net_earnings'           => self::get_net_earnings( $product_id, $price_id, $period, $args ),
				'average_daily_earnings' => self::get_average_daily_earnings( $product_id, $price_id, $period, $args ),
				'generated'              => time()
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
	public static function get_cached_earnings_stats( int $product_id, ?int $price_id = null, ?string $period = null, bool $force = false ): array {
		if ( empty( $product_id ) ) {
			return [];
		}

		$meta_key = Generate::product_meta_key( self::$earnings_meta_key, $price_id );

		// Get cached stats
		$stats = get_post_meta( $product_id, $meta_key, true );

		// If no stats exist, force is true, or stats are older than 24 hours, regenerate them
		if ( empty( $stats ) || $force || ! isset( $stats['all_time']['generated'] ) || ( time() - $stats['all_time']['generated'] ) > DAY_IN_SECONDS ) {
			$stats = self::process_earnings_stats( $product_id, $price_id );
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
	public static function clear_cached_earnings_stats( int $product_id, ?int $price_id = null ): bool {
		if ( empty( $product_id ) ) {
			return false;
		}

		$meta_key = Generate::product_meta_key( self::$earnings_meta_key, $price_id );

		return delete_post_meta( $product_id, $meta_key );
	}

	/**
	 * Check if a product is among the highest earning products.
	 *
	 * @param int $download_id Download ID
	 * @param int $limit       Number of top products to check against
	 *
	 * @return bool True if product is a top earner
	 */
	public static function is_top_earner( int $download_id, int $limit = 10 ): bool {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return false;
		}

		$top_earners = Downloads::get_highest_earning( $limit );

		return in_array( $download_id, $top_earners, true );
	}

}