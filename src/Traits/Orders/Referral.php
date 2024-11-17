<?php
/**
 * AffiliateWP Order Operations for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling AffiliateWP referrals and earnings for EDD orders.
 *
 * @package       ArrayPress\EDD\Downloads
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

use ArrayPress\EDD\Orders\Order;

trait Referral {

	/**
	 * Get total referral earnings for multiple orders.
	 *
	 * @param array $order_ids Array of order IDs.
	 * @param bool  $formatted Whether to return formatted amount.
	 *
	 * @return float|string Total referral earnings.
	 */
	public static function get_total_referral_earnings( array $order_ids, bool $formatted = false ) {
		if ( empty( $order_ids ) || ! function_exists( 'affiliate_wp' ) ) {
			return $formatted ? affwp_currency_filter( affwp_format_amount( 0 ) ) : 0.00;
		}

		$total = 0.00;
		foreach ( $order_ids as $order_id ) {
			$earnings = Order::get_referral_earnings( $order_id );
			if ( $earnings !== null ) {
				$total += $earnings;
			}
		}

		if ( $formatted ) {
			return affwp_currency_filter( affwp_format_amount( $total ) );
		}

		return $total;
	}

}