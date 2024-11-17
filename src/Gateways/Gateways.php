<?php
/**
 * Gateways Statistics for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Gateways
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Gateways;

use ArrayPress\EDD\Date\Generate;
use ArrayPress\Utils\Common\Arr;
use EDD\Stats;
use Exception;

class Gateways {

	/**
	 * Get the payment gateway options.
	 *
	 * @param bool $sort Optional. Whether to sort the options alphabetically by label. Default true.
	 *
	 * @return array An array of payment gateway options with label and value.
	 */
	public static function get_options( bool $sort = true ): array {
		$all_gateways = edd_get_payment_gateways();

		if ( empty( $all_gateways ) ) {
			return [];
		}

		$options = [];

		foreach ( $all_gateways as $key => $gateway ) {
			$options[] = [
				'label' => esc_html( $gateway['admin_label'] ),
				'value' => esc_attr( $key ),
			];
		}

		if ( $sort ) {
			$options = Arr::sort_by_column( $options, 'label' );
		}

		return $options;
	}

}