<?php
/**
 * Review Operations Trait for Easy Digital Downloads (EDD)
 *
 * Provides review and rating functionality for EDD downloads.
 *
 * @package       ArrayPress\EDD\Traits\Download
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Download;

use EDD_Download;

trait Reviews {
	Use Core;

	/**
	 * Get reviews for a download.
	 *
	 * @param int|null $download_id Optional. Download ID. Will use current post ID if not provided.
	 * @param array    $args        Optional. An array of arguments for review retrieval.
	 *
	 * @return array Array of review comment objects
	 */
	public static function get_reviews( int $download_id = 0, array $args = [] ): array {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return [];
		}

		$default_args = [
			'post_id' => $download->ID,
			'status'  => 'approve',
			'type'    => 'edd_review'
		];

		$args = wp_parse_args( $args, $default_args );

		// If count is requested, handle separately
		if ( ! empty( $args['count'] ) ) {
			return [];
		}

		return (array) get_comments( $args );
	}

	/**
	 * Get the number of reviews for a download.
	 *
	 * @param int|null $download_id Optional. Download ID. Will use current post ID if not provided.
	 * @param array    $args        Optional. An array of arguments for review counting.
	 *
	 * @return int Number of reviews
	 */
	public static function get_review_count( int $download_id = 0, array $args = [] ): int {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return 0;
		}

		$args = array_merge( $args, [
			'post_id' => $download->ID,
			'status'  => 'approve',
			'type'    => 'edd_review',
			'count'   => true
		] );

		return (int) get_comments( $args );
	}

	/**
	 * Get the average rating for a download.
	 *
	 * @param int|null $download_id Optional. Download ID. Will use current post ID if not provided.
	 *
	 * @return float Average rating between 0 and 5, or 0 if no ratings
	 */
	public static function get_average_rating( int $download_id = 0 ): float {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return 0.0;
		}

		$reviews = self::get_reviews( $download->ID, [
			'meta_key' => 'rating',
			'status'   => 'approve'
		] );

		if ( empty( $reviews ) ) {
			return 0.0;
		}

		$ratings = array_map( function ( $review ) {
			return (float) get_comment_meta( $review->comment_ID, 'rating', true );
		}, $reviews );

		return round( array_sum( $ratings ) / count( $ratings ), 2 );
	}

	/**
	 * Get rating counts by star level for a download.
	 *
	 * @param int|null $download_id Optional. Download ID. Will use current post ID if not provided.
	 *
	 * @return array Array of counts indexed by rating (1-5)
	 */
	public static function get_rating_counts( int $download_id = 0 ): array {
		$download = self::get_validated( $download_id );
		if ( ! $download ) {
			return array_fill( 1, 5, 0 );
		}

		$reviews = self::get_reviews( $download->ID, [
			'meta_key' => 'rating',
			'status'   => 'approve'
		] );

		$counts = array_fill( 1, 5, 0 );

		foreach ( $reviews as $review ) {
			$rating = (int) get_comment_meta( $review->comment_ID, 'rating', true );
			if ( $rating >= 1 && $rating <= 5 ) {
				$counts[ $rating ] ++;
			}
		}

		return $counts;
	}

	/**
	 * Get the rating distribution percentages for a download.
	 *
	 * @param int|null $download_id Optional. Download ID. Will use current post ID if not provided.
	 *
	 * @return array Array of percentages indexed by rating (1-5)
	 */
	public static function get_rating_percentages( int $download_id = 0 ): array {
		$counts = self::get_rating_counts( $download_id );
		$total  = array_sum( $counts );

		if ( $total === 0 ) {
			return array_fill( 1, 5, 0 );
		}

		return array_map( function ( $count ) use ( $total ) {
			return round( ( $count / $total ) * 100, 2 );
		}, $counts );
	}

}
