<?php
/**
 * Product Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides product-related operations for EDD customers, handling purchased products,
 * bundles, and related term operations.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\EDD\Customers\Customer;

trait Quantity {

	/**
	 * Get count of customer's products by type.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $type        The product type to count.
	 * @param array  $status      Optional. The order statuses to include.
	 *
	 * @return int The count of products matching the specified type.
	 */
	public static function get_count_by_type( int $customer_id, string $type, array $status = [] ): int {
		$count       = 0;
		$product_ids = Customer::get_product_ids( $customer_id, $status );

		if ( empty( $product_ids ) ) {
			return $count;
		}

		$type = strtolower( $type );

		foreach ( $product_ids as $product_id ) {
			$download = edd_get_download( $product_id );
			if ( $download && strtolower( $download->get_type() ) === $type ) {
				$count ++;
			}
		}

		return absint( $count );
	}

	/**
	 * Check if customer has any products of a specific type.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $type        The product type to check.
	 * @param array  $status      Optional. The order statuses to include.
	 *
	 * @return bool True if customer has products of the specified type.
	 */
	public static function has_type(int $customer_id, string $type, array $status = []): bool {
		return self::get_count_by_type($customer_id, $type, $status) > 0;
	}

	/**
	 * Get count of customer's Bundle products.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $status      Optional. The order statuses to include.
	 *
	 * @return int The count of Bundle products.
	 */
	public static function get_bundle_count( int $customer_id, array $status = [] ): int {
		return self::get_count_by_type( $customer_id, 'bundle', $status );
	}

	/**
	 * Get count of customer's All Access pass products.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $status      Optional. The order statuses to include.
	 *
	 * @return int The count of All Access products.
	 */
	public static function get_all_access_count( int $customer_id, array $status = [] ): int {
		return self::get_count_by_type( $customer_id, 'all_access', $status );
	}

	/**
	 * Get count of customer's service products.
	 *
	 * @param int   $customer_id The ID of the customer.
	 * @param array $status      Optional. The order statuses to include.
	 *
	 * @return int The count of Service products.
	 */
	public static function get_service_count( int $customer_id, array $status = [] ): int {
		return self::get_count_by_type( $customer_id, 'service', $status );
	}

}