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
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_author', 'Display_Featured_Image_Genesis_Author_Widget' );
	}

	/**
	 * Build the featured post type widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_post_type( $atts ) {
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_post_type', 'Display_Featured_Image_Genesis_Widget_CPT' );
	}

	/**
	 * Build the featured term widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_term( $atts ) {
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_term', 'Display_Featured_Image_Genesis_Widget_Taxonomy' );
	}

	/**
	 * Helper function to build and return the shortcode.
	 *
	 * @param $atts
	 * @param $shortcode
	 * @param $class
	 *
	 * @return string
	 */
	protected function build_shortcode( $atts, $shortcode, $class ) {
		$defaults = $this->get_defaults( $class );
		$atts     = shortcode_atts( $defaults, $atts, $shortcode );
		$atts     = $this->validate_shortcode( $atts, $class );

		return $this->do_shortcode( $atts, $class );
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
	 *
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
