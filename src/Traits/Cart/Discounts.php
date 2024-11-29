<?php
/**
 * Discount Operations Trait for Easy Digital Downloads (EDD)
 *
 * This trait provides methods for handling discount operations in the EDD cart.
 *
 * @package       ArrayPress\EDD\Traits\Cart
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Cart;

trait Discounts {

	/**
	 * Get all discount IDs in the cart.
	 *
	 * @return array Array of discount IDs.
	 */
	public static function get_discount_ids(): array {
		return self::get_discounts();
	}

	/**
	 * Retrieve all discounts in the cart.
	 *
	 * @param bool $return_objects Whether to return discount objects or IDs.
	 *
	 * @return array An array of discount IDs or objects.
	 */
	public static function get_discounts( bool $return_objects = false ): array {
		$discounts = [];

		foreach ( EDD()->cart->get_discounts() as $discount_code ) {
			$discount = edd_get_discount_by_code( $discount_code );
			if ( $discount && ! is_wp_error( $discount ) ) {
				$discounts[] = $discount;
			}
		}

		return $return_objects ? $discounts : array_unique( wp_list_pluck( $discounts, 'id' ) );
	}

}