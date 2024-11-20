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
	 * @param bool $sort            Optional. Whether to sort alphabetically by label. Default true.
	 * @param bool $include_expired Optional. Whether to include expired status. Default true.
	 *
	 * @return array Array of status options with label and value.
	 */
	public static function get_discount_statuses( bool $sort = true, bool $include_expired = true ): array {
		return [
			[
				'label' => esc_html__( 'Active', 'arraypress' ),
				'value' => 'active'
			],
			[
				'label' => esc_html__( 'Inactive', 'arraypress' ),
				'value' => 'inactive'
			],
			[
				'label' => esc_html__( 'Archived', 'arraypress' ),
				'value' => 'archived'
			],
			[
				'label' => esc_html__( 'Expired', 'arraypress' ),
				'value' => 'expired'
			]
		];
	}

	/**
	 * Get the commission status options.
	 *
	 * @return array Array of status options with label and value.
	 */
	public static function get_commission_statuses(): array {
		return [
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
	}

}