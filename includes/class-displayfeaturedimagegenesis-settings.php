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
		register_setting( 'media', 'displayfeaturedimage_default', array( $this, 'validate_image' ) );
		register_setting( 'media', 'displayfeaturedimage_excerpts', array( $this, 'one_zero' ) );


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

		add_settings_field(
			'displayfeaturedimage_excerpts',
			'<label for="displayfeaturedimage_excerpts">' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'move_excerpts' ),
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
		echo '<p>' . __( 'The Display Featured Image for Genesis plugin has three optional settings. Check the Help tab for more information. ', 'display-featured-image-genesis' ) . '</p>';
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

		if ( !empty( $item->fallback ) ) {
			$preview = wp_get_attachment_image_src( $item->fallback_id, 'medium' );
			echo '<div id="upload_logo_preview">';
			echo '<img src="' . esc_url( $preview[0] ) . '" style="max-width:400px" />';
			echo '</div>';
		}
		echo '<input type="url" id="default_image_url" name="displayfeaturedimage_default" value="' . $item->fallback . '" />';
		echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Default Image', 'display-featured-image-genesis' ) . '" />';
		echo '<p class="description">' . sprintf(
			__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $item->large+1 )
		) . '</p>';
	}

	/**
	 * move excerpts to leader image area
	 * @return 0 1 checkbox
	 *
	 * @since  1.3.0
	 */
	public function move_excerpts() {
		$value = get_option( 'displayfeaturedimage_excerpts' );

		echo '<input type="checkbox" name="displayfeaturedimage_excerpts" id="displayfeaturedimage_excerpts" value="1"' . checked( 1, $value, false ) . ' class="code" /> <label for="displayfeaturedimage_excerpts">' . __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	public function validate_image( $new_value ) {

		// not using get_image_variables since we need to check before commit to option
		$new_value = esc_url( $new_value );
		$valid     = $this->is_valid_img_ext( $new_value );
		$large     = get_option( 'large_size_w' );
		$id        = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		$file      = wp_get_attachment_image_src( $id, 'original' );

		// ok for field to be empty
		if ( empty( $new_value ) ) {
			return;
		}
		// check if file type is legit. if not, wp_die()
		elseif ( !$valid ) {
			wp_die( sprintf(
				__( 'Sorry, that is an invalid file type. <a href="%1$s">Return to the Media Settings page and try again.</a>', 'display-featured-image-genesis' ),
				esc_url( admin_url( 'options-media.php' ) )
			) );
			return get_option( 'displayfeaturedimage_default', '' );
		}
		// if file is an image, but is too small, throw it back
		elseif ( $file[1] <= $large ) {
			wp_die( sprintf(
				__( 'Sorry, that image is too small to use as your Default Featured Image. Your image needs to be at least %1$s pixels wide. <a href="%2$s">Return to the Media Settings page and try again.</a>', 'display-featured-image-genesis' ),
				absint( $large+1 ),
				esc_url( admin_url( 'options-media.php' ) )
			) );

			return get_option( 'displayfeaturedimage_default', '' );
		}
		return $new_value;

	}

	/**
	 * returns file extension
	 * @since  1.2.2
	 */
	protected function get_file_ext( $file ) {
		$parsed = @parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * check if file type is image
	 * @return file       check file extension against list
	 * @since  1.2.2
	 */
	protected function is_valid_img_ext( $file ) {
		$file_ext = $this->get_file_ext( $file );

		$this->valid = empty( $this->valid )
			? (array) apply_filters( 'displayfeaturedimage_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif' ) )
			: $this->valid;

		return ( $file_ext && in_array( $file_ext, $this->valid ) );
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 * @return integer 1 or 0.
	 */
	public function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 1.1.0
	 */
	public function help() {
		$screen = get_current_screen();
		$large  = get_option( 'large_size_w' );

		$displayfeaturedimage_help =
			'<p>' . __( 'Display Featured Image for Genesis has three optional settings:', 'display-featured-image-genesis' ) . '</p>' .
			'<h3>' . __( 'Height', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>' .

			'<h3>' . __( 'Set a Default Featured Image', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . sprintf(
				__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
				absint( $large+1 )
			) . '</p>' .
			'<h3>' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, archives with both a Headline and Intro Text will output both in a box overlaying the Featured Image. Posts which use an optional Excerpt will behave the same way.', 'display-featured-image-genesis' ) . '</p>';

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
