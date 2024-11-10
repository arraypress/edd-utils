<?php
/**
 * Download Settings Registry for Easy Digital Downloads
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.2
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Download;

use ArrayPress\Utils\Elements\Element;

class DownloadSettings {

	/**
	 * Registered settings sections.
	 *
	 * @var array
	 */
	private array $sections = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta_fields' ] );
		add_action( 'edd_meta_box_settings_fields', [ $this, 'render_sections' ], 100 );
		add_filter( 'edd_metabox_fields_save', [ $this, 'register_save_meta_fields' ] );
	}

	/**
	 * Register a new settings section.
	 *
	 * @param string $id    Section identifier.
	 * @param string $title Section title.
	 * @param array  $args  Additional arguments.
	 *
	 * @return void
	 */
	public function add_section( string $id, string $title, array $args = [] ): void {
		$defaults = [
			'title'       => $title,
			'description' => '',
			'fields'      => [],
			'priority'    => 10,
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
			'required'            => false,
			'side_text'           => '',
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
		foreach ( $this->sections as $section_id => $section ) {
			$this->render_section( $section_id, $section, $post_id );
		}
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
            <div class="edd-product-options__title">
				<?php echo esc_html( $section['title'] ); ?>
				<?php
				if ( ! empty( $section['tooltip'] ) ) {
					$tooltip = new \EDD\HTML\Tooltip( [
						'title'   => $section['title'],
						'content' => $section['tooltip'],
					] );
					$tooltip->output();
				}
				?>
            </div>
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
			'id'       => $field_id,
			'name'     => $field_id,
			'value'    => $value,
			'class'    => 'edd-form-group__input ' . $field['class'],
			'required' => $field['required'],
		];

		if ( ! empty( $field['data'] ) ) {
			$common_args['data'] = $field['data'];
		}

		?>
        <div class="edd-form-group__control">
			<?php if ( $field['type'] !== 'checkbox' ) : ?>
                <label for="<?php echo esc_attr( $field_id ); ?>" class="edd-form-group__label">
					<?php echo esc_html( $field['label'] ); ?>
                </label>
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
					if ( ! empty( $field['side_text'] ) ) {
						echo ' <span class="edd-field-side-text">' . esc_html( $field['side_text'] ) . '</span>';
					}
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
			?>

			<?php if ( ! empty( $field['description'] ) ) : ?>
                <p class="edd-form-group__help description"><?php echo wp_kses_post( $field['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( $field['required'] ) : ?>
				<?php echo \EDD()->html->show_required(); ?>
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

}