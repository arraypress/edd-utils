<?php
/**
 * Customer Search Utility Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Customers;

use ArrayPress\Utils\Common\Sanitize;
use EDD\Database\Queries\Customer_Email_Address;
use function absint;
use function esc_html;
use function is_email;
use function wp_parse_args;

class Search {

	/**
	 * @var array List of post statuses to include in the search.
	 */
	private array $status;

	/**
	 * @var int Number of customers to retrieve.
	 */
	private int $number;

	/**
	 * @var string The field to order the results by.
	 */
	private string $orderby;

	/**
	 * @var string The order direction of the results.
	 */
	private string $order;

	/**
	 * Constructor for the Customers class.
	 *
	 * @param array  $status  List of post statuses to include in the search. Default is 'active'.
	 * @param int    $number  Number of customers to retrieve. Default is 30.
	 * @param string $orderby The field to order the results by. Default is 'name'.
	 * @param string $order   The order direction of the results. Default is 'ASC'.
	 */
	public function __construct( array $status = [ 'active' ], int $number = 30, string $orderby = 'name', string $order = 'ASC' ) {
		$this->status  = $status;
		$this->number  = $number;
		$this->orderby = $orderby;
		$this->order   = $order;
	}

	/**
	 * Set the list of post statuses to include in the search.
	 *
	 * @param array $status List of post statuses to include.
	 */
	public function set_status( array $status ): void {
		$this->status = $status;
	}

	/**
	 * Set the number of customers to retrieve.
	 *
	 * @param int $number Number of customers to retrieve.
	 */
	public function set_number( int $number ): void {
		$this->number = $number;
	}

	/**
	 * Set the field to order the results by.
	 *
	 * @param string $orderby The field to order the results by.
	 */
	public function set_orderby( string $orderby ): void {
		$this->orderby = $orderby;
	}

	/**
	 * Set the order direction of the results.
	 *
	 * @param string $order The order direction of the results.
	 */
	public function set_order( string $order ): void {
		$this->order = $order;
	}

	/**
	 * Retrieve EDD customer search results based on a search term and arguments.
	 *
	 * @param string $search         The search term to look for customers.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return customer objects. Default is false.
	 *
	 * @return array An array of formatted search results or customer objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'status'  => $this->status,
			'number'  => $this->number,
			'orderby' => $this->orderby,
			'order'   => $this->order,
		] );

		// Account for search stripping the "+" from emails.
		if ( strpos( $search, ' ' ) ) {
			$original_query = $search;
			$search         = str_replace( ' ', '+', $search );
			if ( ! is_email( $search ) ) {
				$search = $original_query;
			}
		}

		// Email search.
		if ( is_email( $search ) ) {
			$args['email'] = $search;
		} elseif ( is_numeric( $search ) ) {
			// Customer ID.
			$args['id'] = $search;
		} elseif ( strpos( $search, 'c:' ) !== false ) {
			$args['id'] = trim( str_replace( 'c:', '', $search ) );
		} elseif ( strpos( $search, 'user:' ) !== false ) {
			// User ID.
			$args['user_id'] = trim( str_replace( 'user:', '', $search ) );
		} elseif ( strpos( $search, 'u:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'u:', '', $search ) );
		} else {
			$args['search']         = $search;
			$args['search_columns'] = [ 'name', 'email' ];
		}

		if ( is_email( $search ) ) {
			$customer_emails = new Customer_Email_Address();
			$customer_ids    = $customer_emails->query( [
				'fields' => 'customer_id',
				'email'  => $search,
			] );

			$customers = edd_get_customers( [
				'id__in' => $customer_ids,
			] );
		} else {
			$customers = edd_get_customers( $args );
		}

		return $return_objects ? $customers : $this->format_results( $customers );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $customers Array of customer objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $customers ): array {
		// Check if customers are available.
		if ( empty( $customers ) ) {
			return [];
		}

		$options = [];

		// Format the search results with proper escaping.
		foreach ( $customers as $customer ) {
			$options[] = [
				'value' => absint( $customer->id ),
				'label' => esc_html( $customer->name . ' (' . $customer->email . ')' )
			];
		}

		return $options;
	}

}