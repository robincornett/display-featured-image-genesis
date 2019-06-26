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
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_author', 'Display_Featured_Image_Genesis_Author_Widget', 'author' );
	}

	/**
	 * Build the featured post type widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_post_type( $atts ) {
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_post_type', 'Display_Featured_Image_Genesis_Widget_CPT', 'cpt' );
	}

	/**
	 * Build the featured term widget shortcode.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function shortcode_term( $atts ) {
		return $this->build_shortcode( $atts, 'displayfeaturedimagegenesis_term', 'Display_Featured_Image_Genesis_Widget_Taxonomy', 'term' );
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
	protected function build_shortcode( $atts, $shortcode, $class, $id ) {
		$defaults = $this->get_defaults( $id );
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
	protected function get_defaults( $id ) {
		return include "fields/{$id}-defaults.php";
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
		$update = new DisplayFeaturedImageGenesisWidgetsUpdate();

		return $update->update( $atts, array(), $fields->get_fields( $atts ) );
	}
}
