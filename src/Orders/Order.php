<?php
/**
 * Order Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Orders;

use ArrayPress\EDD\Traits\Order\{
	Adjustments,
	Admin,
	Analytics,
	Commissions,
	Conditions,
	Core,
	Discounts,
	Fields,
	Licensing,
	Products,
	Recurring,
	Referral,
	Options,
	Terms,
	Files
};

use ArrayPress\Utils\Traits\Shared\Meta;

class Order {
	use Core;
	use Adjustments;
	use Admin;
	use Analytics;
	use Commissions;
	use Conditions;
	use Discounts;
	use Fields;
	use Licensing;
	use Products;
	use Recurring;
	use Referral;
	use Options;
	use Terms;
	use Files;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * Implements the abstract method from the Meta trait to specify
	 * that this class deals with order meta.
	 *
	 * @return string The meta type 'edd_order'.
	 */
	protected static function get_meta_type(): string {
		return 'edd_order';
	}

}