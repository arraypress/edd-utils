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

namespace ArrayPress\EDD;

use ArrayPress\EDD\Discounts\Search;

if ( ! function_exists( 'search_discounts' ) ) {
	/**
	 * Get EDD discounts based on provided arguments.
	 *
	 * @param array $args Arguments for discount query.
	 *
	 * @return array Array of discount objects or formatted search results.
	 */
	function search_discounts( array $args = [] ): array {
		// Default arguments
		$defaults = [
			'status'         => [ 'active' ],
			'number'         => 30,
			'orderby'        => 'name',
			'order'          => 'ASC',
			's'              => '', // Search parameter
			'return_objects' => true, // Whether to return discount objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search         = $args['s'] ?? '';
		$return_objects = $args['return_objects'];
		unset( $args['s'], $args['return_objects'] );

		// Initialize Discounts class
		$search_query = new Search(
			$args['status'],
			$args['number'],
			$args['orderby'],
			$args['order']
		);

		// Always use get_results, even if search is empty
		return $search_query->get_results( $search, $args, $return_objects );
	}
}