<?php
/**
 * Variable Price Options Registry for Easy Digital Downloads
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.3
 * @author        Your Name
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Download;

use ArrayPress\Utils\Elements\Element;

class VariablePriceOptions {

	/**
	 * Registered sections for variable price options.
	 *
	 * @var array
	 */
	private array $sections = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'edd_download_price_option_row', [ $this, 'render_sections' ], 800, 3 );
		add_filter( 'edd_metabox_save_edd_variable_prices', [ $this, 'sanitize_variable_prices' ], - 999 );
	}

	/**
	 * Add a new section.
	 *
	 * @param string $id    Section identifier.
	 * @param string $title Section title.
	 * @param array  $args  Additional arguments.
	 *
	 * @return void
	 */
	public function add_section( string $id, string $title, array $args = [] ): void {
		$defaults = [
			'title'    => $title,
			'fields'   => [],
			'priority' => 10,
		];

		$this->sections[ $id ] = wp_parse_args( $args, $defaults );
	}

	/**
	 * Add a field to a section.
	 *
	 * @param string $section Section identifier.
	 * @param string $id      Field identifier.
	 * @param array  $args    Field arguments.
	 *
	 * @return void
	 */
	public function add_field( string $section, string $id, array $args ): void {
		if ( ! isset( $this->sections[ $section ] ) ) {
			return;
		}

		$defaults = [
			'name'              => '',
			'label'             => '',
			'type'              => 'text',
			'default'           => '',
			'options'           => [],
			'class'             => '',
			'tooltip'           => '',
			'data'              => [],
			'prefix'            => '',
			'suffix'            => '',
			'sanitize_callback' => 'sanitize_text_field',
		];

		$args = wp_parse_args( $args, $defaults );

		// Forcibly unset 'required' and 'description' if they were passed
		unset( $args['required'], $args['description'] );

		$this->sections[ $section ]['fields'][ $id ] = $args;
	}

	/**
	 * Render all registered sections and their fields.
	 *
	 * @param int   $download_id Download ID.
	 * @param int   $price_id    Price ID.
	 * @param array $args        Custom parameters.
	 *
	 * @return void
	 */
	public function render_sections( int $download_id, int $price_id, array $args ): void {
		foreach ( $this->sections as $section_id => $section ) {
			$this->render_section( $section_id, $section, $download_id, $price_id );
		}
	}

	/**
	 * Render a single section and its fields.
	 *
	 * @param string $section_id  Section identifier.
	 * @param array  $section     Section data.
	 * @param int    $download_id Download ID.
	 * @param int    $price_id    Price ID.
	 *
	 * @return void
	 */
	private function render_section( string $section_id, array $section, int $download_id, int $price_id ): void {
		?>
        <div class="edd-custom-price-option-section edd-custom-price-option-section--<?php echo esc_attr( $section_id ); ?>">
            <span class="edd-custom-price-option-section-title"><?php echo esc_html( $section['title'] ); ?></span>
            <div class="edd-custom-price-option-section-content edd-form-row edd-form-row__settings">
				<?php
				foreach ( $section['fields'] as $field_id => $field ) {
					$this->render_field( $field_id, $field, $download_id, $price_id );
				}
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * Render a single field for variable price options.
	 *
	 * @param string $field_id    Field identifier.
	 * @param array  $field       Field data.
	 * @param int    $download_id Download ID.
	 * @param int    $price_id    Price ID.
	 *
	 * @return void
	 */
	private function render_field( string $field_id, array $field, int $download_id, int $price_id ): void {
		$value = $this->get_field_value( $download_id, $price_id, $field_id, $field['default'] );
		$name  = "edd_variable_prices[{$price_id}][{$field_id}]";
		$id    = "edd_variable_prices_{$price_id}_{$field_id}";

		$common_args = [
			'id'    => $id,
			'name'  => $name,
			'value' => $value,
			'class' => 'edd-form-group__input ' . $field['class'],
		];

		if ( ! empty( $field['data'] ) ) {
			$common_args['data'] = $field['data'];
		}

		?>
        <div class="edd-form-group edd-form-row__column">
			<?php if ( $field['type'] !== 'checkbox' ) : ?>
                <label for="<?php echo esc_attr( $id ); ?>" class="edd-form-group__label">
					<?php echo esc_html( $field['label'] ); ?>
					<?php
					if ( ! empty( $field['tooltip'] ) ) {
						$tooltip = new \EDD\HTML\Tooltip( [
							'title'   => $field['label'],
							'content' => $field['tooltip'],
						] );
						$tooltip->output();
					}
					?>
                </label>
			<?php endif; ?>

            <div class="edd-form-group__control">
				<?php if ( ! empty( $field['prefix'] ) ) : ?>
                    <span class="edd-field-prefix"><?php echo esc_html( $field['prefix'] ); ?></span>
				<?php endif; ?>

				<?php
				$select_args = array_merge( $common_args, [
					'options'          => $field['options'],
					'show_option_all'  => false,
					'show_option_none' => false,
					'selected'         => $value,
				] );

				switch ( $field['type'] ) {
					case 'text':
					case 'email':
						echo \EDD()->html->text( $common_args );
						break;
					case 'number':
						echo Element::input( 'number', $common_args );
						break;
					case 'textarea':
						echo \EDD()->html->textarea( $common_args );
						break;
					case 'color':
						echo Element::input( 'color', $common_args );
						break;
					case 'select':
						echo \EDD()->html->select( $select_args );
						break;
					case 'checkbox':
						$checkbox_args = array_merge( $common_args, [
							'current' => $value,
						] );
						echo \EDD()->html->checkbox( $checkbox_args );
						echo '<label class="edd-form-group__label" for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
						break;
				}

				if ( ! empty( $field['suffix'] ) ) : ?>
                    <span class="edd-field-suffix"><?php echo esc_html( $field['suffix'] ); ?></span>
				<?php endif; ?>
            </div>
        </div>
		<?php
	}

	/**
	 * Get the value of a field for a specific download and price option.
	 *
	 * @param int    $download_id Download ID.
	 * @param int    $price_id    Price ID.
	 * @param string $field_id    Field identifier.
	 * @param mixed  $default     Default value.
	 *
	 * @return mixed
	 */
	private function get_field_value( int $download_id, int $price_id, string $field_id, $default ) {
		$prices = edd_get_variable_prices( $download_id );

		return $prices[ $price_id ][ $field_id ] ?? $default;
	}

	/**
	 * Sanitize variable price values.
	 *
	 * @param array $prices Variable prices array.
	 *
	 * @return array
	 */
	public function sanitize_variable_prices( array $prices ): array {
		foreach ( $prices as $price_id => $price ) {
			foreach ( $this->sections as $section ) {
				foreach ( $section['fields'] as $field_id => $field ) {
					if ( isset( $price[ $field_id ] ) ) {
						$sanitize_callback                = $field['sanitize_callback'] ?? 'sanitize_text_field';
						$prices[ $price_id ][ $field_id ] = call_user_func( $sanitize_callback, $price[ $field_id ] );
					}
				}
			}
		}

		return $prices;
	}
}