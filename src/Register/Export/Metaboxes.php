<?php
/**
 * Export Metaboxes Class
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

namespace ArrayPress\EDD\Register\Export;

class Metaboxes {

	/**
	 * @var array Array of registered metaboxes
	 */
	private array $metaboxes = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'edd_reports_tab_export_content_bottom', [ $this, 'render_metaboxes' ] );
	}

	/**
	 * Register a new export metabox
	 *
	 * @param array $args Metabox arguments
	 */
	public function register_metabox( array $args ) {
		$defaults = array(
			'id'           => '',
			'title'        => '',
			'description'  => '',
			'fields'       => [],
			'export_class' => '',
			'file'         => ''
		);

		$metabox                           = wp_parse_args( $args, $defaults );
		$this->metaboxes[ $metabox['id'] ] = $metabox;
	}

	/**
	 * Get all registered metaboxes
	 *
	 * @return array
	 */
	public function get_metaboxes(): array {
		return $this->metaboxes;
	}

	/**
	 * Render all registered metaboxes
	 */
	public function render_metaboxes() {
		foreach ( $this->metaboxes as $metabox ) {
			$this->render_single_metabox( $metabox );
		}
	}

	/**
	 * Render a single metabox
	 *
	 * @param array $metabox Metabox configuration
	 */
	private function render_single_metabox( array $metabox ) {
		?>
        <div class="postbox edd-export-<?php echo esc_attr( $metabox['id'] ); ?>">
            <h2 class="hndle"><span><?php echo esc_html( $metabox['title'] ); ?></span></h2>
            <div class="inside">
                <p><?php echo esc_html( $metabox['description'] ); ?></p>
                <form id="edd-export-<?php echo esc_attr( $metabox['id'] ); ?>"
                      class="edd-export-form edd-import-export-form" method="post">
					<?php
					foreach ( $metabox['fields'] as $field ) {
						$this->render_field( $field );
					}
					wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' );
					?>
                    <input type="hidden" name="edd-export-class"
                           value="<?php echo esc_attr( $metabox['export_class'] ); ?>"/>
                    <button type="submit"
                            class="button button-secondary"><?php esc_html_e( 'Generate CSV', 'arraypress' ); ?></button>
                </form>
            </div>
        </div>
		<?php
	}

	/**
	 * Render a single field
	 *
	 * @param array $field Field configuration
	 */
	private function render_field( array $field ) {
		$field = wp_parse_args( $field, array(
			'type'  => '',
			'id'    => '',
			'name'  => '',
			'label' => '',
			'class' => '',
			'desc'  => '',
		) );

		unset( $field['desc'], $field['class'] );

		if ( ! empty( $field['label'] ) ) {
			echo '<label for="' . esc_attr( $field['id'] ) . '" class="screen-reader-text">' . esc_html( $field['label'] ) . '</label>';
		}

		switch ( strtolower( $field['type'] ) ) {
			case 'customer':
				$this->render_customer_field( $field );
				break;
			case 'product':
				$this->render_product_field( $field );
				break;
			case 'country':
				$this->render_country_field( $field );
				break;
			case 'order_statuses':
				$this->render_order_statuses_field( $field );
				break;
			case 'region':
				$this->render_region_field( $field );
				break;
			case 'date':
				$this->render_date_field( $field );
				break;
			case 'month_year':
				$this->render_month_year_field( $field );
				break;
			case 'separator':
				$this->render_separator( $field );
				break;
			default:
				if ( method_exists( EDD()->html, $field['type'] ) ) {
					echo call_user_func( array( EDD()->html, $field['type'] ), $field );
				}
				break;
		}
	}

	/**
	 * Render a product field
	 *
	 * @param array $field Field configuration
	 */
	private function render_product_field( array $field ) {
		$defaults = array(
			'id'       => 'edd_export_product',
			'name'     => 'product',
			'chosen'   => true,
			'multiple' => false,
		);

		$args = wp_parse_args( $field, $defaults );

		echo EDD()->html->product_dropdown( $args );
	}

	/**
	 * Render a customer field
	 *
	 * @param array $field Field configuration
	 */
	private function render_customer_field( array $field ) {
		$defaults = array(
			'id'            => 'edd_export_customer',
			'name'          => 'customer_id',
			'chosen'        => true,
			'multiple'      => false,
			'none_selected' => '',
			'placeholder'   => __( 'All Customers', 'arraypress' ),
		);

		$args = wp_parse_args( $field, $defaults );

		echo EDD()->html->customer_dropdown( $args );
	}

	/**
	 * Render a country field
	 *
	 * @param array $field Field configuration
	 */
	private function render_country_field( array $field ) {
		$defaults = array(
			'id'              => 'edd_export_country',
			'name'            => 'country',
			'selected'        => false,
			'show_option_all' => false,
		);

		$args = wp_parse_args( $field, $defaults );

		echo EDD()->html->country_select( $args );
	}

	/**
	 * Render a region field
	 *
	 * @param array $field Field configuration
	 */
	private function render_region_field( array $field ) {
		$defaults = array(
			'id'          => 'edd_reports_filter_regions',
			'placeholder' => __( 'All Regions', 'arraypress' ),
		);

		$args = wp_parse_args( $field, $defaults );

		echo EDD()->html->region_select( $args );
	}

	/**
	 * Render a download field
	 *
	 * @param array $field Field configuration
	 */
	private function render_order_statuses_field( array $field ) {
		$defaults = array(
			'id'               => 'edd_export_status',
			'name'             => 'status',
			'show_option_all'  => __( 'All Statuses', 'arraypress' ),
			'show_option_none' => false,
			'selected'         => false,
			'options'          => edd_get_payment_statuses(),
		);

		$args = wp_parse_args( $field, $defaults );

		echo EDD()->html->select( $args );
	}

	/**
	 * Render a date field
	 *
	 * @param array $field Field configuration
	 */
	private function render_date_field( array $field ) {
		?>
        <fieldset class="edd-from-to-wrapper">
            <legend class="screen-reader-text"><?php echo esc_html( $field['legend'] ?? '' ); ?></legend>
            <label for="<?php echo esc_attr( $field['id'] ); ?>-start"
                   class="screen-reader-text"><?php esc_html_e( 'Set start date', 'arraypress' ); ?></label>
            <span id="edd-<?php echo esc_attr( $field['id'] ); ?>-start-wrap">
            <?php
            echo EDD()->html->date_field( array(
	            'id'          => $field['id'] . '-start',
	            'class'       => 'edd-export-start',
	            'name'        => $field['name'] . '-start',
	            'placeholder' => _x( 'From', 'date filter', 'arraypress' ),
            ) );
            ?>
        </span>
            <label for="<?php echo esc_attr( $field['id'] ); ?>-end"
                   class="screen-reader-text"><?php esc_html_e( 'Set end date', 'arraypress' ); ?></label>
            <span id="edd-<?php echo esc_attr( $field['id'] ); ?>-end-wrap">
            <?php
            echo EDD()->html->date_field( array(
	            'id'          => $field['id'] . '-end',
	            'class'       => 'edd-export-end',
	            'name'        => $field['name'] . '-end',
	            'placeholder' => _x( 'To', 'date filter', 'arraypress' ),
            ) );
            ?>
        </span>
        </fieldset>
		<?php
	}

	/**
	 * Render a month and year dropdown field
	 *
	 * @param array $field Field configuration
	 */
	private function render_month_year_field( array $field ) {
		?>
        <fieldset class="edd-to-and-from-container">
            <legend class="screen-reader-text"><?php echo esc_html( $field['legend'] ?? '' ); ?></legend>
            <label for="<?php echo esc_attr( $field['id'] ); ?>_month"
                   class="screen-reader-text"><?php echo esc_html( $field['month_label'] ?? __( 'Select month', 'arraypress' ) ); ?></label>
			<?php echo EDD()->html->month_dropdown( $field['name'] . '_month', 0, $field['id'], true ); ?>
            <label for="<?php echo esc_attr( $field['id'] ); ?>_year"
                   class="screen-reader-text"><?php echo esc_html( $field['year_label'] ?? __( 'Select year', 'arraypress' ) ); ?></label>
			<?php echo EDD()->html->year_dropdown( $field['name'] . '_year', 0, 5, 0, $field['id'] ); ?>
        </fieldset>
		<?php
	}

	/**
	 * Render a separator
	 *
	 * @param array $field Field configuration
	 */
	private function render_separator( array $field ) {
		?>
        <span class="edd-to-and-from--separator"><?php echo esc_html( $field['text'] ?? _x( '&mdash; to &mdash;', 'Date one to date two', 'arraypress' ) ); ?></span>
		<?php
	}
}