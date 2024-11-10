<?php
/**
 * Option Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

use ArrayPress\Utils\Common\Arr;

trait Options {

	/**
	 * Get the payment status options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of payment status options with label and value.
	 */
	public static function get_status_options( bool $sort = true ): array {
		$all_statuses = edd_get_payment_statuses();

		if ( empty( $all_statuses ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_statuses as $status => $label ) {
			$options[] = [
				'label' => esc_html( $label ),
				'value' => esc_attr( $status ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

}