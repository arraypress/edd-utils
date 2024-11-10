<?php
/**
 * Export Column Registration Utility for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Export;

use Exception;

class Columns {

	/**
	 * The type of CSV export (e.g., 'downloads', 'customers', etc.).
	 *
	 * This determines which EDD export filters to hook into.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * An associative array of custom columns with their configurations.
	 *
	 * Each element should be in the format:
	 * 'column_key' => [
	 *     'label' => 'Column Label',
	 *     'callback' => callable
	 * ]
	 *
	 * @var array
	 */
	private array $columns;

	/**
	 * The field to use as the identifier for each row in the export data.
	 *
	 * This is typically 'ID' or 'id', but can be customized if needed.
	 *
	 * @var string
	 */
	private string $id_field;

	/**
	 * RegisterColumns constructor.
	 *
	 * @param string $type     The type of CSV export (e.g., 'downloads', 'customers', etc.).
	 * @param array  $columns  An associative array of custom columns with their configurations.
	 * @param string $id_field The field to use as the identifier (default: 'ID').
	 *
	 * @throws Exception If an invalid or empty array is passed.
	 */
	public function __construct( string $type, array $columns, string $id_field = 'ID' ) {
		if ( empty( $type ) || empty( $columns ) ) {
			throw new Exception( 'Invalid type or empty columns array provided.' );
		}

		$this->type     = $type;
		$this->columns  = $columns;
		$this->id_field = $id_field;

		$this->setup_hooks();
	}

	/**
	 * Register custom columns for the CSV export.
	 *
	 * @param array $cols An array of existing columns for the CSV export.
	 *
	 * @return array An updated array of columns with the custom columns added.
	 */
	public function register_csv_columns( array $cols ): array {
		foreach ( $this->columns as $key => $column ) {
			$cols[ $key ] = $column['label'] ?? '';
		}

		return $cols;
	}

	/**
	 * Filter the CSV data to add custom columns.
	 *
	 * @param array $data The data for the CSV export.
	 *
	 * @return array The modified data for the CSV export.
	 */
	public function filter_csv_data( array $data ): array {
		foreach ( $data as $key => $row ) {
			$id = $this->get_row_id( $row );
			foreach ( $this->columns as $column_key => $column ) {
				if ( isset( $column['callback'] ) && is_callable( $column['callback'] ) ) {
					$data[ $key ][ $column_key ] = call_user_func( $column['callback'], $id );
				}
			}
		}

		return $data;
	}

	/**
	 * Get the ID value from a row.
	 *
	 * @param array $row The row data.
	 *
	 * @return mixed The ID value.
	 */
	private function get_row_id( array $row ) {
		if ( isset( $row[ $this->id_field ] ) ) {
			return $row[ $this->id_field ];
		}

		// Check for 'ID' if the specified field doesn't exist
		if ( $this->id_field !== 'ID' && isset( $row['ID'] ) ) {
			return $row['ID'];
		}

		// Check for 'id' if neither specified field nor 'ID' exist
		if ( $this->id_field !== 'id' && isset( $row['id'] ) ) {
			return $row['id'];
		}

		// If no ID field is found, return null or throw an exception
		return null;
	}

	/**
	 * Setup the necessary hooks for custom CSV columns.
	 */
	private function setup_hooks(): void {
		add_filter( "edd_export_csv_cols_{$this->type}", [ $this, 'register_csv_columns' ] );
		add_filter( "edd_export_get_data_{$this->type}", [ $this, 'filter_csv_data' ] );
	}

	/**
	 * Register custom columns for CSV export.
	 *
	 * @param string $type     The type of CSV export (e.g., 'downloads', 'customers', etc.).
	 * @param array  $columns  An associative array of custom columns with their configurations.
	 * @param string $id_field The field to use as the identifier (default: 'ID').
	 *
	 * @return Columns
	 * @throws Exception
	 */
	public static function register( string $type, array $columns, string $id_field = 'ID' ): Columns {
		return new self( $type, $columns, $id_field );
	}

}