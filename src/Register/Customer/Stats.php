<?php
/**
 * Customer Stats Registry for Easy Digital Downloads
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.9
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Customer;

use EDD_Customer;
use ArrayPress\Utils\Common\Str;

class Stats {
	/**
	 * Registered stats items.
	 *
	 * @var array
	 */
	private array $items = [];

	/**
	 * Register a new stats item.
	 *
	 * @param string $key  Unique identifier for the stats item.
	 * @param array  $args Configuration arguments for the stats item.
	 *
	 * @return void
	 */
	public function register( string $key, array $args ): void {
		$defaults = [
			'label'      => '',
			'callback'   => '',
			'icon'       => 'dashicons-info',
			'url'        => '',
			'singular'   => '',
			'plural'     => '',
			'priority'   => 10,
			'span_class' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		// Generate singular and plural if not provided
		if ( empty( $args['singular'] ) ) {
			$args['singular'] = $this->generate_singular( $key );
		}
		if ( empty( $args['plural'] ) ) {
			$args['plural'] = $this->generate_plural( $args['singular'] );
		}

		$this->items[ $key ] = $args;
	}

	/**
	 * Render the stats list for a given customer.
	 *
	 * @param EDD_Customer $customer The customer object.
	 *
	 * @return void
	 */
	public function render( EDD_Customer $customer ): void {
		if ( empty( $this->items ) ) {
			return;
		}
		uasort( $this->items, fn( $a, $b ) => $a['priority'] <=> $b['priority'] );
		foreach ( $this->items as $key => $item ) {
			$this->render_item( $key, $item, $customer );
		}
	}

	/**
	 * Setup hooks to integrate with EDD.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_action( 'edd_customer_stats_list', [ $this, 'render' ] );
	}

	/**
	 * Render a single stats item.
	 *
	 * @param string       $key      The item key.
	 * @param array        $item     The item data.
	 * @param EDD_Customer $customer The customer object.
	 *
	 * @return void
	 */
	private function render_item( string $key, array $item, EDD_Customer $customer ): void {
		if ( ! is_callable( $item['callback'] ) ) {
			return;
		}
		$value       = call_user_func( $item['callback'], $customer );
		$label       = $value === 1 ? $item['singular'] : $item['plural'];
		$value_class = 'edd_' . strtolower( sanitize_html_class( $item['singular'] ) );

		if ( ! empty( $item['span_class'] ) ) {
			$value_class .= ' ' . sanitize_html_class( $item['span_class'] );
		}

		?>
        <li>
			<?php if ( ! empty( $item['url'] ) ) : ?>
            <a href="<?php echo esc_url( $item['url'] . $customer->id ); ?>">
				<?php endif; ?>
                <span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
                <span class="<?php echo esc_attr( $value_class ); ?>"><?php echo esc_html( $value ); ?></span>
				<?php echo esc_html( $label ); ?>
				<?php if ( ! empty( $item['url'] ) ) : ?>
            </a>
		<?php endif; ?>
        </li>
		<?php
	}


	/**
	 * Generate singular form from key.
	 *
	 * @param string $key The key to generate singular form from.
	 *
	 * @return string
	 */
	private function generate_singular( string $key ): string {
		return ucfirst( Str::singularize( $key ) );
	}

	/**
	 * Generate plural form from singular.
	 *
	 * @param string $singular The singular form to generate plural from.
	 *
	 * @return string
	 */
	private function generate_plural( string $singular ): string {
		return Str::pluralize( $singular );
	}
}