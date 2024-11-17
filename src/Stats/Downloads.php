<?php
/**
 * Download Statistics for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Stats
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Stats;

class Downloads {

	/**
	 * Valid criterion types for product analysis
	 *
	 * @var array
	 */
	private static array $criteria = [
		'earnings' => '_edd_download_earnings',
		'sales'    => '_edd_download_sales'
	];

	/**
	 * Get top performing products based on specified criterion.
	 *
	 * @param int    $number    The number of products to retrieve.
	 * @param string $criterion The criterion for comparison ('earnings' or 'sales').
	 *
	 * @return array List of product IDs ordered by the specified criterion.
	 */
	public static function get_top_products( int $number = 10, string $criterion = 'earnings' ): array {
		if ( ! isset( self::$criteria[ $criterion ] ) ) {
			$criterion = 'earnings';
		}

		return self::get_products_by_meta( self::$criteria[ $criterion ], $number, 'DESC' );
	}

	/**
	 * Get the highest earning products.
	 *
	 * @param int $number The number of products to retrieve.
	 *
	 * @return array List of product IDs ordered by earnings.
	 */
	public static function get_highest_earning( int $number = 10 ): array {
		return self::get_products_by_meta( '_edd_download_earnings', $number, 'DESC' );
	}

	/**
	 * Get the lowest earning products.
	 *
	 * @param int $number The number of products to retrieve.
	 *
	 * @return array List of product IDs ordered by earnings.
	 */
	public static function get_lowest_earning( int $number = 10 ): array {
		return self::get_products_by_meta( '_edd_download_earnings', $number, 'ASC' );
	}

	/**
	 * Get the highest selling products.
	 *
	 * @param int $number The number of products to retrieve.
	 *
	 * @return array List of product IDs ordered by sales.
	 */
	public static function get_highest_selling( int $number = 10 ): array {
		return self::get_products_by_meta( '_edd_download_sales', $number, 'DESC' );
	}

	/**
	 * Get the lowest selling products.
	 *
	 * @param int $number The number of products to retrieve.
	 *
	 * @return array List of product IDs ordered by sales.
	 */
	public static function get_lowest_selling( int $number = 10 ): array {
		return self::get_products_by_meta( '_edd_download_sales', $number, 'ASC' );
	}

	/**
	 * Get products sorted by a specific meta key.
	 *
	 * @param string $meta_key The meta key to sort by.
	 * @param int    $number   The number of products to retrieve.
	 * @param string $order    The sort order ('ASC' or 'DESC').
	 *
	 * @return array List of product IDs.
	 */
	private static function get_products_by_meta( string $meta_key, int $number, string $order ): array {
		return get_posts( [
			'post_type'      => 'download',
			'posts_per_page' => $number,
			'meta_query'     => [
				[
					'key'     => $meta_key,
					'value'   => '',
					'compare' => '!=',
				],
			],
			'fields'         => 'ids',
			'orderby'        => 'meta_value_num',
			'order'          => $order
		] );
	}

}