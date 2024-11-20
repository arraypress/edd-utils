<?php
/**
 * Easy Digital Downloads Extensions Utility Class
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Common;

class Extensions {

	/**
	 * Extension version constants mapping.
	 *
	 * Maps extension identifiers to their version constants.
	 */
	private const VERSION_CONSTANTS = [
		'software_licensing' => 'EDD_SL_VERSION',
		'recurring'          => 'EDD_RECURRING_VERSION',
		'commissions'        => 'EDDC_VERSION',
		'free_downloads'     => 'EDD_FREE_DOWNLOADS_VER',
		'reviews'            => 'EDD_REVIEWS_VERSION',
		'product_updates'    => 'EDD_PRODUCT_UPDATES_VERSION',
		'fes'                => 'fes_plugin_version',
		'invoices'           => 'EDD_INVOICES_VERSION',
		'stripe_pro'         => 'EDD_STRIPE_PRO_VERSION',
		'wallet'             => 'EDD_WALLET_VERSION',
		'gateway_fees'       => 'edd_gf_plugin_version'
	];

	/**
	 * Extension class mapping.
	 *
	 * Maps extension identifiers to their main classes for existence checks.
	 */
	private const EXTENSION_CLASSES = [
		'all_access'         => 'EDD_All_Access',
		'software_licensing' => 'EDD_Software_Licensing',
		'recurring'          => [ 'function' => 'EDD_Recurring' ],
		'commissions'        => 'EDDC',
		'free_downloads'     => 'EDD_Free_Downloads',
		'reviews'            => 'EDD_Reviews',
		'product_updates'    => 'EDD_Product_Updates',
		'fes'                => 'EDD_Front_End_Submissions',
		'invoices'           => 'EDDInvoices',
		'stripe_pro'         => 'EDD_Stripe_Pro',
		'wallet'             => 'EDD_Wallet',
		'gateway_fees'       => 'EDD_GF',
		'custom_deliverable' => 'EDD_Custom_Deliverables'
	];

	/**
	 * Check if All Access is active.
	 *
	 * @return bool True if All Access is active, false otherwise.
	 */
	public static function has_all_access(): bool {
		return class_exists( self::EXTENSION_CLASSES['all_access'] );
	}

	/**
	 * Check if Software Licensing is active.
	 *
	 * @return bool True if Software Licensing is active, false otherwise.
	 */
	public static function has_software_licensing(): bool {
		return class_exists( self::EXTENSION_CLASSES['software_licensing'] );
	}

	/**
	 * Check if Recurring Payments is active.
	 *
	 * @return bool True if Recurring Payments is active, false otherwise.
	 */
	public static function has_recurring(): bool {
		return function_exists( self::EXTENSION_CLASSES['recurring']['function'] );
	}

	/**
	 * Check if Commissions is active.
	 *
	 * @return bool True if Commissions is active, false otherwise.
	 */
	public static function has_commissions(): bool {
		return class_exists( self::EXTENSION_CLASSES['commissions'] );
	}

	/**
	 * Check if Free Downloads is active.
	 *
	 * @return bool True if Free Downloads is active, false otherwise.
	 */
	public static function has_free_downloads(): bool {
		return class_exists( self::EXTENSION_CLASSES['free_downloads'] );
	}

	/**
	 * Check if Reviews is active.
	 *
	 * @return bool True if Reviews is active, false otherwise.
	 */
	public static function has_reviews(): bool {
		return class_exists( self::EXTENSION_CLASSES['reviews'] );
	}

	/**
	 * Check if Product Updates is active.
	 *
	 * @return bool True if Product Updates is active, false otherwise.
	 */
	public static function has_product_updates(): bool {
		return class_exists( self::EXTENSION_CLASSES['product_updates'] );
	}

	/**
	 * Check if Frontend Submissions is active.
	 *
	 * @return bool True if Frontend Submissions is active, false otherwise.
	 */
	public static function has_fes(): bool {
		return class_exists( self::EXTENSION_CLASSES['fes'] );
	}

	/**
	 * Check if Invoices is active.
	 *
	 * @return bool True if Invoices is active, false otherwise.
	 */
	public static function has_invoices(): bool {
		return class_exists( self::EXTENSION_CLASSES['invoices'] );
	}

	/**
	 * Check if Stripe Pro is active.
	 *
	 * @return bool True if Stripe Pro is active, false otherwise.
	 */
	public static function has_stripe_pro(): bool {
		return class_exists( self::EXTENSION_CLASSES['stripe_pro'] );
	}

	/**
	 * Check if Wallet is active.
	 *
	 * @return bool True if Wallet is active, false otherwise.
	 */
	public static function has_wallet(): bool {
		return class_exists( self::EXTENSION_CLASSES['wallet'] );
	}

	/**
	 * Check if Gateway Fees is active.
	 *
	 * @return bool True if Gateway Fees is active, false otherwise.
	 */
	public static function has_gateway_fees(): bool {
		return class_exists( self::EXTENSION_CLASSES['gateway_fees'] );
	}

	/**
	 * Check if Custom Deliverables is active.
	 *
	 * @return bool True if Custom Deliverables is active, false otherwise.
	 */
	public static function has_custom_deliverable(): bool {
		return class_exists( self::EXTENSION_CLASSES['custom_deliverable'] );
	}

	/**
	 * Check multiple extensions at once.
	 *
	 * @param array $extensions Array of extension names to check.
	 *
	 * @return array Associative array of extension names and their status.
	 */
	public static function check_multiple( array $extensions ): array {
		$results = [];

		foreach ( $extensions as $extension ) {
			$method = 'has_' . strtolower( str_replace( '-', '_', $extension ) );

			if ( method_exists( __CLASS__, $method ) ) {
				$results[ $extension ] = self::$method();
			}
		}

		return $results;
	}

	/**
	 * Get all active EDD extensions.
	 *
	 * @return array Array of active extensions.
	 */
	public static function get_active_extensions(): array {
		$active     = [];
		$reflection = new \ReflectionClass( __CLASS__ );

		foreach ( $reflection->getMethods() as $method ) {
			if ( strpos( $method->getName(), 'has_' ) === 0 && $method->getName() !== 'has_multiple' ) {
				$extension_name = str_replace( 'has_', '', $method->getName() );
				if ( self::{$method->getName()}() ) {
					$active[] = $extension_name;
				}
			}
		}

		return $active;
	}

	/**
	 * Check if a specific version of an extension is active.
	 *
	 * @param string $extension The extension to check.
	 * @param string $version   The minimum version required.
	 *
	 * @return bool True if the extension is active and meets the version requirement.
	 */
	public static function check_version( string $extension, string $version ): bool {
		// First check if the extension is active
		$check_method = 'has_' . $extension;
		if ( ! method_exists( __CLASS__, $check_method ) || ! self::$check_method() ) {
			return false;
		}

		// If no version constant is defined for this extension, return true as it's at least active
		if ( ! isset( self::VERSION_CONSTANTS[ $extension ] ) || ! defined( self::VERSION_CONSTANTS[ $extension ] ) ) {
			return true;
		}

		// Compare versions
		return version_compare( constant( self::VERSION_CONSTANTS[ $extension ] ), $version, '>=' );
	}

}