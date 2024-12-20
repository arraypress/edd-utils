<?php
/**
 * Download Utilities for Easy Digital Downloads (EDD)
 *
 * Provides extended functionality for EDD downloads not available in core.
 * Focuses on price handling, product analysis, and advanced product details.
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Downloads;

use ArrayPress\EDD\Traits\Download\{
	Core,
	Earnings,
	Licensing,
	Orders,
	Price,
	Recurring,
	Reviews,
	Sales,
	VariablePrices
};
use ArrayPress\Utils\Traits\Shared\Meta;

class Download {
	use Core;
	use Earnings;
	use Licensing;
	use Orders;
	use Price;
	use Recurring;
	use Reviews;
	use Sales;
	use VariablePrices;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * Implements the abstract method from the Meta trait to specify
	 * that this class deals with post meta.
	 *
	 * @return string The meta type 'post'.
	 */
	protected static function get_meta_type(): string {
		return 'post';
	}

}