<?php
/**
 * Recurring (Subscription) Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides subscription-related functionality for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use EDD_Download;

trait Recurring {

	/**
	 * Required trait method for getting validated download.
	 *
	 * @param int $download_id Download ID
	 *
	 * @return EDD_Download|null
	 */
	abstract protected static function get_validated( int $download_id = 0 ): ?EDD_Download;

	/**
	 * Get the number of subscription renewals/billing cycles for a product.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional. Price ID for variable-priced products
	 *
	 * @return int Number of renewal times (0 means unlimited)
	 */
	public static function get_subscription_times( int $download_id, ?int $price_id = null ): int {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return 0;
		}

		if ( edd_has_variable_prices( $download_id ) && isset( $price_id ) ) {
			$prices = edd_get_variable_prices( $download_id );

			return isset( $prices[ $price_id ]['times'] ) ? (int) $prices[ $price_id ]['times'] : 0;
		}

		$times = get_post_meta( $download_id, 'edd_times', true );

		return $times ? (int) $times : 0;
	}

	/**
	 * Get recurring subscription details.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Optional price ID
	 *
	 * @return array Recurring details
	 */
	public static function get_recurring_details( int $download_id, ?int $price_id = null ): array {
		if ( ! function_exists( 'EDD_Recurring' ) ) {
			return self::get_default_recurring_details();
		}

		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return self::get_default_recurring_details();
		}

		$is_variable  = edd_has_variable_prices( $download_id );
		$is_recurring = $is_variable
			? EDD_Recurring()->is_price_recurring( $download_id, $price_id )
			: EDD_Recurring()->is_recurring( $download_id );

		if ( ! $is_recurring ) {
			return self::get_default_recurring_details();
		}

		return self::build_recurring_details( $download_id, $price_id, $is_variable );
	}

	/**
	 * Get default recurring details structure.
	 *
	 * @return array Default recurring details
	 */
	private static function get_default_recurring_details(): array {
		return [
			'is_recurring'      => false,
			'trial_period'      => 0,
			'trial_period_unit' => '',
			'billing_cycle'     => '',
			'renewal_times'     => 0,
			'signup_fee'        => 0.00,
		];
	}

	/**
	 * Build recurring details for a product.
	 *
	 * @param int      $download_id Download ID
	 * @param int|null $price_id    Price ID
	 * @param bool     $is_variable Whether product has variable prices
	 *
	 * @return array Recurring details
	 */
	private static function build_recurring_details( int $download_id, ?int $price_id, bool $is_variable ): array {
		if ( ! function_exists( 'EDD_Recurring' ) ) {
			return self::get_default_recurring_details();
		}

		$recurring               = EDD_Recurring();
		$details                 = self::get_default_recurring_details();
		$details['is_recurring'] = true;

		if ( $is_variable ) {
			$details['billing_cycle'] = $recurring->get_period( $price_id, $download_id );
			$details['renewal_times'] = $recurring->get_times( $price_id, $download_id );
			$details['signup_fee']    = (float) $recurring->get_signup_fee( $price_id, $download_id );
			$trial_period             = $recurring->get_trial_period( $download_id, $price_id );
		} else {
			$details['billing_cycle'] = $recurring->get_period_single( $download_id );
			$details['renewal_times'] = $recurring->get_times_single( $download_id );
			$details['signup_fee']    = (float) $recurring->get_signup_fee_single( $download_id );
			$trial_period             = $recurring->get_trial_period( $download_id );
		}

		if ( ! empty( $trial_period ) ) {
			$details['trial_period']      = $trial_period['quantity'];
			$details['trial_period_unit'] = $trial_period['unit'];
		}

		return $details;
	}

}