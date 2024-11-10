<?php
/**
 * Recount Tools Registration Utility for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.1
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Tools;

use Exception;

class Recount {

	/**
	 * An associative array of custom recount tools with their configurations.
	 *
	 * @var array
	 */
	private array $tools;

	/**
	 * RecountTools constructor.
	 *
	 * @param array $tools An associative array of custom recount tools with their configurations.
	 *
	 * @throws Exception If an empty array is passed.
	 */
	public function __construct( array $tools ) {
		if ( empty( $tools ) ) {
			throw new Exception( 'Empty tools array provided.' );
		}

		$this->tools = $this->validate_tools( $tools );
		$this->setup_hooks();
	}

	/**
	 * Validate and sanitize the tools array.
	 *
	 * @param array $tools The array of tools to validate.
	 *
	 * @return array The validated and sanitized tools array.
	 */
	private function validate_tools( array $tools ): array {
		$validated_tools = [];

		foreach ( $tools as $key => $tool ) {
			if ( ! isset( $tool['class'] ) || ! isset( $tool['file'] ) ) {
				continue; // Skip tools without required 'class' and 'file' keys
			}

			$validated_tool = [
				'class' => $tool['class'],
				'file'  => $tool['file'],
				'label' => $tool['label'] ?? ucwords( str_replace( '-', ' ', $key ) ),
			];

			if ( isset( $tool['description'] ) ) {
				$validated_tool['description'] = $tool['description'];
				$validated_tools[ $key ]       = $validated_tool;
			}
		}

		return $validated_tools;
	}

	/**
	 * Add custom recount tool options.
	 */
	public function add_recount_tool_options(): void {
		foreach ( $this->tools as $key => $tool ) {
			echo sprintf( '<option data-type="%s" value="%s">%s</option>',
				esc_attr( $key ),
				esc_attr( $tool['class'] ),
				esc_html( $tool['label'] )
			);
		}
	}

	/**
	 * Add custom recount tool descriptions.
	 */
	public function add_recount_tool_descriptions(): void {
		foreach ( $this->tools as $key => $tool ) {
			if ( isset( $tool['description'] ) ) {
				echo sprintf( '<span id="%s">%s</span>',
					esc_attr( $key ),
					wp_kses_post( $tool['description'] )
				);
			}
		}
	}

	/**
	 * Include custom batch processing classes.
	 *
	 * @param string $class The class being requested to run for the batch export.
	 */
	public function include_batch_processer( string $class ): void {
		foreach ( $this->tools as $tool ) {
			if ( $class === $tool['class'] && ! empty( $tool['file'] ) ) {
				require_once $tool['file'];
				break;
			}
		}
	}

	/**
	 * Setup the necessary hooks for custom recount tools.
	 */
	private function setup_hooks(): void {
		add_action( 'edd_recount_tool_options', [ $this, 'add_recount_tool_options' ] );
		add_action( 'edd_recount_tool_descriptions', [ $this, 'add_recount_tool_descriptions' ] );
		add_action( 'edd_batch_export_class_include', [ $this, 'include_batch_processer' ] );
	}

	/**
	 * Register custom recount tools.
	 *
	 * @param array $tools An associative array of custom recount tools with their configurations.
	 *
	 * @return Recount
	 * @throws Exception
	 */
	public static function register( array $tools ): Recount {
		return new self( $tools );
	}
}