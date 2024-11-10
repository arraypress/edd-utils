<?php
/**
 * License Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for handling EDD Software Licensing operations.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Licensing {

	/**
	 * Get the count of licenses for an order.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $args     Optional. Additional arguments for the license query.
	 *
	 * @return int The number of licenses for the order.
	 */
	public static function get_license_count( int $order_id, array $args = [] ): int {
		if ( empty( $order_id ) || ! class_exists( 'EDD_Software_Licensing' ) ) {
			return 0;
		}

		$default_args = [
			'payment_id' => $order_id,
			'number'     => - 1, // Get all licenses
		];

		$query_args = wp_parse_args( $args, $default_args );

		return edd_software_licensing()->licenses_db->count( $query_args );
	}

	/**
	 * Check if the order has any licenses.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order has any licenses, false otherwise.
	 */
	public static function has_license( int $order_id ): bool {
		return (bool) self::get_license_count( $order_id, [ 'number' => 1 ] ) > 0;
	}

	/**
	 * Retrieve Easy Digital Downloads (EDD) Software Licensing licenses based on various criteria.
	 *
	 * @param int   $order_id       The order ID associated with the licenses.
	 * @param mixed $cart_index     The cart index to filter licenses by. Use null for no cart index filter.
	 * @param int   $download_id    The ID of the download for which licenses are to be retrieved.
	 * @param bool  $allow_children Indicates whether to return child licenses if found on a payment containing a
	 *                              bundle.
	 *
	 * @return array|false Returns an array of EDD_SL_License objects if licenses are found,
	 *                     or false if no licenses are found or if Software Licensing is unavailable.
	 */
	public static function get_licenses_by( int $order_id, $cart_index = null, int $download_id = 0, bool $allow_children = true ) {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$args = [
			'number'     => - 1,
			'payment_id' => $order_id,
		];

		if ( null !== $cart_index ) {
			$args['cart_index'] = $cart_index;
		}

		if ( ! empty( $download_id ) ) {
			$args['download_id'] = $download_id;
		}

		if ( false === $allow_children ) {
			$args['parent'] = 0;
		}

		return edd_software_licensing()->licenses_db->get_licenses( $args );
	}

	/**
	 * Check if the order is a software licensing upgrade.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is a software licensing upgrade, false otherwise.
	 */
	public static function is_license_upgrade( int $order_id ): bool {
		return (bool) edd_get_order_meta( $order_id, '_edd_sl_upgraded_payment_id', true );
	}

	/**
	 * Check if the order is a software licensing renewal.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is a software licensing renewal, false otherwise.
	 */
	public static function is_license_renewal( int $order_id ): bool {
		return (bool) edd_get_order_meta( $order_id, '_edd_sl_is_renewal', true );
	}

	/**
	 * Check if the order contains any license products.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order contains license products, false otherwise.
	 */
	public static function has_license_products( int $order_id ): bool {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 1
		] );

		if ( empty( $order_items ) ) {
			return false;
		}

		foreach ( $order_items as $item ) {
			$download = new \EDD_SL_Download( $item->product_id );
			if ( $download->licensing_enabled() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the number of license activations allowed for an order item.
	 *
	 * @param int      $order_id The ID of the order.
	 * @param int      $item_id  The ID of the order item.
	 * @param int|null $price_id Optional. The price ID if variable pricing is enabled.
	 *
	 * @return int The number of allowed activations, or 0 if licensing is not enabled.
	 */
	public static function get_license_activation_limit(
		int $order_id,
		int $item_id,
		?int $price_id = null
	): int {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return 0;
		}

		$download = new \EDD_SL_Download( $item_id );

		if ( ! $download->licensing_enabled() ) {
			return 0;
		}

		if ( $download->has_variable_prices() && ! is_null( $price_id ) ) {
			return $download->get_price_activation_limit( $price_id );
		}

		return $download->get_activation_limit();
	}

	/**
	 * Check the download completion status of software licenses in the order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null 'full' if all licenses downloaded, 'partial' if some downloaded,
	 *                     null if no downloads or invalid order.
	 */
	public static function get_license_download_completion_status( int $order_id ): ?string {
		if ( empty( $order_id ) || ! function_exists( 'edd_software_licensing' ) ) {
			return null;
		}

		$licenses = self::get_licenses_by( $order_id );
		if ( empty( $licenses ) ) {
			return null;
		}

		$total_downloads     = 0;
		$completed_downloads = 0;

		foreach ( $licenses as $license ) {
			$download_count = $license->get_download_count();
			if ( $download_count > 0 ) {
				$completed_downloads ++;
			}
			$total_downloads ++;
		}

		if ( $completed_downloads === 0 ) {
			return null;
		}

		return ( $completed_downloads >= $total_downloads ) ? 'full' : 'partial';
	}
}