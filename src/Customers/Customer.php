<?php
/**
 * Customer Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Customers;

use ArrayPress\EDD\Traits\Customer\{
	Admin,
	Comments,
	Core,
	Fields,
	Notes,
	Orders,
	Products,
	Recurring,
	Reviews,
	Status,
	Terms
};
use ArrayPress\Utils\Traits\Shared\Meta;

class Customer {
	use Core;
	use Fields;
	use Comments;
	use Orders;
	use Products;
	use Reviews;
	use Recurring;
	use Admin;
	use Status;
	use Notes;
	use Terms;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * Implements the abstract method from the Meta trait to specify
	 * that this class deals with EDD Customer meta.
	 *
	 * @return string The meta type 'edd_customer'.
	 */
	protected static function get_meta_type(): string {
		return 'edd_customer';
	}

}