<?php
/**
 * Notes Operations Trait for Easy Digital Downloads (EDD) Customers
 *
 * This trait provides note-related operations for customer records.
 *
 * @package       ArrayPress\EDD\Traits\Customer
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Customer;

use ArrayPress\EDD\Customers\Customer;

trait Notes {

	/**
	 * Check if a customer has any notes.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if the customer has notes, false otherwise.
	 */
	public static function has_notes( int $customer_id ): bool {
		return self::get_notes_count( $customer_id ) > 0;
	}

	/**
	 * Adds a note for a specified customer.
	 *
	 * @param int    $customer_id The customer's ID to whom the note will be added.
	 * @param string $note        The content of the note to add.
	 *
	 * @return bool|null True on success, false on failure, null if customer not found.
	 */
	public static function add_note( int $customer_id, string $note ): ?bool {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return null;
		}

		return $customer->add_note( $note );
	}

	/**
	 * Get notes for a customer with pagination support.
	 *
	 * @param int $customer_id The ID of the customer.
	 * @param int $length      The number of notes to get. Default 20.
	 * @param int $paged       Which page of notes to get. Default 1.
	 *
	 * @return array An array of customer notes.
	 */
	public static function get_notes( int $customer_id, int $length = 20, int $paged = 1 ): array {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return [];
		}

		return $customer->get_notes( $length, $paged );
	}

	/**
	 * Get the total number of notes for a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return int The number of notes for the customer.
	 */
	public static function get_notes_count( int $customer_id ): int {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return 0;
		}

		return $customer->get_notes_count();
	}

	/**
	 * Search for specific notes by content for a customer.
	 *
	 * @param int    $customer_id The ID of the customer.
	 * @param string $search      The text to search for in notes.
	 * @param int    $length      Optional. The number of notes to get. Default 20.
	 * @param int    $paged       Optional. Which page of notes to get. Default 1.
	 *
	 * @return array Array of notes that match the search criteria.
	 */
	public static function get_notes_by( int $customer_id, string $search, int $length = 20, int $paged = 1 ): array {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer || empty( $search ) ) {
			return [];
		}

		// Get all notes for the customer
		$notes = $customer->get_notes( $length, $paged );

		// Filter notes that contain the search string
		return array_filter( $notes, function ( $note ) use ( $search ) {
			return stripos( $note->content, $search ) !== false;
		} );
	}

	/**
	 * Get the most recent note for a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return object|null The most recent note object or null if no notes exist.
	 */
	public static function get_latest_note( int $customer_id ): ?object {
		$notes = Customer::get_notes( $customer_id, 1 );

		return ! empty( $notes ) ? reset( $notes ) : null;
	}

	/**
	 * Delete all notes for a customer.
	 *
	 * @param int $customer_id The ID of the customer.
	 *
	 * @return bool True if notes were deleted successfully, false otherwise.
	 */
	public static function delete_all_notes( int $customer_id ): bool {
		$customer = Customer::get_validated( $customer_id );
		if ( ! $customer ) {
			return false;
		}

		$notes = edd_get_notes( array(
			'object_id'   => $customer_id,
			'object_type' => 'customer',
			'number'      => - 1
		) );

		$success = true;
		foreach ( $notes as $note ) {
			if ( ! edd_delete_note( $note->id ) ) {
				$success = false;
			}
		}

		return $success;
	}

}