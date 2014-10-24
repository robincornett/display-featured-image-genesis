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
		register_setting( 'media', 'displayfeaturedimage_default' );


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

		add_settings_field(
			'displayfeaturedimage_default',
			'<label for="displayfeaturedimage_default">' . __( 'Default Featured Image', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'set_default_image' ),
			'media',
			'display_featured_image_section'
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Section description
	 * @return section description
	 *
	 * @since 1.1.0
	 */
	public function section_description() {
		echo '<p>' . __( 'The Display Featured Image for Genesis plugin has two optional settings: 1) change the height of the backstretch effect, and 2) set a default backstretch image to use if no featured image is set.', 'display-featured-image-genesis' ) . '</p>';
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
	 * Default image uploader
	 *
	 * @return  image
	 *
	 * @since  1.2.1
	 */
	public function set_default_image() {
		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		if ( $item->fallback && $item->original[1] >= $item->large ) {
			echo '<div id="upload_logo_preview">';
			echo '<img src="' . $item->original[0] . '" style="max-width:400px" />';
			echo '</div>';
		}
		elseif ( $item->fallback && $item->original[1] <= $item->large ) {
			echo '<div class="error"><p>' . __( 'Sorry, your image is too small to serve as the default featured image.', 'display-featured-image-genesis' ) . '</p></div>';
		}
		echo '<input type="text" id="default_image_url" name="displayfeaturedimage_default" value="' . $item->fallback . '" />';
		echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Default Image', 'display-featured-image-genesis' ) . '" />';
		echo '<p class="description">' . __( 'If you would like to use a default image for the featured image, upload it here. Must be a backstretch sized image.', 'display-featured-image-genesis' ) . '</p>';
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
			'<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>' .

			'<h3>' . __( 'Set a Default Featured Image', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives. It must be larger than your site&#39;s Large Image setting, or else it will not display. This is for a backstretch image only.', 'display-featured-image-genesis' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage-help',
			'title'   => __( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'content' => $displayfeaturedimage_help,
		) );

	}

	/**
	 * enqueue admin scripts
	 * @return scripts to use image uploader
	 *
	 * @since  1.2.1
	 */
	public function enqueue_scripts() {
		wp_register_script( 'displayfeaturedimage-upload', plugins_url( '/includes/js/settings-upload.js', dirname( __FILE__ ) ), array( 'jquery', 'media-upload', 'thickbox' ), '1.0.0' );

		if ( 'options-media' == get_current_screen()->id ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
		}

	}

}
