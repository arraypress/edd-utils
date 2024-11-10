<?php
/**
 * Admin Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for handling admin URLs, links, and other admin-related functionality.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

use ArrayPress\Utils\Elements\Element;

trait Admin {

	/**
	 * Generates an admin URL for editing a specific order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The admin URL for editing the order if it exists, otherwise null.
	 */
	public static function get_admin_url( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order ) ) {
			return null;
		}

		return edd_get_admin_url( [
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
			'id'   => absint( $order->id ),
		] );
	}

	/**
	 * Retrieve the URL of the purchase history page for the order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The purchase history URL, or null if the order doesn't exist.
	 */
	public static function get_purchase_history_url( int $order_id ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		return add_query_arg( 'payment_key', $order->payment_key, edd_get_success_page_uri() );
	}

	/**
	 * Generates the admin URL for viewing specific logs related to an order.
	 *
	 * @param int    $order_id The ID of the order.
	 * @param string $view     Optional. The specific log view to display.
	 *
	 * @return string|null The URL for the admin logs page for the order, or null if invalid.
	 */
	public static function get_admin_logs_url( int $order_id, string $view = '' ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		$url_args = [
			'page'  => 'edd-tools',
			'tab'   => 'logs',
			'order' => absint( $order_id )
		];

		if ( ! empty( $view ) ) {
			$url_args['view'] = $view;
		}

		return edd_get_admin_url( $url_args );
	}

	/**
	 * Generate a link to the admin logs.
	 *
	 * @param int    $order_id The ID of the order.
	 * @param string $view     The log view.
	 * @param string $label    The text for the link.
	 *
	 * @return string|null HTML link to the admin logs, or null if not available.
	 */
	public static function get_admin_logs_link( int $order_id, string $view = '', string $label = 'View Logs' ): ?string {
		$url = self::get_admin_logs_url( $order_id, $view );
		if ( null === $url || empty( $label ) ) {
			return null;
		}

		return Element::link( $url, $label );
	}

	/**
	 * Retrieve the admin status link for an order with an optional prefix.
	 *
	 * @param int    $order_id The ID of the order.
	 * @param string $prefix   Optional. Prefix to be added before the order number.
	 *
	 * @return string|null HTML anchor tag with the order number and status, or null if invalid.
	 */
	public static function get_admin_status_link( int $order_id, string $prefix = '' ): ?string {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}

		$state = '';
		if ( 'complete' !== $order->status ) {
			$state = ' &mdash; ' . edd_get_payment_status_label( $order->status );
		}

		$url = edd_get_admin_url( [
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
			'id'   => $order->id,
		] );

		$label = sprintf( '%1$s%2$s', $prefix, $order->get_number() . $state );

		return Element::link( $url, $label );
	}

}