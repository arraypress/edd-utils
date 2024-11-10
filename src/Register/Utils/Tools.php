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

use ArrayPress\EDD\Register\Tools\Recount;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\register_recount_tools' ) ) :
	/**
	 * Register custom recount tools for EDD.
	 *
	 * @param array         $tools          An associative array of custom recount tools with their configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Recount|null Returns the RegisterRecountTools instance or null if an exception occurs.
	 */
	function register_recount_tools(
		array $tools,
		?callable $error_callback = null
	): ?Recount {
		try {
			return Recount::register( $tools );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;