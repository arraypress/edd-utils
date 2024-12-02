<?php
/**
 * Software Licensing Trait for Easy Digital Downloads (EDD) Customers
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

trait Licensing {

	/**
	 * Checks if the customer has any active licenses.
	 *
	 * @param int  $customer_id            The ID of the customer to check.
	 * @param int  $download_id            Optional. Specific download ID to check.
	 * @param bool $include_child_licenses Optional. Include child licenses in check.
	 *
	 * @return bool True if customer has active licenses, false otherwise.
	 */
	public static function has_active_licenses( int $customer_id = 0, int $download_id = 0, bool $include_child_licenses = true ): bool {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$user = Customer::get_user( $customer_id );
		if ( empty( $user ) ) {
			return false;
		}

		$licenses = edd_software_licensing()->get_license_keys_of_user(
			$user->ID,
			$download_id,
			'active',
			$include_child_licenses
		);

		return ! empty( $licenses );
	}

	/**
	 * Get all licenses for a customer.
	 *
	 * @param int    $customer_id            The ID of the customer.
	 * @param int    $download_id            Optional. Specific download ID to retrieve.
	 * @param string $status                 Optional. Filter by license status. Default 'any'.
	 * @param bool   $include_child_licenses Optional. Include child licenses.
	 *
	 * @return array|null Array of license objects or null if none found.
	 */
	public static function get_licenses(
		int $customer_id = 0,
		int $download_id = 0,
		string $status = 'any',
		bool $include_child_licenses = true
	): ?array {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return null;
		}

		$user = Customer::get_user( $customer_id );
		if ( empty( $user ) ) {
			return null;
		}

		$licenses = edd_software_licensing()->get_license_keys_of_user(
			$user->ID,
			$download_id,
			$status,
			$include_child_licenses
		);

		return ! empty( $licenses ) ? $licenses : null;
	}

	/**
	 * Get the count of customer licenses.
	 *
	 * @param int    $customer_id            The ID of the customer.
	 * @param int    $download_id            Optional. Specific download ID to count.
	 * @param string $status                 Optional. Filter by license status.
	 * @param bool   $include_child_licenses Optional. Include child licenses.
	 *
	 * @return int The number of licenses.
	 */
	public static function get_license_count(
		int $customer_id = 0,
		int $download_id = 0,
		string $status = 'any',
		bool $include_child_licenses = true
	): int {
		$licenses = self::get_licenses( $customer_id, $download_id, $status, $include_child_licenses );

		return $licenses ? count( $licenses ) : 0;
	}

	/**
	 * Get customer's license by product ID.
	 *
	 * @param int    $customer_id            The ID of the customer.
	 * @param int    $product_id             The ID of the product.
	 * @param string $status                 Optional. Filter by license status.
	 * @param bool   $include_child_licenses Optional. Include child licenses.
	 *
	 * @return object|null License object if found, null otherwise.
	 */
	public static function get_license_by_product(
		int $customer_id,
		int $product_id,
		string $status = 'active',
		bool $include_child_licenses = true
	): ?object {
		return self::get_licenses( $customer_id, $product_id, $status, $include_child_licenses )[0] ?? null;
	}
}