<?php

/**
 * Class Display_Featured_Image_Genesis_Helper
 * @package DisplayFeaturedImageGenesis
 * @copyright 2017-2020 Robin Cornett
 */
class Display_Featured_Image_Genesis_Helper extends DisplayFeaturedImageGenesisGetSetting {

	/**
	 * Base id/slug for the settings page.
	 * @var string $page
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * The image settings class.
	 * @var $images \DisplayFeaturedImageGenesisSettingsImages
	 */
	private $images;

	/**
	 * Generic function to add settings sections
	 *
	 * @since 2.4.0
	 *
	 * @param $sections
	 */
	protected function add_sections( $sections ) {

		foreach ( $sections as $section ) {
			add_settings_section(
				$this->page . '_' . $section['id'],
				$section['title'],
				array( $this, 'section_description' ),
				$this->page . '_' . $section['tab']
			);
		}
	}

	/**
	 * Generic function to add settings fields
	 *
	 * @param $fields
	 * @param  array $sections registered sections
	 *
	 * @since 2.4.0
	 */
	protected function add_fields( $fields, $sections ) {
		foreach ( $fields as $field ) {
			add_settings_field(
				$field['id'],
				sprintf( '<label for="%s-%s">%s</label>', $this->page, $field['id'], $field['title'] ),
				array( $this, 'do_field' ),
				$this->page . '_' . $sections[ $field['section'] ]['tab'],
				$this->page . '_' . $sections[ $field['section'] ]['id'],
				$field
			);
		}
	}

	/**
	 * Generic field method.
	 *
	 * @param $field
	 */
	public function do_field( $field ) {
		$callback = $this->get_callback( $field );
		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $field );
		}
		if ( ! empty( $field['description'] ) ) {
			$this->do_description( $field );
		}
	}

	/**
	 * Get the correct method to output the field.
	 *
	 * @param $field
	 *
	 * @return array|bool
	 * @since 3.1.0
	 */
	private function get_callback( $field ) {
		$callback = false;
		if ( ! empty( $field['type'] ) ) {
			$callback = "do_{$field['type']}";
		} elseif ( ! empty( $field['callback'] ) ) {
			$callback = $field['callback'];
		}

		return $callback ? array( $this, $callback ) : false;
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 2.5.0
	 */
	protected function get_active_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );

		return $tab ? $tab : 'main';
	}

	/**
	 * Echoes out the section description.
	 *
	 * @since 2.3.0
	 *
	 * @param $section
	 */
	public function section_description( $section ) {
		$id     = str_replace( "{$this->page}_", '', $section['id'] );
		$method = "{$id}_section_description";
		if ( method_exists( $this, $method ) ) {
			echo wp_kses_post( wpautop( $this->$method() ) );
		}
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 2.3.0
	 *
	 * @param $args
	 */
	public function do_number( $args ) {
		printf(
			'<input type="number" step="1" min="%1$s" max="%2$s" id="%3$s" name="%5$s" value="%4$s" class="small-text" />%6$s',
			(int) $args['min'],
			(int) $args['max'],
			esc_attr( $this->get_field_id( $args ) ),
			esc_attr( $this->get_field_value( $args ) ),
			esc_attr( $this->get_field_name( $args ) ),
			esc_attr( $args['label'] )
		);
	}

	/**
	 * generic checkbox function (for all checkbox settings)
	 *
	 * @since  2.3.0
	 *
	 * @param $args
	 */
	public function do_checkbox( $args ) {
		$name  = $this->get_field_name( $args );
		$value = $this->get_field_value( $args );
		printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $name ) );
		printf(
			'<label for="%1$s"><input type="checkbox" name="%4$s" id="%1$s" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $this->get_field_id( $args ) ),
			checked( 1, esc_attr( $value ), false ),
			esc_attr( $args['label'] ),
			esc_attr( $name )
		);
	}

	/**
	 * Build a checkbox array.
	 *
	 * @param $args
	 */
	public function do_checkbox_array( $args ) {
		echo '<fieldset>';
		$name  = $this->get_field_name( $args );
		$id    = $this->get_field_id( $args );
		$value = $this->get_field_value( $args );
		foreach ( $args['options'] as $choice => $label ) {
			$check = isset( $value[ $choice ] ) ? $value[ $choice ] : 0;
			printf(
				'<input type="hidden" name="%s[%s]" value="0" />',
				esc_attr( $name ),
				esc_attr( $choice )
			);
			printf(
				'<label for="%5$s-%1$s" style="margin-right:12px !important;"><input type="checkbox" name="%4$s[%1$s]" id="%5$s-%1$s" value="1"%2$s class="code" aria-labelledby="%5$s" />%3$s</label>',
				esc_attr( $choice ),
				checked( 1, $check, false ),
				esc_html( $label ),
				esc_attr( $name ),
				esc_attr( $id )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * radio buttons
	 * @since 2.6.0
	 *
	 * @param $args
	 */
	public function do_radio( $args ) {
		echo '<fieldset>';
		$name  = $this->get_field_name( $args );
		$id    = $this->get_field_id( $args );
		$value = $this->get_field_value( $args );
		printf( '<legend class="screen-reader-text">%s</legend>', esc_html( $args['legend'] ) );
		foreach ( $args['choices'] as $choice => $label ) {
			printf(
				'<label for="%1$s-%2$s" style="margin-right:12px !important;"><input type="radio" id="%1$s-%2$s" name="%5$s" value="%2$s"%3$s />%4$s</label>  ',
				esc_attr( $id ),
				esc_attr( $choice ),
				checked( $choice, $value, false ),
				esc_attr( $label ),
				esc_attr( $name )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Output a select field.
	 *
	 * @param $args
	 */
	public function do_select( $args ) {
		$value = $this->get_field_value( $args );
		printf(
			'<select id="%1$s" name="%2$s">',
			esc_attr( $this->get_field_id( $args ) ),
			esc_attr( $this->get_field_name( $args ) )
		);
		foreach ( (array) $args['choices'] as $option => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option ),
				selected( $option, $value, false ),
				esc_attr( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Maybe instantiate the image settings class and do the image field.
	 *
	 * @param $args
	 *
	 * @since 3.1.0
	 */
	protected function do_image( $args ) {
		$images = $this->get_images_class();
		$images->do_image( $args );
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @param  $args array
	 *
	 * @since 2.3.0
	 */
	protected function do_description( $args ) {
		$description = isset( $args['description'] ) ? $args['description'] : false;
		$function    = $args['id'] . '_description';
		if ( method_exists( $this, $function ) ) {
			$description = $this->$function();
		}
		if ( ! $description ) {
			return;
		}
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Get all public content types, not including built in.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_content_types() {
		$args   = array(
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
	 * Get the current field name.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function get_field_name( $args ) {
		return isset( $args['key'] ) && $args['key'] ? $this->page . '[' . $args['key'] . '][' . $args['setting'] . ']' : $this->page . '[' . $args['id'] . ']';
	}

	/**
	 * Get the current field id.
	 *
	 * @param $args
	 *
	 * @return string
	 *
	 */
	public function get_field_id( $args ) {
		return isset( $args['key'] ) && $args['key'] ? $this->page . '-' . $args['key'] . '-' . $args['setting'] : $this->page . '-' . $args['id'];
	}

	/**
	 * Get the current field value.
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @internal param $setting
	 *
	 */
	public function get_field_value( $args ) {
		if ( isset( $args['key'] ) && $args['key'] ) {
			$value = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
		} else {
			$value = isset( $this->setting[ $args['id'] ] ) ? $this->setting[ $args['id'] ] : '';
		}

		return $value;
	}

	/**
	 * Get the images settings class.
	 * @return \DisplayFeaturedImageGenesisSettingsImages
	 * @since 3.1.0
	 */
	protected function get_images_class() {
		if ( isset( $this->images ) ) {
			return $this->images;
		}

		include_once 'class-displayfeaturedimagegenesis-settings-images.php';
		$this->images = new DisplayFeaturedImageGenesisSettingsImages( $this->setting );

		return $this->images;
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    2.3.0
	 * @access   protected
	 *
	 * @param    string $action The name of the action specified on the submenu page
	 * @param    string $nonce  The nonce specified on the submenu page
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
