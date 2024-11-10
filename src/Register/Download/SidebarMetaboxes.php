<?php
/**
 * Sidebar Metaboxes Registry for Easy Digital Downloads
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Register\Download;

use ArrayPress\Utils\Elements\Element;

class SidebarMetaboxes {

	/**
	 * Registered metaboxes.
	 *
	 * @var array
	 */
	private array $metaboxes = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta_fields' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_filter( 'edd_metabox_fields_save', [ $this, 'register_save_meta_fields' ] );
		add_action( 'admin_footer', [ $this, 'add_inline_css' ] );
	}

	/**
	 * Add a new metabox.
	 *
	 * @param string $id    Metabox identifier.
	 * @param string $title Metabox title.
	 * @param array  $args  Additional arguments.
	 *
	 * @return void
	 */
	public function add_metabox( string $id, string $title, array $args = [] ): void {
		$defaults = [
			'post_type' => 'download',
			'priority'  => 'default',
			'sections'  => [],
		];

		$this->metaboxes[ $id ]          = wp_parse_args( $args, $defaults );
		$this->metaboxes[ $id ]['title'] = $title;
	}

	/**
	 * Add a new section to a metabox.
	 *
	 * @param string $metabox_id Metabox identifier.
	 * @param string $id         Section identifier.
	 * @param string $title      Section title.
	 * @param array  $args       Additional arguments.
	 *
	 * @return void
	 */
	public function add_section( string $metabox_id, string $id, string $title, array $args = [] ): void {
		if ( ! isset( $this->metaboxes[ $metabox_id ] ) ) {
			return;
		}

		$defaults = [
			'title'       => $title,
			'description' => '',
			'fields'      => [],
		];

		$this->metaboxes[ $metabox_id ]['sections'][ $id ] = wp_parse_args( $args, $defaults );
	}

	/**
	 * Add a field to a section.
	 *
	 * @param string $metabox_id Metabox identifier.
	 * @param string $section_id Section identifier.
	 * @param string $id         Field identifier.
	 * @param array  $args       Field arguments.
	 *
	 * @return void
	 */
	public function add_field( string $metabox_id, string $section_id, string $id, array $args ): void {
		if ( ! isset( $this->metaboxes[ $metabox_id ]['sections'][ $section_id ] ) ) {
			return;
		}

		$defaults = [
			'name'              => '',
			'label'             => '',
			'description'       => '',
			'type'              => 'text',
			'default'           => '',
			'options'           => [],
			'class'             => '',
			'tooltip'           => '',
			'data'              => [],
			'required'          => false,
			'side_text'         => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
		];

		$this->metaboxes[ $metabox_id ]['sections'][ $section_id ]['fields'][ $id ] = wp_parse_args( $args, $defaults );
	}

	/**
	 * Register meta fields.
	 *
	 * @return void
	 */
	public function register_meta_fields(): void {
		foreach ( $this->metaboxes as $metabox ) {
			foreach ( $metabox['sections'] as $section ) {
				foreach ( $section['fields'] as $field_id => $field ) {
					$this->register_meta_field( $field_id, $field );
				}
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
	 * Add meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		foreach ( $this->metaboxes as $id => $metabox ) {
			add_meta_box(
				$id,
				$metabox['title'],
				[ $this, 'render_metabox' ],
				$metabox['post_type'],
				'side',
				$metabox['priority'],
				[ 'metabox_id' => $id ]
			);
		}
	}

	/**
	 * Render a metabox.
	 *
	 * @param \WP_Post $post    The post object.
	 * @param array    $metabox The metabox arguments.
	 *
	 * @return void
	 */
	public function render_metabox( $post, $metabox ): void {
		$metabox_id   = $metabox['args']['metabox_id'];
		$metabox_data = $this->metaboxes[ $metabox_id ];

		foreach ( $metabox_data['sections'] as $section_id => $section ) {
			$this->render_section( $section_id, $section, $post->ID );
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
		$value = get_post_meta( $post_id, $field_id, true );
		if ( empty( $value ) && $field['default'] !== '' ) {
			$value = $field['default'];
		}

		$common_args = [
			'id'       => $field_id,
			'name'     => $field_id,
			'value'    => $value,
			'class'    => 'edd-form-group__input ' . ( $field['class'] ?? '' ),
			'required' => $field['required'] ?? false,
		];

		// Handle data attributes separately
		if ( ! empty( $field['data'] ) && is_array( $field['data'] ) ) {
			foreach ( $field['data'] as $data_key => $data_value ) {
				$common_args[ 'data-' . $data_key ] = $data_value;
			}
		}

		$select_args = array_merge( $common_args, [
			'options'          => $field['options'] ?? [],
			'show_option_all'  => false,
			'show_option_none' => false,
			'selected'         => $value,
		] );

		?>
        <div class="edd-form-group__control">
			<?php if ( $field['type'] !== 'checkbox' ) : ?>
                <label for="<?php echo esc_attr( $field_id ); ?>" class="edd-form-group__label">
					<?php echo esc_html( $field['label'] ); ?>
                </label>
			<?php endif; ?>

			<?php
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
					$checkbox_args = array_merge( $common_args, [ 'current' => $value ] );
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
				default:
					echo \EDD()->html->text( $common_args );
					break;
			}

			if ( ! empty( $field['side_text'] ) ) {
				echo ' <span class="edd-field-side-text">' . esc_html( $field['side_text'] ) . '</span>';
			}
			?>

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
		foreach ( $this->metaboxes as $metabox ) {
			foreach ( $metabox['sections'] as $section ) {
				$fields = array_merge( $fields, array_keys( $section['fields'] ) );
			}
		}

		return $fields;
	}

	/**
	 * Add inline CSS for the edd-product-options__title class.
	 *
	 * @return void
	 */
	public function add_inline_css(): void {
		?>
        <style type="text/css">
            .edd-product-options__title {
                border-top: 1px solid #c3c4c7;
                border-bottom: 1px solid #c3c4c7;
                background-color: #f9f9f9;
                display: flex;
                font-weight: 600;
                margin: 0 -12px 16px;
                padding: 8px 12px;
                justify-content: space-between;
                align-items: center;
            }
        </style>
		<?php
	}

}