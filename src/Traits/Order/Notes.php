<?php
/**
 * Notes Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * This trait provides note-related operations for order records.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Order;

use EDD\Orders\Order;

trait Notes {

	/**
	 * Get and validate an order object.
	 *
	 * @param int $order_id Order ID
	 *
	 * @return Order|null Order object or null if invalid
	 */
	abstract protected static function get_validated( int $order_id = 0 ): ?Order;

	/**
	 * Adds a note for a specified order.
	 *
	 * @param int    $order_id The order ID to which the note will be added.
	 * @param string $note     The content of the note to add.
	 *
	 * @return int|false ID of newly created note, false on error.
     */
	public static function add_note( int $order_id, string $note ) {
		$order = self::get_validated( $order_id );
		if ( ! $order ) {
			return null;
		}

		return edd_add_note( [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'content'     => $note,
		] );
	}

	/**
	 * Get notes for an order with pagination support.
	 *
	 * @param int $order_id The ID of the order.
	 * @param int $length   The number of notes to get. Default 20.
	 * @param int $paged    Which page of notes to get. Default 1.
	 *
	 * @return array An array of order notes.
	 */
	public static function get_notes( int $order_id, int $length = 20, int $paged = 1 ): array {
		$order = self::get_validated( $order_id );
		if ( ! $order ) {
			return [];
		}

		return edd_get_notes( [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'number'      => $length,
			'paged'       => $paged
		] );
	}

	/**
	 * Get the total number of notes for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return int The number of notes for the order.
	 */
	public static function get_notes_count( int $order_id ): int {
		$order = self::get_validated( $order_id );
		if ( ! $order ) {
			return 0;
		}

		return edd_count_notes( [
			'object_id'   => $order_id,
			'object_type' => 'order'
		] );
	}

	/**
	 * Search for specific notes by content for an order.
	 *
	 * @param int    $order_id The ID of the order.
	 * @param string $search   The text to search for in notes.
	 * @param int    $length   Optional. The number of notes to get. Default 20.
	 * @param int    $paged    Optional. Which page of notes to get. Default 1.
	 *
	 * @return array Array of notes that match the search criteria.
	 */
	public static function get_notes_by( int $order_id, string $search, int $length = 20, int $paged = 1 ): array {
		$order = self::get_validated( $order_id );
		if ( ! $order || empty( $search ) ) {
			return [];
		}

		return edd_get_notes( [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'search'      => $search,
			'number'      => $length,
			'paged'       => $paged
		] );
	}

	/**
	 * Get the most recent note for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return object|null The most recent note object or null if no notes exist.
	 */
	public static function get_latest_note( int $order_id ): ?object {
		$notes = self::get_notes( $order_id, 1 );

		return ! empty( $notes ) ? reset( $notes ) : null;
	}

	/**
	 * Delete all notes for an order.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if notes were deleted successfully, false otherwise.
	 */
	public static function delete_all_notes( int $order_id ): bool {
		$order = self::get_validated( $order_id );
		if ( ! $order ) {
			return false;
		}

		$notes = edd_get_notes( [
			'object_id'   => $order_id,
			'object_type' => 'order',
			'number'      => - 1
		] );

		$success = true;
		foreach ( $notes as $note ) {
			if ( ! edd_delete_note( $note->id ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Check if an order has any notes.
	 *
	 * @param int $order_id The ID of the order.
	 *
	 * @return bool True if the order has notes, false otherwise.
	 */
	public static function has_notes( int $order_id ): bool {
		return self::get_notes_count( $order_id ) > 0;
	}

}