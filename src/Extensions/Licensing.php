<?php
/**
 * License Operations Class for Easy Digital Downloads (EDD)
 *
 * @package     ArrayPress/EDD-Utils
 * @copyright   Copyright 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Extensions;

class Licensing {

	/**
	 * Get the count of active site activations for a license.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return int Number of active sites.
	 */
	public static function get_activation_count( int $license_id ): int {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return 0;
		}

		$license = edd_software_licensing()->get_license( $license_id );
		if ( ! $license ) {
			return 0;
		}

		$args = array(
			'license_id' => $license_id,
			'activated'  => 1,
			'is_local'   => array( 0, 1 ),
		);

		return edd_software_licensing()->activations_db->count( $args );
	}

	/**
	 * Get all activated sites for a license.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return array|null Array of activated sites or null if none found.
	 */
	public static function get_activated_sites( int $license_id ): ?array {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return null;
		}

		$license = edd_software_licensing()->get_license( $license_id );
		if ( ! $license ) {
			return null;
		}

		$sites = $license->get_activations();

		return ! empty( $sites ) ? $sites : null;
	}

	/**
	 * Check if a license has reached its activation limit.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return bool True if limit reached, false otherwise.
	 */
	public static function is_activation_limit_reached( int $license_id ): bool {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$license = edd_software_licensing()->get_license( $license_id );

		return $license && $license->is_at_limit();
	}

	/**
	 * Get the activation limit for a license.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return int|string Activation limit or 'Unlimited'
	 */
	public static function get_license_limit( int $license_id ) {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return 0;
		}

		$license = edd_software_licensing()->get_license( $license_id );

		return $license ? $license->license_limit() : 0;
	}

	/**
	 * Get license expiration date.
	 *
	 * @param int    $license_id The ID of the license.
	 * @param string $format     Optional. Date format.
	 *
	 * @return string|null Formatted expiration date or null if not found.
	 */
	public static function get_license_expiration( int $license_id, string $format = 'Y-m-d H:i:s' ): ?string {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return null;
		}

		$license = edd_software_licensing()->get_license( $license_id );
		if ( ! $license || $license->is_lifetime ) {
			return null;
		}

		return date( $format, $license->expiration );
	}

	/**
	 * Check if license is expired.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return bool True if expired, false otherwise.
	 */
	public static function is_expired( int $license_id ): bool {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$license = edd_software_licensing()->get_license( $license_id );

		return $license && $license->is_expired();
	}

	/**
	 * Get the license term.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return string License term (e.g., "Lifetime" or "1 Year")
	 */
	public static function get_license_term( int $license_id ): string {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return '';
		}

		$license = edd_software_licensing()->get_license( $license_id );

		return $license ? $license->license_term() : '';
	}

	/**
	 * Check if a license is active for a specific site.
	 *
	 * @param int    $license_id The ID of the license.
	 * @param string $site_url   The site URL to check.
	 *
	 * @return bool True if site is active, false otherwise.
	 */
	public static function is_site_active( int $license_id, string $site_url ): bool {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return false;
		}

		$license = edd_software_licensing()->get_license( $license_id );

		return $license && $license->is_site_active( $site_url );
	}

	/**
	 * Get the license status.
	 *
	 * @param int $license_id The ID of the license.
	 *
	 * @return string License status
	 */
	public static function get_status( int $license_id ): string {
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return '';
		}

		$license = edd_software_licensing()->get_license( $license_id );
		if ( ! $license ) {
			return '';
		}

		$status     = $license->status;
		$expiration = $license->expiration;

		if ( ! empty( $expiration ) && $expiration < current_time( 'timestamp' ) && 'expired' !== $status ) {
			return 'expired';
		} elseif ( 'expired' === $status && $expiration > current_time( 'timestamp' ) ) {
			$count = self::get_activation_count( $license_id );

			return $count >= 1 ? 'active' : 'inactive';
		}

		return $status;
	}

}