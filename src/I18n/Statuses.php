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

namespace ArrayPress\EDD\I18n;

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

	/**
	 * Get the date period options.
	 *
	 * This method retrieves an array of date period options that can be used in various contexts
	 * throughout the application. Each period is represented as an array with 'label' and 'value' keys.
	 *
	 * @return array Array of period options with label and value pairs.
	 */
	public static function get_date_periods(): array {
		$periods = [
			[
				'label' => esc_html__( 'All Time', 'arraypress' ),
				'value' => 'all_time'
			],
			[
				'label' => esc_html__( 'Today', 'arraypress' ),
				'value' => 'today'
			],
			[
				'label' => esc_html__( 'Yesterday', 'arraypress' ),
				'value' => 'yesterday'
			],
			[
				'label' => esc_html__( 'This Week', 'arraypress' ),
				'value' => 'this_week'
			],
			[
				'label' => esc_html__( 'Last Week', 'arraypress' ),
				'value' => 'last_week'
			],
			[
				'label' => esc_html__( 'Last 30 Days', 'arraypress' ),
				'value' => 'last_30_days'
			],
			[
				'label' => esc_html__( 'This Month', 'arraypress' ),
				'value' => 'this_month'
			],
			[
				'label' => esc_html__( 'Last Month', 'arraypress' ),
				'value' => 'last_month'
			],
			[
				'label' => esc_html__( 'This Quarter', 'arraypress' ),
				'value' => 'this_quarter'
			],
			[
				'label' => esc_html__( 'Last Quarter', 'arraypress' ),
				'value' => 'last_quarter'
			],
			[
				'label' => esc_html__( 'This Year', 'arraypress' ),
				'value' => 'this_year'
			],
			[
				'label' => esc_html__( 'Last Year', 'arraypress' ),
				'value' => 'last_year'
			]
		];

		/**
		 * Filters the array of date period options.
		 *
		 * @param array $periods The array of period options.
		 */
		return apply_filters( 'arraypress_date_periods', $periods );
	}

}