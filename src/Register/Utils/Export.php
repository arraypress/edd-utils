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

use ArrayPress\EDD\Register\Export\BatchExporters;
use ArrayPress\EDD\Register\Export\Columns;
use ArrayPress\EDD\Register\Export\Metaboxes;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_export_columns' ) ) :
	/**
	 * Register custom columns for EDD CSV exports.
	 *
	 * @param string        $type           The type of CSV export (e.g., 'downloads', 'customers', etc.).
	 * @param array         $columns        An associative array of custom columns with their configurations.
	 * @param string        $id_field       The field to use as the identifier (default: 'ID').
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Columns|null Returns the RegisterExportColumns instance or null if an exception occurs.
	 */
	function register_custom_export_columns(
		string $type,
		array $columns,
		string $id_field = 'ID',
		?callable $error_callback = null
	): ?Columns {
		try {
			return Columns::register( $type, $columns, $id_field );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}

endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_batch_exporters' ) ) :
	/**
	 * Register batch exporters for EDD.
	 *
	 * @param array         $exporters      An associative array of batch exporters with their configurations.
	 * @param string|null   $base_path      Optional base path for exporter files.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return BatchExporters|null Returns the BatchExporters instance or null if an exception occurs.
	 */
	function register_custom_batch_exporters(
		array $exporters,
		?string $base_path = null,
		?callable $error_callback = null
	): ?BatchExporters {
		try {
			return BatchExporters::register( $exporters, $base_path );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_export_metaboxes' ) ) :
	/**
	 * Register export metaboxes for EDD.
	 *
	 * @param array         $metaboxes      An array of metabox configurations.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return Metaboxes|null Returns the Metaboxes instance or null if an exception occurs.
	 */
	function register_custom_export_metaboxes(
		array $metaboxes,
		?callable $error_callback = null
	): ?Metaboxes {
		try {
			$export_metaboxes = new Metaboxes();
			foreach ( $metaboxes as $metabox ) {
				if ( is_array( $metabox ) ) {
					$export_metaboxes->register_metabox( $metabox );
				} else {
					throw new \InvalidArgumentException( "Each metabox configuration must be an array." );
				}
			}

			return $export_metaboxes;
		} catch ( \Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_custom_exporters' ) ) :
	/**
	 * Register export metaboxes and batch exporters for EDD.
	 *
	 * @param array         $metaboxes      An array of metabox configurations.
	 * @param string|null   $base_path      Optional base path for exporter files.
	 * @param callable|null $error_callback Callback function for error handling.
	 *
	 * @return array Returns an array containing ExportMetaboxes and BatchExporters instances, or null values if exceptions
	 *               occur.
	 */
	function register_custom_exporters(
		array $metaboxes,
		?string $base_path = null,
		?callable $error_callback = null
	): ?array {
		$export_metaboxes = null;
		$batch_exporters  = null;

		try {
			$export_metaboxes = new Metaboxes();
			$exporters        = [];

			foreach ( $metaboxes as $metabox ) {
				if ( is_array( $metabox ) ) {
					$export_metaboxes->register_metabox( $metabox );
				} else {
					throw new \InvalidArgumentException( "Each metabox configuration must be an array." );
				}

				if ( ! empty( $metabox['export_class'] ) && ! empty( $metabox['file'] ) ) {
					$exporters[ $metabox['id'] ] = [
						'class' => $metabox['export_class'],
						'file'  => $metabox['file'],
					];
				}
			}

			$batch_exporters = register_custom_batch_exporters( $exporters, $base_path, $error_callback );
		} catch ( \Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}

		return [ $export_metaboxes, $batch_exporters ];
	}
endif;