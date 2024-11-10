<?php
/**
 * File Identifiers Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides methods for handling file identifiers, keys, and related operations in EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Files;

trait Key {

	/**
	 * Get the product file key.
	 *
	 * @param int      $file_id     The ID of the file.
	 * @param int      $download_id The ID of the download.
	 * @param int|null $price_id    The price ID of the product, if applicable.
	 *
	 * @return string The product file key.
	 */
	public static function get_file_key( int $file_id, int $download_id, ?int $price_id = null ): string {
		$keys = [ $file_id, $download_id ];

		if ( $price_id !== null ) {
			$keys[] = absint( $price_id );
		}

		return implode( '_', $keys );
	}

	/**
	 * Parse a product file key into its component parts.
	 *
	 * @param string $value The product file key to parse.
	 *
	 * @return array{file_id: int|false, download_id: int|false, price_id: int|false} An array containing the file ID,
	 *                        download ID, and price ID.
	 */
	public static function parse_file_key( string $value ): array {
		if ( empty( $value ) ) {
			return [
				'file_id'     => false,
				'download_id' => false,
				'price_id'    => false
			];
		}

		$parts = explode( '_', $value );

		return [
			'file_id'     => isset( $parts[0] ) ? absint( $parts[0] ) : false,
			'download_id' => isset( $parts[1] ) ? absint( $parts[1] ) : false,
			'price_id'    => isset( $parts[2] ) ? absint( $parts[2] ) : false
		];
	}

	/**
	 * Validate a product file key.
	 *
	 * @param string $value The product file key to validate.
	 *
	 * @return bool True if the product file key is valid, false otherwise.
	 */
	public static function validate_file_key( string $value ): bool {
		$args = self::parse_file_key( $value );

		$download = edd_get_download( $args['download_id'] );
		if ( ! $download ) {
			return false;
		}

		$files = $download->get_files();

		return isset( $files[ $args['file_id'] ] );
	}

}