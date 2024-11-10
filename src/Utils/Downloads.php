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

use ArrayPress\EDD\Downloads\Search;

if ( ! function_exists( 'search_downloads' ) ) {
	/**
	 * Get EDD downloads based on provided arguments.
	 *
	 * @param array $args Arguments for download query.
	 *
	 * @return array Array of download objects or formatted search results.
	 */
	function search_downloads( array $args = [] ): array {
		// Default arguments
		$defaults = [
			'status'          => [],
			'number'          => 30,
			'orderby'         => 'title',
			'order'           => 'ASC',
			's'               => '', // Search parameter
			'no_bundles'      => false,
			'variations'      => false,
			'variations_only' => false,
			'excludes'        => [],
			'return_objects'  => true, // Whether to return download objects or formatted results
		];

		$args = wp_parse_args( $args, $defaults );

		// Extract and remove custom parameters
		$search          = $args['s'] ?? '';
		$return_objects  = $args['return_objects'];
		$no_bundles      = $args['no_bundles'];
		$variations      = $args['variations'];
		$variations_only = $args['variations_only'];
		$excludes        = $args['excludes'];
		unset( $args['s'], $args['return_objects'], $args['no_bundles'], $args['variations'], $args['variations_only'], $args['excludes'] );

		// Initialize Downloads class
		$search_query = new Search(
			$no_bundles,
			$variations,
			$variations_only,
			$excludes,
			$args['status'],
			$args['number'],
			$args['orderby'],
			$args['order']
		);

		// Always use get_results, even if search is empty
		return $search_query->get_results( $search, $args, $return_objects );
	}
}