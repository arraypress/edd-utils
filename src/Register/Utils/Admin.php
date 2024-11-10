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

use ArrayPress\EDD\Register\Admin\Notices;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_notices' ) ) :
	/**
	 * Register custom notices for EDD.
	 *
	 * @param string        $plugin_slug    The slug of the plugin registering the notices.
	 * @param array         $notices        An associative array of notices with their configurations.
	 * @param string        $capability     Optional. The capability required to view the notices (default: 'edit_shop_payments').
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Notices|null Returns the Notices instance or null if an exception occurs.
	 */
	function register_custom_notices(
		string $plugin_slug,
		array $notices,
		string $capability = 'edit_shop_payments',
		?callable $error_callback = null
	): ?Notices {
		try {

			// Create or get the Notices instance
			$notice_manager = new Notices();

			$notice_manager->register_notices( $plugin_slug, $notices, $capability );

			return $notice_manager;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;