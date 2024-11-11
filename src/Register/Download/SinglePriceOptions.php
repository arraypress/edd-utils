<?php
/**
 * Single Price Options Registry for Easy Digital Downloads
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.2
 * @author        Your Name
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Download;

use ArrayPress\Utils\Elements\Element;

class SinglePriceOptions {

	/**
	 * Registered settings sections.
	 *
	 * @var array
	 */
	private array $sections = [];

	/**
	 * Flag to check if JS has been added.
	 *
	 * @var bool
	 */
	private static bool $js_added = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta_fields' ] );
		add_action( 'edd_after_price_field', [ $this, 'render_sections' ], 10 );
		add_filter( 'edd_metabox_fields_save', [ $this, 'register_save_meta_fields' ] );
		add_action( 'admin_footer', [ $this, 'add_admin_scripts' ] );
	}

	/**
	 * Add a new settings section.
	 *
	 * @param string $id   Section identifier.
	 * @param array  $args Additional arguments.
	 *
	 * @return void
	 */
	public function add_section( string $id, array $args = [] ): void {
		$defaults = [
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
			'name'                => '',
			'label'               => '',
			'description'         => '',
			'type'                => 'text',
			'default'             => '',
			'options'             => [],
			'class'               => '',
			'tooltip'             => '',
			'data'                => [],
			'prefix'              => '',
			'suffix'              => '',
			'permission_callback' => null,
			'sanitize_callback'   => 'sanitize_text_field',
			'show_in_rest'        => false,
		];

		$this->sections[ $section ]['fields'][ $id ] = wp_parse_args( $args, $defaults );
	}

	/**
	 * Register meta fields.
	 *
	 * @return void
	 */
	public function register_meta_fields(): void {
		foreach ( $this->sections as $section ) {
			foreach ( $section['fields'] as $field_id => $field ) {
				$this->register_meta_field( $field_id, $field );
			}
		}
	}

	/**
	 * Register a single meta field.
	 *
	 * @param string $field_id Field identifier.
	 * @param array  $field    Field data.
	 *
	 * @return void
	 */
	private function register_meta_field( string $field_id, array $field ): void {
		$args = [
			'object_subtype'    => 'download',
			'type'              => $field['type'],
			'description'       => $field['description'],
			'single'            => true,
			'sanitize_callback' => $field['sanitize_callback'],
			'auth_callback'     => $field['permission_callback'] ?? null,
			'show_in_rest'      => $field['show_in_rest'],
		];

		register_meta( 'post', $field_id, $args );
	}

	/**
	 * Render all registered sections and their fields.
	 *
	 * @param int $post_id Download (Post) ID.
	 *
	 * @return void
	 */
	public function render_sections( int $post_id ): void {
		?>
        <div id="edd-custom-single-price-options" class="edd-form-row edd-custom-single">
			<?php
			foreach ( $this->sections as $section_id => $section ) {
				$this->render_section( $section_id, $section, $post_id );
			}
			?>
        </div>
		<?php
	}

	/**
	 * Render a single section and its fields.
	 *
	 * @param string $section_id Section identifier.
	 * @param array  $section    Section data.
	 * @param int    $post_id    Download (Post) ID.
	 *
	 * @return void
	 */
	private function render_section( string $section_id, array $section, int $post_id ): void {
		?>
        <div class="edd-form-group edd-product-options-wrapper">
			<?php
			foreach ( $section['fields'] as $field_id => $field ) {
				$this->render_field( $field_id, $field, $post_id );
			}
			?>
        </div>
		<?php
	}

	/**
	 * Render a single field.
	 *
	 * @param string $field_id Field identifier.
	 * @param array  $field    Field data.
	 * @param int    $post_id  Download (Post) ID.
	 *
	 * @return void
	 */
	private function render_field( string $field_id, array $field, int $post_id ): void {
		// Check permission
		if ( ! empty( $field['permission_callback'] ) && ! call_user_func( $field['permission_callback'] ) ) {
			return;
		}

		$value = get_post_meta( $post_id, $field_id, true );
		if ( empty( $value ) && $field['default'] !== '' ) {
			$value = $field['default'];
		}

		$common_args = [
			'id'    => $field_id,
			'name'  => $field_id,
			'value' => $value,
			'class' => 'edd-form-group__input ' . $field['class'],
		];

		if ( ! empty( $field['data'] ) ) {
			$common_args['data'] = $field['data'];
		}

		?>
        <div class="edd-form-group edd-form-row__column">
			<?php if ( $field['type'] !== 'checkbox' ) : ?>
                <label for="<?php echo esc_attr( $field_id ); ?>" class="edd-form-group__label">
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
						echo '<label class="edd-form-group__label" for="' . esc_attr( $field_id ) . '">' . esc_html( $field['label'] ) . '</label>';
						break;
					case 'product_dropdown':
						echo \EDD()->html->product_dropdown( $select_args );
						break;
					case 'customer_dropdown':
						echo \EDD()->html->customer_dropdown( $select_args );
						break;
					case 'user_dropdown':
						echo \EDD()->html->user_dropdown( $select_args );
						break;
					case 'discount_dropdown':
						echo \EDD()->html->discount_dropdown( $select_args );
						break;
					case 'category_dropdown':
						echo \EDD()->html->category_dropdown( $field_id, $value );
						break;
					case 'year_dropdown':
						echo \EDD()->html->year_dropdown( $field_id, $value );
						break;
					case 'month_dropdown':
						echo \EDD()->html->month_dropdown( $field_id, $value );
						break;
					case 'country_select':
						echo \EDD()->html->country_select( $common_args, $value );
						break;
					case 'region_select':
						echo \EDD()->html->region_select( $common_args, '', $value );
						break;
					case 'date_field':
						echo \EDD()->html->date_field( $common_args );
						break;
					case 'ajax_user_search':
						echo \EDD()->html->ajax_user_search( $common_args );
						break;
				}

				if ( ! empty( $field['suffix'] ) ) : ?>
                    <span class="edd-field-suffix"><?php echo esc_html( $field['suffix'] ); ?></span>
				<?php endif; ?>
            </div>

			<?php if ( ! empty( $field['description'] ) ) : ?>
                <p class="edd-form-group__help description"><?php echo wp_kses_post( $field['description'] ); ?></p>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * Register fields to be saved.
	 *
	 * @param array $fields Current fields to be saved.
	 *
	 * @return array
	 */
	public function register_save_meta_fields( array $fields ): array {
		foreach ( $this->sections as $section ) {
			$fields = array_merge( $fields, array_keys( $section['fields'] ) );
		}

		return $fields;
	}

	/**
	 * Add admin scripts.
	 *
	 * @return void
	 */
	public function add_admin_scripts(): void {
		if ( self::$js_added ) {
			return;
		}

		?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                function toggleSinglePriceOptions() {
                    var $variablePricing = $('#edd_variable_pricing');
                    var $singlePriceOptions = $('#edd-custom-single-price-options');

                    function updateVisibility() {
                        $singlePriceOptions.toggle(!$variablePricing.is(':checked'));
                    }

                    $variablePricing.on('change', updateVisibility);
                    updateVisibility(); // Initial state
                }

                toggleSinglePriceOptions();
            });
        </script>
		<?php

		self::$js_added = true;
	}

}