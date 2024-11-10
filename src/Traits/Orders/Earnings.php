<?php
/**
 * Earnings Operations Trait for Easy Digital Downloads (EDD) Orders
 *
 * Provides methods for calculating total and average earnings.
 *
 * @package       ArrayPress\EDD\Traits\Order
 * @since         1.0.0
 * @author        David Sherlock
 * @copyright     Copyright (c) 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Traits\Orders;

trait Earnings {

	/**
	 * Required trait method for getting EDD Stats instance.
	 *
	 * @param array $args Stats arguments
	 *
	 * @return \EDD\Stats
	 */
	abstract protected static function get_stats( array $args = [] ): \EDD\Stats;

	/**
	 * Get the total store earnings from the database or transient.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total store earnings, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_earnings( array $args = [] ): float {
		$defaults = [
			'currency'     => 'converted',
			'revenue_type' => 'gross',
			'status'       => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure output is always 'raw'
		$args['output']   = 'raw';
		$args['function'] = 'SUM';

		$stats = self::get_stats( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get the total store net earnings from the database.
	 *
	 * This function forces the revenue_type to 'net' and does not allow it to be overridden.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total store net earnings, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_net_earnings( array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the total tax amount from the database.
	 *
	 * This function forces the column to 'tax'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total tax amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_tax( array $args = [] ): float {
		$args['column'] = 'tax';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the total discount amount from the database.
	 *
	 * This function forces the column to 'discount'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The total discount amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_total_discount_amount( array $args = [] ): float {
		$args['column'] = 'discount';

		return self::get_total_earnings( $args );
	}

	/**
	 * Get the average order value from the database or transient.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average order value, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_earnings( array $args = [] ): float {
		$defaults = [
			'currency'     => 'converted',
			'revenue_type' => 'gross',
			'status'       => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure output is always 'raw'
		$args['output']   = 'raw';
		$args['function'] = 'AVG';

		$stats = self::get_stats( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get the average net order value from the database or transient.
	 *
	 * This function forces the revenue_type to 'net' and does not allow it to be overridden.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average net order value, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_net_average_earnings( array $args = [] ): float {
		$args['revenue_type'] = 'net';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average tax amount from the database or transient.
	 *
	 * This function forces the column to 'tax'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average tax amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_tax_amount( array $args = [] ): float {
		$args['column'] = 'tax';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average discount amount from the database or transient.
	 *
	 * This function forces the column to 'discount'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average discount amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_discount_amount( array $args = [] ): float {
		$args['column'] = 'discount';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get the average subtotal amount from the database or transient.
	 *
	 * This function forces the column to 'subtotal'.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average subtotal amount, or 0.0 if the EDD\Stats class does not exist.
	 */
	public static function get_average_subtotal_amount( array $args = [] ): float {
		$args['column'] = 'subtotal';

		return self::get_average_earnings( $args );
	}

	/**
	 * Get average spending per customer.
	 *
	 * @param array $args Optional. Additional arguments for the query.
	 *
	 * @return float The average amount spent per customer.
	 */
	public static function get_average_customer_spend( array $args = [] ): float {
		global $wpdb;

		$defaults = [
			'type'   => 'sale',
			'status' => edd_get_complete_order_statuses()
		];

		$args = wp_parse_args( $args, $defaults );

		// Build status IN clause
		$status_placeholder = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );

		$sql = $wpdb->prepare(
			"SELECT AVG(total_spent) as avg_spend
            FROM (
                SELECT customer_id, SUM(total) as total_spent
                FROM {$wpdb->prefix}edd_orders
                WHERE type = %s 
                AND status IN ($status_placeholder)
                AND customer_id != 0
                GROUP BY customer_id
            ) as customer_totals",
			array_merge(
				[ $args['type'] ],
				$args['status']
			)
		);

		$result = $wpdb->get_var( $sql );

		return is_null( $result ) ? 0.0 : round( (float) $result, 2 );
	}

}