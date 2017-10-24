<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsShortcodes
 */
class DisplayFeaturedImageGenesisWidgetsShortcodes {

	/**
	 * Build the featured author widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_author( $atts ) {
		$class    = 'Display_Featured_Image_Genesis_Author_Widget';
		$defaults = $this->get_defaults( $class );
		$atts     = shortcode_atts( $defaults, $atts, 'displayfeaturedimagegenesis_author' );
		$atts     = $this->validate_shortcode( $atts, $class );

		return $this->do_shortcode( $atts, $class );
	}

	/**
	 * Build the featured post type widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_post_type( $atts ) {
		$class    = 'Display_Featured_Image_Genesis_Widget_CPT';
		$defaults = $this->get_defaults( $class );
		$atts     = shortcode_atts( $defaults, $atts, 'displayfeaturedimagegenesis_post_type' );
		$atts     = $this->validate_shortcode( $atts, $class );

		return $this->do_shortcode( $atts, $class );
	}

	/**
	 * Build the featured term widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_term( $atts ) {
		$class    = 'Display_Featured_Image_Genesis_Widget_Taxonomy';
		$defaults = $this->get_defaults( $class );
		$atts     = shortcode_atts( $defaults, $atts, 'displayfeaturedimagegenesis_term' );
		$atts     = $this->validate_shortcode( $atts, $class );

		return $this->do_shortcode( $atts, $class );
	}

	/**
	 * Add media shortcode buttons to the editor.
	 */
	public function shortcode_buttons() {
		$widgets = array(
			'displayfeaturedimagegenesis_term'      => __( 'Add Featured Term Widget', 'display-featured-image-genesis' ),
			'displayfeaturedimagegenesis_author'    => __( 'Add Featured Author Widget', 'display-featured-image-genesis' ),
			'displayfeaturedimagegenesis_post_type' => __( 'Add Featured Post Type Widget', 'display-featured-image-genesis' ),
		);
		$setting = displayfeaturedimagegenesis_get_setting();
		foreach ( $widgets as $widget => $button_label ) {
			if ( ! $setting['shortcode'][ $widget ] ) {
				continue;
			}
			sixtenpress_shortcode_register( $widget, array(
				'modal'  => $widget,
				'button' => array(
					'id'    => "{$widget}-create",
					'class' => "{$widget}-create",
					'label' => $button_label,
				),
				'self'   => true,
				'labels' => array(
					'title'  => __( 'Create Widget', 'display-featured-image-genesis' ),
					'insert' => __( 'Insert Widget', 'display-featured-image-genesis' ),
				),
			) );
		}
	}

	/**
	 * Add the widget forms to the modal.
	 * @param $shortcode
	 */
	public function do_modal( $shortcode ) {
		$widgets = array(
			'displayfeaturedimagegenesis_term'      => 'Display_Featured_Image_Genesis_Widget_Taxonomy',
			'displayfeaturedimagegenesis_author'    => 'Display_Featured_Image_Genesis_Author_Widget',
			'displayfeaturedimagegenesis_post_type' => 'Display_Featured_Image_Genesis_Widget_CPT',
		);
		foreach ( $widgets as $shortcode_text => $widget ) {
			if ( $shortcode_text === $shortcode ) {
				$class = new $widget();
				$class->form( array() );
			}
		}
	}

	/**
	 * Modify our modals' CSS.
	 *
	 * @param $css
	 *
	 * @return string
	 */
	public function inline_css( $css ) {
		return '.displayfeaturedimagegenesis_term .media-modal-content, .displayfeaturedimagegenesis_post_type .media-modal-content {max-width: 500px;max-height:475px;}
		.displayfeaturedimagegenesis_author .media-modal-content {max-width: 300px;}';
	}

	/**
	 * Get the widget defaults.
	 *
	 * @param $class
	 *
	 * @return mixed
	 */
	protected function get_defaults( $class ) {
		$widget_class = new $class();

		return $widget_class->defaults();
	}

	/**
	 * Return the shortcode output.
	 * @param $atts
	 * @param $class
	 *
	 * @return string
	 */
	protected function do_shortcode( $atts, $class ) {
		$args = array(
			'id' => 'displayfeaturedimagegenesis-shortcode',
		);
		ob_start();
		the_widget( $class, $atts, $args );
		$output = ob_get_clean();

		return do_shortcode( trim( $output ) );
	}

	/**
	 * Validate the shortcode.
	 *
	 * @param $atts
	 * @param $class
	 *
	 * @return mixed
	 */
	protected function validate_shortcode( $atts, $class ) {
		$fields = new $class();
		foreach ( $fields->get_fields( $atts ) as $field ) {
			$value = $field['args']['id'];
			if ( ! isset( $atts[ $value ] ) ) {
				continue;
			}
			switch ( $field['method'] ) {
				// Sanitize numbers
				case 'number':
					$atts[ $value ] = $atts[ $value ] ? absint( $atts[ $value ] ) : '';
					break;

				// Sanitize checkboxes
				case 'checkbox':
					$atts[ $value ] = filter_var( $atts[ $value ], FILTER_VALIDATE_BOOLEAN );
					break;

				// Sanitize text fields
				case 'text':
					$atts[ $value ] = strip_tags( $atts[ $value ] );
					break;

				// Escape select options
				case 'select':
					$atts[ $value ] = esc_attr( $atts[ $value ] );
					break;

				case 'textarea':
					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$atts[ $value ] = sanitize_textarea_field( $atts[ $value ] );
					} else {
						$atts[ $value ] = esc_textarea( $atts[ $value ] );
					}
					break;

				// Default
				default:
					$atts[ $value ] = esc_attr( $atts[ $value ] );
					break;
			}
		} // End foreach().
		$atts['title'] = strip_tags( $atts['title'] );

		return $atts;
	}
}
