<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Helper extends DisplayFeaturedImageGenesisGetSetting {

	/**
	 * Variable for the plugin setting.
	 * @var $setting
	 */
	protected $setting;

	/**
	 * Base id/slug for the settings page.
	 * @var string $page
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * Generic function to add settings sections
	 *
	 * @since 2.4.0
	 */
	protected function add_sections( $sections ) {

		foreach ( $sections as $section ) {
			add_settings_section(
				$this->page . '_' . $section['id'],
				$section['title'],
				array( $this, $section['id'] . '_section_description' ),
				$this->page . '_' . $section['id']
			);
		}
	}

	/**
	 * Generic function to add settings fields
	 * @param  array $sections registered sections
	 * @return array           all settings fields
	 *
	 * @since 2.4.0
	 */
	protected function add_fields( $fields, $sections ) {

		foreach ( $fields as $field ) {
			add_settings_field(
				'[' . $field['id'] . ']',
				sprintf( '<label for="%s">%s</label>', $field['id'], $field['title'] ),
				array( $this, $field['callback'] ),
				$this->page . '_' . $sections[ $field['section'] ]['id'],
				$this->page . '_' . $sections[ $field['section'] ]['id'],
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 2.5.0
	 */
	protected function get_active_tab() {
		return isset( $_GET['tab'] ) ? $_GET['tab'] : 'main';
	}

	/**
	 * Echoes out the section description.
	 * @param  string $description text string for description
	 * @return string              as paragraph and escaped
	 *
	 * @since 2.3.0
	 */
	protected function print_section_description( $description ) {
		echo wp_kses_post( wpautop( $description ) );
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 2.3.0
	 */
	public function do_number( $args ) {
		printf( '<label for="%5$s[%3$s]"><input type="number" step="1" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />%6$s</label>',
			(int) $args['min'],
			(int) $args['max'],
			esc_attr( $args['setting'] ),
			esc_attr( $this->setting[ $args['setting'] ] ),
			esc_attr( $this->page ),
			esc_attr( $args['label'] )
		);
		$this->do_description( $args['setting'] );

	}

	/**
	 * generic checkbox function (for all checkbox settings)
	 * @return 0 1 checkbox
	 *
	 * @since  2.3.0
	 */
	public function do_checkbox( $args ) {
		$setting = $this->get_checkbox_setting( $args );
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		printf( '<label for="%4$s[%1$s]" style="margin-right:12px;"><input type="checkbox" name="%4$s[%1$s]" id="%4$s[%1$s]" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $args['setting'] ),
			checked( 1, esc_attr( $setting ), false ),
			esc_attr( $args['label'] ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Get the current value for the checkbox.
	 * @param $args
	 *
	 * @return int
	 */
	protected function get_checkbox_setting( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		if ( isset( $args['setting_name'] ) && isset( $this->setting[ $args['setting_name'] ][ $args['name'] ] ) ) {
			$setting = $this->setting[ $args['setting_name'] ][ $args['name'] ];
		}
		return $setting;
	}

	/**
	 * Build a checkbox array.
	 * @param $args
	 */
	public function do_checkbox_array( $args ) {
		foreach ( $args['options'] as $key => $value ) {
			$type_args = array(
				'setting'      => "{$args['setting']}][{$key}",
				'label'        => $value,
				'setting_name' => $args['setting'],
				'name'         => $key,
			);
			$this->do_checkbox( $type_args );
		}
	}

	/**
	 * radio buttons
	 * @return string radio button output
	 * @since 2.6.0
	 */
	public function do_radio_buttons( $args ) {
		echo '<fieldset>';
		printf( '<legend class="screen-reader-text">%s</legend>', $args['legend'] );
		foreach ( $args['buttons'] as $key => $button ) {
			printf( '<label for="%5$s[%1$s][%2$s]" style="margin-right:12px !important;"><input type="radio" id="%5$s[%1$s][%2$s]" name="%5$s[%1$s]" value="%2$s"%3$s />%4$s</label>  ',
				esc_attr( $args['setting'] ),
				esc_attr( $key ),
				checked( $key, $this->setting[ $args['setting'] ], false ),
				esc_attr( $button ),
				esc_attr( $this->page )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Generic callback to display a field description.
	 * @param  string $args setting name used to identify description callback
	 * @return string       Description to explain a field.
	 *
	 * @since 2.3.0
	 */
	protected function do_description( $args ) {
		$function = $args . '_description';
		if ( ! method_exists( $this, $function ) ) {
			return;
		}
		$description = $this->$function();
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * display image preview
	 * @param  int $id featured image ID
	 * @param $alt string description for alt text
	 * @return $image     image preview
	 *
	 * @since 2.3.0
	 */
	public function render_image_preview( $id, $alt = '' ) {
		if ( empty( $id ) ) {
			return;
		}

		$id       = displayfeaturedimagegenesis_check_image_id( $id );
		$alt_text = sprintf( __( '%s featured image', 'display-featured-image-genesis' ), esc_attr( $alt ) );
		$preview  = wp_get_attachment_image_src( (int) $id, 'medium' );
		$image    = sprintf( '<div class="upload_logo_preview"><img src="%s" alt="%s" /></div>', esc_url( $preview[0] ), esc_attr( $alt_text ) );
		return $image;
	}

	/**
	 * show image select/delete buttons
	 * @param  int $id   image ID
	 * @param  string $name name for value/ID/class
	 * @return $buttons       select/delete image buttons
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $id, $name ) {
		$id = displayfeaturedimagegenesis_check_image_id( $id );
		$id = $id ? (int) $id : '';
		printf( '<input type="hidden" class="upload_image_id" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( $id ) );
		printf( '<input id="%s" type="button" class="upload_default_image button-secondary" value="%s" />',
			esc_attr( $name ),
			esc_attr__( 'Select Image', 'display-featured-image-genesis' )
		);
		if ( ! empty( $id ) ) {
			printf( ' <input type="button" class="delete_image button-secondary" value="%s" />',
				esc_attr__( 'Delete Image', 'display-featured-image-genesis' )
			);
		}
	}

	/**
	 * Get all public content types, not including built in.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_content_types() {
		$args = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output = 'names';

		return get_post_types( $args, $output );
	}

	/**
	 * Get all public content types, including built in.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_content_types_built_in() {
		$built_in   = array( 'post', 'page' );
		$post_types = $this->get_content_types();
		return array_merge( $built_in, $post_types );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    2.3.0
	 * @access   protected
	 *
	 * @param    string    $action   The name of the action specified on the submenu page
	 * @param    string    $nonce    The nonce specified on the submenu page
	 *
	 * @return   bool                True if the user has permission to save; false, otherwise.
	 * @author   Tom McFarlin (https://tommcfarlin.com/save-wordpress-submenu-page-options/)
	 */
	protected function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}
		return ( $is_nonce_set && $is_valid_nonce );
	}
}
