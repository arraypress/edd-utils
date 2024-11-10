<?php
/**
 * Log Utilities for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Downloads;

use ArrayPress\EDD\Traits\Files\{
	Core,
	Price,
	Fields,
	Key
};

class Files {
	use Core;
	use Price;
	use Fields;
	use Key;
}