<?php
/**
 * Admin Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides admin-related operations for EDD customers, including URL and link generation.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\Utils\Elements\Element;
use EDD_Customer;

trait Admin {

	/**
	 * Required trait method for getting customer data.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return EDD_Customer|null
	 */
	abstract protected static function get_validated( int $customer_id ): ?EDD_Customer;

	/**
	 * Retrieve the customer admin URL.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return string|null The customer admin URL, null on failure.
	 */
	public static function get_admin_url( int $customer_id = 0 ): ?string {
		if ( empty( $customer_id ) ) {
			return null;
		}

		return edd_get_admin_url( [
			'page' => 'edd-customers',
			'view' => 'overview',
			'id'   => absint( $customer_id )
		] );
	}

	/**
	 * Generate a link to the customer admin logs.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $label       The text for the link.
	 *
	 * @return string|null HTML link to the customer admin logs, or null if not available.
	 */
	public static function get_admin_link( int $customer_id = 0, string $label = '' ): ?string {
		$customer = self::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		if ( empty( $label ) ) {
			$label = ! empty( $customer->name ) ? $customer->name : __( 'No Name', 'arraypress' );
		}

		$url = self::get_admin_url( $customer->id );

		return Element::link( $url, $label );
	}

	/**
	 * Retrieve the customer admin log URL.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $view        The log view.
	 *
	 * @return string|null The customer admin log URL, null on failure.
	 */
	public static function get_admin_logs_url( int $customer_id = 0, string $view = '' ): ?string {
		if ( empty( $customer_id ) ) {
			return null;
		}

		return edd_get_admin_url( [
			'page'     => 'edd-tools',
			'tab'      => 'logs',
			'view'     => $view,
			'customer' => absint( $customer_id )
		] );
	}

	/**
	 * Generate a link to the customer admin logs.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $label       The text for the link.
	 *
	 * @return string|null HTML link to the customer admin logs, or null if not available.
	 */
	public static function get_admin_logs_link( int $customer_id = 0, string $label = 'View Logs' ): ?string {
		$url = self::get_admin_logs_url( $customer_id );
		if ( ! $url || empty( $label ) ) {
			return null;
		}

		return Element::link( $url, $label );
	}


	/**
	 * Get the admin orders URL for a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return string|null The admin orders URL, or null if invalid.
	 */
	public static function get_admin_orders_url( int $customer_id ): ?string {
		if ( empty( $customer_id ) ) {
			return null;
		}

		return edd_get_admin_url( [
			'page'     => 'edd-payment-history',
			'customer' => absint( $customer_id )
		] );
	}

	/**
	 * Generate a link to the customer admin orders page.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $label       The text for the link.
	 *
	 * @return string HTML link to the customer admin orders, or mdash if not available.
	 */
	public static function get_admin_orders_link( int $customer_id, string $label = 'View Orders' ): ?string {
		$url = self::get_admin_orders_url( $customer_id );
		if ( ! $url || empty( $label ) ) {
			return null;
		}

		return Element::link( $url, $label );
	}

}