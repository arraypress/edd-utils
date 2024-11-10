<?php
/**
 * AffiliateWP Order Operations for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling AffiliateWP referrals and earnings for EDD orders.
 *
 * @package       ArrayPress\EDD\Orders
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

trait Referral {

	/**
	 * Check if an order contains any AffiliateWP referrals.
	 *
	 * @param int $order_id The ID of the order to check.
	 *
	 * @return bool True if there are referrals associated with the order.
	 */
	public static function is_referral( int $order_id ): bool {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return false;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );

		return ! is_wp_error( $referral );
	}

	/**
	 * Get referral earnings for an order.
	 *
	 * @param int  $order_id  The ID of the order.
	 * @param bool $formatted Whether to return formatted amount.
	 *
	 * @return float|string|null The referral earnings or null if no referrals found.
	 */
	public static function get_referral_earnings( int $order_id, bool $formatted = false ) {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );
		if ( is_wp_error( $referral ) ) {
			return null;
		}

		$amount = floatval( $referral->amount );

		if ( $formatted ) {
			return affwp_currency_filter( affwp_format_amount( $amount ) );
		}

		return $amount;
	}

	/**
	 * Get the affiliate ID associated with an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return int|null The affiliate ID or null if no referral found.
	 */
	public static function get_affiliate_id( int $order_id ): ?int {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );

		return ! is_wp_error( $referral ) ? (int) $referral->affiliate_id : null;
	}

	/**
	 * Get the referral status for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The referral status or null if no referral found.
	 */
	public static function get_referral_status( int $order_id ): ?string {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );

		return ! is_wp_error( $referral ) ? affwp_get_referral_status( $referral ) : null;
	}

	/**
	 * Get the affiliate's visit ID that led to the order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return int|null The visit ID or null if not found.
	 */
	public static function get_visit_id( int $order_id ): ?int {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );

		return ! is_wp_error( $referral ) ? (int) $referral->visit_id : null;
	}

	/**
	 * Get the referral description for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The referral description or null if not found.
	 */
	public static function get_referral_description( int $order_id ): ?string {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$referral = affwp_get_referral_by( 'reference', (string) $order_id, 'edd' );

		return ! is_wp_error( $referral ) ? $referral->description : null;
	}

	/**
	 * Get affiliate's username for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The affiliate's username or null if not found.
	 */
	public static function get_affiliate_username( int $order_id ): ?string {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$affiliate_id = self::get_affiliate_id( $order_id );
		if ( ! $affiliate_id ) {
			return null;
		}

		$user_id = affwp_get_affiliate_user_id( $affiliate_id );
		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return $user ? $user->user_login : null;
	}

	/**
	 * Get affiliate's email address for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return string|null The affiliate's email address or null if not found.
	 */
	public static function get_affiliate_email( int $order_id ): ?string {
		if ( empty( $order_id ) || ! function_exists( 'affiliate_wp' ) ) {
			return null;
		}

		$affiliate_id = self::get_affiliate_id( $order_id );
		if ( ! $affiliate_id ) {
			return null;
		}

		$user_id = affwp_get_affiliate_user_id( $affiliate_id );
		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return $user ? $user->user_email : null;
	}

}