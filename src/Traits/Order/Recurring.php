<?php
/**
 * Subscription Operations Trait for Easy Digital Downloads (EDD) Downloads
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Recurring {

	/**
	 * Check if the order is recurring.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is recurring, false otherwise.
	 */
	public static function is_recurring( int $order_id ): bool {
		return self::is_subscription( $order_id ) || self::is_renewal( $order_id );
	}

	/**
	 * Check if the order is a subscription.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is a subscription, false otherwise.
	 */
	public static function is_subscription( int $order_id ): bool {
		return (bool) edd_get_order_meta( $order_id, '_edd_subscription_payment', true );
	}

	/**
	 * Check if the order is a renewal.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if the order is a renewal, false otherwise.
	 */
	public static function is_renewal( int $order_id ): bool {
		if ( empty( $order_id ) ) {
			return false;
		}

		$order = edd_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		return ! empty( edd_get_order_meta( $order_id, '_edd_sl_is_renewal', true ) ) ||
		       ! empty( edd_get_order_meta( $order_id, 'subscription_id', true ) );
	}

	/**
	 * Get subscriptions associated with an order.
	 *
	 * @param int        $order_id   The ID of the order.
	 * @param array|null $query_args Optional. Additional query arguments.
	 *
	 * @return array Array of subscription objects.
	 */
	protected static function get_subscriptions( int $order_id, ?array $query_args = null ): array {
		if ( ! function_exists( 'EDD_Recurring' ) || empty( $order_id ) ) {
			return [];
		}

		$order = edd_get_order( $order_id );
		if ( empty( $order ) ) {
			return [];
		}

		$args = wp_parse_args( $query_args ?? [], [
			'order'  => 'ASC',
			'number' => - 1
		] );

		$subs_db = new \EDD_Subscriptions_DB();

		// Handle parent order subscriptions
		if ( edd_get_order_meta( $order->id, '_edd_subscription_payment', true ) ) {
			return $subs_db->get_subscriptions( array_merge( [ 'parent_payment_id' => $order->id ], $args ) );
		}

		// Handle child order subscriptions
		if ( ! empty( $order->parent ) ) {
			return $subs_db->get_subscriptions( array_merge( [ 'parent_payment_id' => $order->parent ], $args ) );
		}

		// Handle single subscription
		$subscription_id = edd_get_order_meta( $order->id, 'subscription_id', true );
		if ( ! empty( $subscription_id ) ) {
			$subscription = new \EDD_Subscription( $subscription_id );

			return $subscription ? [ $subscription ] : [];
		}

		return [];
	}

	/**
	 * Get the subscription ID associated with an order.
	 *
	 * @param int      $order_id   The ID of the order.
	 * @param int      $product_id Optional. The ID of the product.
	 * @param int|null $price_id   Optional. The ID of the price.
	 *
	 * @return int|null The subscription ID or null if not found.
	 */
	public static function get_subscription_id( int $order_id, int $product_id = 0, ?int $price_id = null ): ?int {
		if ( ! function_exists( 'EDD_Recurring' ) || empty( $order_id ) ) {
			return null;
		}

		$args = [ 'order' => 'ASC' ];

		if ( ! empty( $product_id ) ) {
			$args['product_id'] = absint( $product_id );
		}

		if ( $price_id !== null ) {
			$args['price_id'] = absint( $price_id );
		}

		$subscriptions = self::get_subscriptions( $order_id, $args );
		$subscription  = reset( $subscriptions );

		return $subscription ? (int) $subscription->id : null;
	}

	/**
	 * Get subscription expiration for a specific product in an order.
	 *
	 * @param int      $order_id   The ID of the order.
	 * @param int      $product_id The ID of the product.
	 * @param int|null $price_id   Optional. The ID of the price.
	 *
	 * @return string|null The subscription expiration date or null if not found.
	 */
	public static function get_subscription_expiration( int $order_id, int $product_id, ?int $price_id = null ): ?string {
		if ( ! function_exists( 'EDD_Recurring' ) || empty( $order_id ) ) {
			return null;
		}

		$subscription_id = self::get_subscription_id( $order_id, $product_id, $price_id );
		if ( empty( $subscription_id ) ) {
			return null;
		}

		$subscription = new \EDD_Subscription( $subscription_id );

		return $subscription ? $subscription->expiration : null;
	}

	/**
	 * Get signup fee adjustment for a subscription order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return object|null The signup fee adjustment object or null if not found.
	 */
	public static function get_signup_fee( int $order_id ): ?object {
		if ( empty( $order_id ) ) {
			return null;
		}

		$order_adjustments = edd_get_order_adjustments( [
			'number'      => 1,
			'object_id'   => $order_id,
			'object_type' => 'order',
			'type'        => 'fee',
			'type_key'    => 'signup_fee'
		] );

		return ! empty( $order_adjustments ) ? current( $order_adjustments ) : null;
	}

	/**
	 * Get all subscription products from an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return array|null Array of subscription order items or null if none found.
	 */
	public static function get_subscription_products( int $order_id ): ?array {
		if ( ! function_exists( 'EDD_Recurring' ) ) {
			return null;
		}

		$order_items = edd_get_order_items( [
			'order_id'   => $order_id,
			'status__in' => edd_get_deliverable_order_item_statuses(),
			'number'     => 999999,
		] );

		if ( empty( $order_items ) ) {
			return null;
		}

		$subscription_items = array_filter( $order_items, function ( $item ) {
			return EDD_Recurring()->is_recurring( $item->product_id );
		} );

		return ! empty( $subscription_items ) ? array_values( $subscription_items ) : null;
	}

	/**
	 * Check if the order has any subscription products.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order has subscription products, false otherwise.
	 */
	public static function has_subscription_products( int $order_id ): bool {
		$subscription_products = self::get_subscription_products( $order_id );

		return ! empty( $subscription_products );
	}


}