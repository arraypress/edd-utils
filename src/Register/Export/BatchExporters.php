<?php
/**
 * Batch Export Helper for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL2+
 * @since         1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Export;

class BatchExporters {

	/**
	 * @var array An associative array of batch exports with their configurations.
	 */
	private array $exports;

	/**
	 * @var string|null The base path for export files.
	 */
	private ?string $base_path;

	/**
	 * BatchExporters constructor.
	 *
	 * @param array       $exports   An associative array of batch exports with their configurations.
	 * @param string|null $base_path Optional base path for export files.
	 */
	public function __construct( array $exports, ?string $base_path = null ) {
		$this->exports   = $exports;
		$this->base_path = $base_path;
	}

	/**
	 * Register batch exports with Easy Digital Downloads.
	 *
	 * @return void
	 */
	public function register_exports(): void {
		add_action( 'edd_batch_export_class_include', [ $this, 'include_export_class' ] );
	}

	/**
	 * Include the batch export class file.
	 *
	 * @param string $class The class being requested to run for the batch export.
	 *
	 * @return void
	 */
	public function include_export_class( string $class ): void {
		foreach ( $this->exports as $key => $export ) {
			if ( $export['class'] === $class && ! empty( $export['file'] ) ) {
				$file = $this->get_full_file_path( $export['file'] );
				if ( file_exists( $file ) ) {
					require_once $file;

					return;
				}
			}
		}
	}

	/**
	 * Get the full file path.
	 *
	 * @param string $file The file path.
	 *
	 * @return string The full file path.
	 */
	private function get_full_file_path( string $file ): string {
		return $this->base_path ? trailingslashit( $this->base_path ) . $file : $file;
	}

	/**
	 * Static method to create and register batch exports.
	 *
	 * @param array       $exports   An associative array of batch exports with their configurations.
	 * @param string|null $base_path Optional base path for export files.
	 *
	 * @return self
	 */
	public static function register( array $exports, ?string $base_path = null ): self {
		$instance = new self( $exports, $base_path );
		$instance->register_exports();

		return $instance;
	}
}