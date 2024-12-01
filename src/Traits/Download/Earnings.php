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
use EDD\Stats;

trait Earnings {

	/**
	 * Get earnings for a product.
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return float Total earnings for the product.
	 */
	public static function get_earnings( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		if ( empty( $download_id ) ) {
			return 0.0;
		}

		$stats = new Stats();

		$query_args                = Dates::parse_args( $args, $period );
		$query_args['download_id'] = $download_id;
		$query_args['price_id']    = $price_id;

		return (float) $stats->get_order_item_earnings( $query_args );
	}

	/**
	 * Get average earnings per day.
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return float Average earnings per day.
	 */
	public static function get_average_daily_earnings( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['function'] = 'AVG';

		return self::get_earnings( $download_id, $price_id, $period, $args );
	}

	/**
	 * Get net earnings (includes refunds).
	 *
	 * @param int         $download_id The product ID
	 * @param int|null    $price_id    Optional. The price ID. Default null.
	 * @param string|null $period      Optional. Date range period. Default 'all_time'.
	 * @param array       $args        Optional. Additional query arguments.
	 *
	 * @return float Net earnings amount.
	 */
	public static function get_net_earnings( int $download_id, ?int $price_id = null, ?string $period = 'all_time', array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_earnings( $download_id, $price_id, $period, $args );
	}

}