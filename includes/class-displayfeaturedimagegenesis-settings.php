<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Settings {

	/**
	 * Settings for media screen
	 * @return settings for backstretch image options
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {
		register_setting( 'media', 'displayfeaturedimage_less_header', 'absint' );

		add_settings_section(
			'display_featured_image_section',
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			array( $this, 'section_description'),
			'media'
		);

		add_settings_field(
			'displayfeaturedimage_less_header',
			'<label for="displayfeaturedimage_less_header">' . __( 'Height' , 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'header_size' ),
			'media',
			'display_featured_image_section'
		);
	}

	/**
	 * Section description
	 * @return section description
	 *
	 * @since 1.1.0
	 */
	public function section_description() {
		echo '<p>' . __( 'Change this setting to reduce the maximum height of the backstretch image.', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Setting for reduction amount
	 * @return number of pixels to remove in backstretch-set.js
	 *
	 * @since 1.1.0
	 */
	public function header_size() {
		$value = get_option( 'displayfeaturedimage_less_header', 0 );

		echo '<label for="displayfeaturedimage_less_header">' . __( 'Pixels to remove ', 'display-featured-image-genesis' ) . '</label>';
		echo '<input type="number" step="1" min="0" max="400" id="displayfeaturedimage_less_header" name="displayfeaturedimage_less_header" value="' . esc_attr( $value ) . '" class="small-text" />';
		echo '<p class="description">' . __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' ) . '</p>';

	}

	/**
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 1.1.0
	 */
	public function help() {
		$screen = get_current_screen();

		$displayfeaturedimage_help =
			'<h3>' . __( 'Reducto!', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage-help',
			'title'   => __( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'content' => $displayfeaturedimage_help,
		) );

	}

}
