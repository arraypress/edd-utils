<?php
/**
 * Downloadable Files Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for handling downloadable items and their properties.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Files {

	/**
	 * Get all downloadable files for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return array An array of downloadable files.
	 */
	public static function get_downloadable_files( int $order_id ): array {
		$order = edd_get_order( $order_id );
		if ( empty( $order ) ) {
			return [];
		}

		$files = [];

		if ( $order->get_items() ) {
			foreach ( $order->get_items() as $item ) {
				$item_files = edd_get_download_files( $item->product_id, $item->price_id );

				if ( ! empty( $item_files ) ) {
					$files = array_merge( $files, $item_files );
				} elseif ( edd_is_bundled_product( $item->product_id ) ) {
					$bundled_products = edd_get_bundled_products( $item->product_id );

					foreach ( $bundled_products as $bundle_item ) {
						$bundle_files = edd_get_download_files( $bundle_item );

						if ( ! empty( $bundle_files ) ) {
							$files = array_merge( $files, $bundle_files );
						}
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Get the count of downloadable files in the order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return int The number of downloadable files in the order.
	 */
	public static function get_downloadable_file_count( int $order_id ): int {
		return count( self::get_downloadable_files( $order_id ) );
	}

	/**
	 * Check the download status of an order.
	 *
	 * @param int   $order_id    The ID of the order.
	 *
	 * @return array {
	 *     Download status information
	 *
	 * @type string $status      Either 'none', 'partial', or 'complete'
	 * @type int    $total_files Total number of downloadable files
	 * @type int    $downloaded  Number of files that have been downloaded
	 * @type array  $file_status Array of individual file download status
	 *                           }
	 */
	public static function get_download_status( int $order_id ): array {
		// Get all downloadable files for the order
		$files = self::get_downloadable_files( $order_id );

		if ( empty( $files ) ) {
			return [
				'status'      => 'none',
				'total_files' => 0,
				'downloaded'  => 0,
				'file_status' => []
			];
		}

		// Get all download logs for this order
		$download_logs = edd_get_file_download_logs( [
			'order_id' => $order_id,
			'number'   => - 1 // Get all logs
		] );

		// Create a map of downloaded file IDs
		$downloaded_files = array_reduce( $download_logs, function ( $carry, $log ) {
			$carry[ $log->file_id ] = true;

			return $carry;
		}, [] );

		// Track download status for each file
		$file_status = array_map( function ( $file ) use ( $downloaded_files ) {
			$file_id = $file['id'] ?? null;

			return [
				'file_id'    => $file_id,
				'name'       => $file['name'] ?? 'Unknown',
				'downloaded' => isset( $downloaded_files[ $file_id ] )
			];
		}, $files );

		// Calculate statistics
		$total_files      = count( $files );
		$downloaded_count = count( array_filter( $file_status, function ( $file ) {
			return $file['downloaded'];
		} ) );

		// Determine overall status
		$status = 'none';
		if ( $downloaded_count > 0 ) {
			$status = ( $downloaded_count === $total_files ) ? 'complete' : 'partial';
		}

		return [
			'status'      => $status,
			'total_files' => $total_files,
			'downloaded'  => $downloaded_count,
			'file_status' => $file_status
		];
	}

	/**
	 * Check if an order has been fully downloaded.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if all files have been downloaded, false otherwise.
	 */
	public static function is_fully_downloaded( int $order_id ): bool {
		$status = self::get_download_status( $order_id );

		return $status['status'] === 'complete';
	}

	/**
	 * Check if an order has been partially downloaded.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if some but not all files have been downloaded, false otherwise.
	 */
	public static function is_partially_downloaded( int $order_id ): bool {
		$status = self::get_download_status( $order_id );

		return $status['status'] === 'partial';
	}

}