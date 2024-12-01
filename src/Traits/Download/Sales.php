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
	 * Get sales count for a product.
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return int Total number of sales for the product.
	 */
	public static function get_sales( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): int {
		if ( empty( $download_id ) ) {
			return 0;
		}

		$stats = new Stats();

		$query_args                = Dates::parse_args( $args, $period );
		$query_args['download_id'] = $download_id;
		$query_args['price_id']    = $price_id;

		return (int) $stats->get_order_item_count( $query_args );
	}

	/**
	 * Get average daily sales.
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return float Average sales per day.
	 */
	public static function get_average_daily_sales( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['function'] = 'AVG';

		return (float) self::get_sales( $download_id, $price_id, $period, $args );
	}

	/**
	 * Get net sales (includes refunds).
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return int Net number of sales.
	 */
	public static function get_net_sales( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): int {
		$args['revenue_type'] = 'net';

		return self::get_sales( $download_id, $price_id, $period, $args );
	}

}