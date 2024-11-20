<?php
/**
 * Main Cart Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress\EDD\Cart
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Cart;

use ArrayPress\EDD\Traits\Cart\{
	Core,
	Discounts,
	Products,
	ProductType,
	Taxonomy,
	Totals
};

class Cart {
	use Core;
	use Discounts;
	use Products;
	use ProductType;
	use Taxonomy;
	use Totals;
}