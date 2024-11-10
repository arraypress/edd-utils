<?php
/**
 * Earnings Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for calculating total and average earnings.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

use EDD\Stats;

trait Core {

	/**
	 * Get Stats instance with given arguments.
	 *
	 * @param array $args Stats arguments
	 *
	 * @return Stats
	 */
	protected static function get_stats( array $args = [] ): Stats {
		return new Stats( $args );
	}

}