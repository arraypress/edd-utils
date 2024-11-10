<?php
/**
 * Discount Search Utility Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Discounts;

use ArrayPress\Utils\Common\Sanitize;
use function absint;
use function esc_html;
use function wp_parse_args;

class Search {

	/**
	 * @var array List of post statuses to include in the search.
	 */
	private array $status;

	/**
	 * @var int Number of discounts to retrieve.
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
	 * Constructor for the Discounts class.
	 *
	 * @param array  $status  List of post statuses to include in the search. Default is 'active'.
	 * @param int    $number  Number of discounts to retrieve. Default is 30.
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
	 * Set the number of discounts to retrieve.
	 *
	 * @param int $number Number of discounts to retrieve.
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
	 * Retrieve EDD discount search results based on a search term and arguments.
	 *
	 * @param string $search         The search term to look for discounts.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return discount objects. Default is false.
	 *
	 * @return array An array of formatted search results or discount objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'status__in' => $this->status,
			'number'     => $this->number,
			'search'     => $search,
			'orderby'    => $this->orderby,
			'order'      => $this->order,
		] );

		$discounts = edd_get_discounts( $args );

		return $return_objects ? $discounts : $this->format_results( $discounts );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $discounts Array of discount objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $discounts ): array {
		// Check if discounts are available.
		if ( empty( $discounts ) ) {
			return [];
		}

		$options = [];

		// Format the search results with proper escaping.
		foreach ( $discounts as $discount ) {
			$options[] = [
				'value' => absint( $discount->id ),
				'label' => esc_html( $discount->name . ' (' . $discount->code . ')' )
			];
		}

		return $options;
	}

}