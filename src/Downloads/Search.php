<?php
/**
 * Download Search Utility Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Downloads;

use ArrayPress\Utils\Common\Sanitize;
use WP_Query;
use function add_filter;
use function apply_filters;
use function current_user_can;
use function edd_get_download_type;
use function edd_get_variable_prices;
use function esc_html;
use function explode;
use function get_posts;
use function implode;
use function preg_match;
use function remove_filter;
use function strlen;
use function trim;
use function wp_list_pluck;

class Search {

	/**
	 * @var bool Indicates whether to exclude bundles from the search results.
	 */
	private bool $no_bundles;

	/**
	 * @var bool Indicates whether to include variations in the search results.
	 */
	private bool $variations;

	/**
	 * @var bool Indicates whether to include only variations in the search results.
	 */
	private bool $variations_only;

	/**
	 * @var array List of IDs to exclude from the search results.
	 */
	private array $excludes;

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
	 * Constructor for the Downloads class.
	 *
	 * @param bool   $no_bundles      Indicates whether to exclude bundles from the search results. Default is false.
	 * @param bool   $variations      Indicates whether to include variations in the search results. Default is false.
	 * @param bool   $variations_only Indicates whether to include only variations in the search results. Default is
	 *                                false.
	 * @param array  $excludes        List of IDs to exclude from the search results. Default is an empty array.
	 * @param array  $status          List of post statuses to include in the search. Default is determined by user
	 *                                capabilities.
	 * @param int    $number          Number of customers to retrieve. Default is 30.
	 * @param string $orderby         The field to order the results by. Default is 'title'.
	 * @param string $order           The order direction of the results. Default is 'ASC'.
	 */
	public function __construct( bool $no_bundles = false, bool $variations = false, bool $variations_only = false, array $excludes = [], array $status = [], int $number = 30, string $orderby = 'title', string $order = 'ASC' ) {
		$this->no_bundles      = $no_bundles;
		$this->variations      = $variations;
		$this->variations_only = $variations_only;
		$this->excludes        = $excludes;
		$this->status          = ! empty( $status ) ? $status : (
		! current_user_can( 'edit_products' )
			? apply_filters( 'edd_product_dropdown_status_nopriv', [ 'publish' ] )
			: apply_filters( 'edd_product_dropdown_status', [ 'publish', 'draft', 'private', 'future' ] )
		);
		$this->number          = $number;
		$this->orderby         = $orderby;
		$this->order           = $order;
	}

	/**
	 * Set whether to exclude bundles from the search results.
	 *
	 * @param bool $no_bundles Indicates whether to exclude bundles.
	 */
	public function set_no_bundles( bool $no_bundles ): void {
		$this->no_bundles = $no_bundles;
	}

	/**
	 * Set whether to include variations in the search results.
	 *
	 * @param bool $variations Indicates whether to include variations.
	 */
	public function set_variations( bool $variations ): void {
		$this->variations = $variations;
	}

	/**
	 * Set whether to include only variations in the search results.
	 *
	 * @param bool $variations_only Indicates whether to include only variations.
	 */
	public function set_variations_only( bool $variations_only ): void {
		$this->variations_only = $variations_only;
	}

	/**
	 * Set the list of IDs to exclude from the search results.
	 *
	 * @param array $excludes List of IDs to exclude.
	 */
	public function set_excludes( array $excludes ): void {
		$this->excludes = $excludes;
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
	 * Perform a search for downloads.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return download objects. Default is false.
	 *
	 * @return array An array of formatted search results or post objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'orderby'          => $this->orderby,
			'order'            => $this->order,
			'post_type'        => 'download',
			'posts_per_page'   => $this->number,
			'post_status'      => implode( ',', $this->status ),
			'post__not_in'     => $this->excludes,
			'edd_search'       => $search,
			'suppress_filters' => false,
		] );

		$posts = $this->get_posts( $args );

		return $return_objects ? $posts : $this->format_results( $posts );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $posts Array of download objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $posts ): array {
		// Check if posts are available.
		if ( empty( $posts ) ) {
			return [];
		}

		$options = [];

		$posts = wp_list_pluck( $posts, 'post_title', 'ID' );

		// Loop through all items...
		foreach ( $posts as $post_id => $title ) {

			// Skip bundles if we're excluding them.
			if ( $this->no_bundles && 'bundle' === edd_get_download_type( $post_id ) ) {
				continue;
			}
			$product_title = $title;

			// Look for variable pricing.
			$prices = edd_get_variable_prices( $post_id );

			if ( ! empty( $prices ) && ( ! $this->variations || ! $this->variations_only ) ) {
				$title .= ' (' . __( 'All Price Options', 'arraypress-conditions' ) . ')';
			}

			if ( empty( $prices ) || ! $this->variations_only ) {
				$options[] = [
					'value' => esc_attr( $post_id ),
					'label' => esc_html( $title ),
				];
			}

			// Maybe include variable pricing.
			if ( $this->variations && ! empty( $prices ) ) {
				foreach ( $prices as $key => $value ) {
					$name = ! empty( $value['name'] ) ? $value['name'] : '';

					if ( ! empty( $name ) ) {
						$options[] = [
							'value' => esc_attr( $post_id . '_' . $key ),
							'label' => esc_html( $product_title . ': ' . $name ),
						];
					}
				}
			}
		}

		return $options;
	}

	/**
	 * Gets the posts.
	 *
	 * @param array $args The array of arguments for WP_Query.
	 *
	 * @return array
	 */
	private function get_posts( array $args ): array {
		add_filter( 'posts_where', [ $this, 'filter_where' ], 10, 2 );
		$posts = get_posts( $args );
		remove_filter( 'posts_where', [ $this, 'filter_where' ], 10, 2 );

		return $posts;
	}

	/**
	 * Filters the WHERE SQL query for the edd_download_search.
	 * This searches the download titles only, not the excerpt/content.
	 *
	 * @param string   $where
	 * @param WP_Query $wp_query
	 *
	 * @return string
	 */
	public function filter_where( string $where, WP_Query $wp_query ): string {
		$search = $wp_query->get( 'edd_search' );
		if ( ! $search ) {
			return $where;
		}

		$terms = $this->parse_search_terms( $search );
		if ( empty( $terms ) ) {
			return $where;
		}

		global $wpdb;
		$query = '';
		foreach ( $terms as $term ) {
			$operator = empty( $query ) ? '' : ' AND ';
			$term     = $wpdb->esc_like( $term );
			$query    .= "{$operator}{$wpdb->posts}.post_title LIKE '%{$term}%'";
		}
		if ( $query ) {
			$where .= " AND ({$query})";
		}

		return $where;
	}

	/**
	 * Parses the search terms to allow for a "fuzzy" search.
	 *
	 * @param string $search
	 *
	 * @return array
	 */
	protected function parse_search_terms( string $search ): array {
		$terms   = explode( ' ', $search );
		$checked = [];

		foreach ( $terms as $term ) {
			// Keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}

}
