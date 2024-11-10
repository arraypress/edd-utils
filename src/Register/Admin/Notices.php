<?php
/**
 * Extended EDD Notices Class
 *
 * @package     ArrayPress\EDD\Notices
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      David Sherlock
 */

namespace ArrayPress\EDD\Register\Admin;

if ( ! class_exists( 'EDD_Notices' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php';
}

use EDD_Notices;

class Notices extends EDD_Notices {

	/**
	 * @var array Custom notices for specific plugins
	 */
	private array $custom_notices = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', array( $this, 'register_custom_notices' ), 30 );
	}

	/**
	 * Register new custom notices
	 *
	 * @param string $plugin_slug The slug of the plugin
	 * @param array  $notices     Array of notices to register
	 * @param string $capability  Optional. Capability required to view notices
	 */
	public function register_notices( string $plugin_slug, array $notices, string $capability = 'edit_shop_payments' ) {
		if ( ! isset( $this->custom_notices[ $plugin_slug ] ) ) {
			$this->custom_notices[ $plugin_slug ] = array();
		}

		foreach ( $notices as $notice_key => $notice_args ) {
			// Generate ID if not provided
			if ( ! isset( $notice_args['id'] ) ) {
				$notice_args['id'] = str_replace( '_', '-', $notice_key );
			}

			// Set default capability if not provided
			if ( ! isset( $notice_args['capability'] ) ) {
				$notice_args['capability'] = $capability;
			}

			$this->custom_notices[ $plugin_slug ][ $notice_key ] = $notice_args;
		}
	}

	/**
	 * Register custom notices for specific plugins
	 */
	public function register_custom_notices() {
		foreach ( $this->custom_notices as $plugin_slug => $notices ) {
			foreach ( $notices as $notice_key => $notice_args ) {
				$this->maybe_add_custom_notice( $plugin_slug, $notice_key, $notice_args );
			}
		}
	}

	/**
	 * Maybe add a custom notice if conditions are met
	 *
	 * @param string $plugin_slug The slug of the plugin
	 * @param string $notice_key  The unique key for the notice
	 * @param array  $notice_args The notice arguments
	 */
	private function maybe_add_custom_notice( string $plugin_slug, string $notice_key, array $notice_args ) {
		if ( $this->should_display_notice( $notice_args ) ) {
			$this->add_notice( $notice_args );
		}
	}

	/**
	 * Determine if a notice should be displayed
	 *
	 * @param array $notice_args The notice arguments
	 *
	 * @return bool Whether the notice should be displayed
	 */
	private function should_display_notice( array $notice_args ): bool {
		if ( isset( $notice_args['display_callback'] ) && is_callable( $notice_args['display_callback'] ) ) {
			return call_user_func( $notice_args['display_callback'] );
		}

		// Default to checking user capability
		return current_user_can( $notice_args['capability'] );
	}

	/**
	 * Get custom notices for a specific plugin
	 *
	 * @param string $plugin_slug The slug of the plugin
	 *
	 * @return array The custom notices for the plugin
	 */
	public function get_custom_notices( string $plugin_slug ): array {
		return $this->custom_notices[ $plugin_slug ] ?? array();
	}

	/**
	 * Remove custom notices for a specific plugin
	 *
	 * @param string $plugin_slug The slug of the plugin
	 */
	public function remove_custom_notices( string $plugin_slug ) {
		if ( isset( $this->custom_notices[ $plugin_slug ] ) ) {
			unset( $this->custom_notices[ $plugin_slug ] );
		}
	}

	/**
	 * Remove a specific custom notice
	 *
	 * @param string $plugin_slug The slug of the plugin
	 * @param string $notice_key  The unique key for the notice
	 */
	public function remove_custom_notice( string $plugin_slug, string $notice_key ) {
		if ( isset( $this->custom_notices[ $plugin_slug ][ $notice_key ] ) ) {
			unset( $this->custom_notices[ $plugin_slug ][ $notice_key ] );
		}
	}

}