<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsShortcodes
 */
class DisplayFeaturedImageGenesisWidgetsShortcodes {

	/**
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
	 * @param $class
	 *
	 * @return mixed
	 */
	protected function get_defaults( $class ) {
		$widget_class = new $class();

		return $widget_class->defaults();
	}

	/**
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
