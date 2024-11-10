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

use ArrayPress\EDD\Register\Download\DownloadSettings;
use ArrayPress\EDD\Register\Download\SidebarMetaboxes;
use ArrayPress\EDD\Register\Download\SinglePriceOptions;
use ArrayPress\EDD\Register\Download\VariablePriceOptions;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_download_settings' ) ) :
	/**
	 * Register custom download settings for EDD.
	 *
	 * @param array         $sections       An array of section configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return DownloadSettings|null Returns the DownloadSettings instance or null if an exception occurs.
	 */
	function register_custom_download_settings(
		array $sections,
		?callable $error_callback = null
	): ?DownloadSettings {
		try {
			$download_settings = new DownloadSettings();

			foreach ( $sections as $section_id => $section ) {
				if ( ! isset( $section['title'] ) || ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
					throw new \InvalidArgumentException( "Invalid section configuration for '{$section_id}'." );
				}

				$download_settings->add_section( $section_id, $section['title'], $section );

				foreach ( $section['fields'] as $field_id => $field ) {
					$download_settings->add_field( $section_id, $field_id, $field );
				}
			}

			return $download_settings;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_single_price_options' ) ) :
	/**
	 * Register custom single price options for EDD.
	 *
	 * @param array         $sections       An array of section configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return SinglePriceOptions|null Returns the SinglePriceOptions instance or null if an exception occurs.
	 */
	function register_custom_single_price_options(
		array $sections,
		?callable $error_callback = null
	): ?SinglePriceOptions {
		try {
			$single_price_options = new SinglePriceOptions();

			foreach ( $sections as $section_id => $section ) {
				if ( ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
					throw new \InvalidArgumentException( "Invalid section configuration for '{$section_id}'." );
				}

				$single_price_options->add_section( $section_id, $section );

				foreach ( $section['fields'] as $field_id => $field ) {
					$single_price_options->add_field( $section_id, $field_id, $field );
				}
			}

			return $single_price_options;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			error_log( 'EDD Single Price Options Registration Error: ' . $e->getMessage() );

			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_variable_price_options' ) ) :
	/**
	 * Register variable price options for EDD.
	 *
	 * @param array         $sections       An array of section configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return VariablePriceOptions|null Returns the VariablePriceOptions instance or null if an exception occurs.
	 */
	function register_custom_variable_price_options(
		array $sections,
		?callable $error_callback = null
	): ?VariablePriceOptions {
		try {
			$variable_price_options = new VariablePriceOptions();

			foreach ( $sections as $section_id => $section ) {
				if ( ! isset( $section['title'] ) || ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
					throw new \InvalidArgumentException( "Invalid section configuration for '{$section_id}'." );
				}

				$variable_price_options->add_section( $section_id, $section['title'], $section );

				foreach ( $section['fields'] as $field_id => $field ) {
					$variable_price_options->add_field( $section_id, $field_id, $field );
				}
			}

			return $variable_price_options;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_sidebar_metaboxes' ) ) :
	/**
	 * Register custom sidebar metaboxes for EDD.
	 *
	 * @param array         $metaboxes      An array of metabox configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return SidebarMetaboxes|null Returns the SidebarMetaboxes instance or null if an exception occurs.
	 */
	function register_custom_sidebar_metaboxes(
		array $metaboxes,
		?callable $error_callback = null
	): ?SidebarMetaboxes {
		try {
			$sidebar_metaboxes = new SidebarMetaboxes();

			foreach ( $metaboxes as $metabox_id => $metabox ) {
				if ( ! isset( $metabox['title'] ) || ! isset( $metabox['sections'] ) || ! is_array( $metabox['sections'] ) ) {
					throw new \InvalidArgumentException( "Invalid metabox configuration for '{$metabox_id}'." );
				}

				$sidebar_metaboxes->add_metabox( $metabox_id, $metabox['title'], $metabox );

				foreach ( $metabox['sections'] as $section_id => $section ) {
					if ( ! isset( $section['title'] ) || ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
						throw new \InvalidArgumentException( "Invalid section configuration for '{$section_id}' in metabox '{$metabox_id}'." );
					}

					$sidebar_metaboxes->add_section( $metabox_id, $section_id, $section['title'], $section );

					foreach ( $section['fields'] as $field_id => $field ) {
						$sidebar_metaboxes->add_field( $metabox_id, $section_id, $field_id, $field );
					}
				}
			}

			return $sidebar_metaboxes;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null; // Return null on failure
		}
	}
endif;