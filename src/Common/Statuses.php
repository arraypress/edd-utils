<?php
/**
 * Status Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Common;

class Statuses {

	/**
	 * Get the discount status options.
	 *
	 * This method retrieves an array of discount status options that can be used in various contexts
	 * throughout the application. Each status is represented as an array with 'label' and 'value' keys.
	 *
	 * @return array Array of status options with label and value pairs.
	 */
	public static function get_discount(): array {
		$statuses = [
			[
				'label' => esc_html__( 'Active', 'arraypress' ),
				'value' => 'active'
			],
			[
				'label' => esc_html__( 'Inactive', 'arraypress' ),
				'value' => 'inactive'
			],
			[
				'label' => esc_html__( 'Expired', 'arraypress' ),
				'value' => 'expired'
			],
			[
				'label' => esc_html__( 'Archived', 'arraypress' ),
				'value' => 'archived'
			]
		];

		/**
		 * Filters the array of discount status options.
		 *
		 * @param array $statuses The array of status options.
		 */
		return apply_filters( 'arraypress_discount_statuses', $statuses );
	}

	/**
	 * Get the commission status options.
	 *
	 * This method retrieves an array of commission status options that can be used in various contexts
	 * throughout the application. Each status is represented as an array with 'label' and 'value' keys.
	 *
	 * @return array Array of status options with label and value pairs.
	 */
	public static function get_commission(): array {
		$statuses = [
			[
				'label' => esc_html__( 'Unpaid', 'arraypress' ),
				'value' => 'unpaid'
			],
			[
				'label' => esc_html__( 'Paid', 'arraypress' ),
				'value' => 'paid'
			],
			[
				'label' => esc_html__( 'Revoked', 'arraypress' ),
				'value' => 'revoked'
			]
		];

		/**
		 * Filters the array of commission status options.
		 *
		 * @param array $statuses The array of status options.
		 */
		return apply_filters( 'arraypress_commission_statuses', $statuses );
	}

	/**
	 * Get the customer status options.
	 *
	 * This method retrieves an array of customer status options that can be used in various contexts
	 * throughout the application. Each status is represented as an array with 'label' and 'value' keys.
	 *
	 * @return array Array of status options with label and value pairs.
	 * @since 1.0.0
	 *
	 */
	public static function get_customer(): array {
		$statuses = [
			[
				'label' => esc_html__( 'Active', 'arraypress' ),
				'value' => 'active'
			],
			[
				'label' => esc_html__( 'Inactive', 'arraypress' ),
				'value' => 'inactive'
			],
			[
				'label' => esc_html__( 'Disabled', 'arraypress' ),
				'value' => 'disabled'
			],
			[
				'label' => esc_html__( 'Pending', 'arraypress' ),
				'value' => 'pending'
			]
		];

		/**
		 * Filters the array of customer status options.
		 *
		 * @param array $statuses The array of status options.
		 */
		return apply_filters( 'arraypress_customer_statuses', $statuses );
	}

}