<?php
/**
 * Helper function to register export columns for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD;

use ArrayPress\EDD\Register\Customer\Stats;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_customer_stats' ) ) :
	/**
	 * Register custom customer stats for EDD.
	 *
	 * @param array         $stats          An associative array of custom customer stats with their configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Stats|null Returns the Stats instance or null if an exception occurs.
	 */
	function register_custom_customer_stats(
		array $stats,
		?callable $error_callback = null
	): ?Stats {
		try {
			$customer_stats = new Stats();
			foreach ( $stats as $key => $config ) {
				$customer_stats->register( $key, $config );
			}
			$customer_stats->setup_hooks();

			return $customer_stats;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;