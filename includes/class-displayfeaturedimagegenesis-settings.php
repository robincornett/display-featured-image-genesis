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
	 * variable set for featured image option
	 * @var option
	 */
	protected $displaysetting;

	/**
	 * add a submenu page under Appearance
	 * @return submenu Display Featured image settings page
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image Settings', 'display-featured-image-genesis' ),
			'manage_options',
			'displayfeaturedimagegenesis',
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'load-appearance_page_displayfeaturedimagegenesis', array( $this, 'help' ) );

	}

	/**
	 * create settings form
	 * @return form Display Featured Image for Genesis settings
	 *
	 * @since  1.4.0
	 */
	public function do_settings_form() {
		$page_title = get_admin_page_title();

		echo '<div class="wrap">';
			echo '<h2>' . $page_title . '</h2>';
			echo '<form action="options.php" method="post">';
				settings_fields( 'displayfeaturedimagegenesis' );
				do_settings_sections( 'displayfeaturedimagegenesis' );
				wp_nonce_field( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce', false );
				submit_button();
				settings_errors();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Settings for options screen
	 * @return settings for backstretch image options
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {

		register_setting( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis', array( $this, 'do_validation_things' ) );

		$defaults = array(
			'less_header'   => 0,
			'default'       => '',
			'exclude_front' => 0,
			'move_excerpts' => 0
		);

		$this->displaysetting = get_option( 'displayfeaturedimagegenesis', $defaults );

		add_settings_section(
			'display_featured_image_section',
			__( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
			array( $this, 'section_description'),
			'displayfeaturedimagegenesis'
		);

		add_settings_field(
			'displayfeaturedimagegenesis[less_header]',
			'<label for="displayfeaturedimagegenesis[less_header]">' . __( 'Height' , 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'header_size' ),
			'displayfeaturedimagegenesis',
			'display_featured_image_section'
		);

		add_settings_field(
			'displayfeaturedimagegenesis[default]',
			'<label for="displayfeaturedimagegenesis[default]">' . __( 'Default Featured Image', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'set_default_image' ),
			'displayfeaturedimagegenesis',
			'display_featured_image_section'
		);

		add_settings_field(
			'displayfeaturedimagegenesis[exclude_front]',
			'<label for="displayfeaturedimagegenesis[exclude_front]">' . __( 'Skip Front Page', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'exclude_front' ),
			'displayfeaturedimagegenesis',
			'display_featured_image_section'
		);

		add_settings_field(
			'displayfeaturedimagegenesis[move_excerpts]',
			'<label for="displayfeaturedimagegenesis[move_excerpts]">' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'move_excerpts' ),
			'displayfeaturedimagegenesis',
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
		echo '<p>' . __( 'The Display Featured Image for Genesis plugin has just a few optional settings. Check the Help tab for more information. ', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Setting for reduction amount
	 * @return number of pixels to remove in backstretch-set.js
	 *
	 * @since 1.1.0
	 */
	public function header_size() {

		echo '<label for="displayfeaturedimagegenesis[less_header]">' . __( 'Pixels to remove ', 'display-featured-image-genesis' ) . '</label>';
		echo '<input type="number" step="1" min="0" max="400" id="displayfeaturedimagegenesis[less_header]" name="displayfeaturedimagegenesis[less_header]" value="' . esc_attr( $this->displaysetting['less_header'] ) . '" class="small-text" />';
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

		if ( ! empty( $this->displaysetting['default'] ) ) {
			$preview = wp_get_attachment_image_src( $item->fallback_id, 'medium' );
			echo '<div id="upload_logo_preview">';
			echo '<img src="' . esc_url( $preview[0] ) . '" />';
			echo '</div>';
		}
		echo '<input type="url" id="default_image_url" name="displayfeaturedimagegenesis[default]" value="' . esc_url( $this->displaysetting['default'] ) . '" />';
		echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Default Image', 'display-featured-image-genesis' ) . '" />';
		echo '<p class="description">' . sprintf(
			__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $item->large + 1 )
		) . '</p>';
	}

	/**
	 * option to exclude featured image on front page
	 * @return 0 1 checkbox
	 *
	 * @since  1.4.0
	 */
	public function exclude_front() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[exclude_front]" value="0" />';
		echo '<input type="checkbox" name="displayfeaturedimagegenesis[exclude_front]" id="displayfeaturedimagegenesis[exclude_front]" value="1"' . checked( 1, $this->displaysetting['exclude_front'], false ) . ' class="code" /> <label for="displayfeaturedimagegenesis[exclude_front]">' . __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * option to move excerpts to leader image area
	 * @return 0 1 checkbox
	 *
	 * @since  1.3.0
	 */
	public function move_excerpts() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[move_excerpts]" value="0" />';
		echo '<input type="checkbox" name="displayfeaturedimagegenesis[move_excerpts]" id="displayfeaturedimagegenesis[move_excerpts]" value="1"' . checked( 1, $this->displaysetting['move_excerpts'], false ) . ' class="code" /> <label for="displayfeaturedimagegenesis[move_excerpts]">' . __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * validate all inputs
	 * @param  string $new_value various settings
	 * @return string            number or URL
	 *
	 * @since  1.4.0
	 */
	public function do_validation_things( $new_value ) {

		if ( empty( $_POST['displayfeaturedimagegenesis_nonce'] ) ) {
			wp_die( __( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce' );

		$new_value['less_header']   = absint( $new_value['less_header'] );

		$new_value['default']       = $this->validate_image( $new_value['default'] );

		$new_value['exclude_front'] = $this->one_zero( $new_value['exclude_front'] );

		$new_value['move_excerpts'] = $this->one_zero( $new_value['move_excerpts'] );

		return $new_value;

	}

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	protected function validate_image( $new_value ) {

		$new_value = esc_url( $new_value );
		$valid     = $this->is_valid_img_ext( $new_value );
		$large     = get_option( 'large_size_w' );
		$id        = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		$metadata  = wp_get_attachment_metadata( $id );
		$width     = $metadata['width'];

		// ok for field to be empty
		if ( $new_value ) {

			if ( ! $valid ) {
				$message   = __( 'Sorry, that is an invalid file type. The Default Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' );
				$new_value = $this->displaysetting['default'];

				add_settings_error(
					$this->displaysetting['default'],
					esc_attr( 'invalid' ),
					$message,
					'error'
				);
			}
			// if file is an image, but is too small, throw it back
			elseif ( $width <= $large ) {
				$message   = __( 'Sorry, your image is too small. The Default Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' );
				$new_value = $this->displaysetting['default'];

				add_settings_error(
					$this->displaysetting['default'],
					esc_attr( 'weetiny' ),
					$message,
					'error'
				);
			}

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
	protected function one_zero( $new_value ) {
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

		$height_help =
			'<h3>' . __( 'Height', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>';

		$default_help =
			'<h3>' . __( 'Set a Default Featured Image', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . sprintf(
				__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
				absint( $large + 1 )
			) . '</p>';

		$skipfront_help =
			'<h3>' . __( 'Show on Front Page', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'If you set a Default Featured Image, it will show on every post/page of your site. This may not be desirable on child themes with a front page constructed with widgets, so you can select this option to prevent the Featured Image from showing on the front page. Checking this will prevent the Featured Image from showing on the Front Page, even if you have set an image for that page individually.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . sprintf(
				__( 'If you want to prevent entire groups of posts from not using the Featured Image, you will want to <a href="%s" target="_blank">add a filter</a> to your theme functions.php file.', 'display-featured-image-genesis' ),
				esc_url( 'https://github.com/robincornett/display-featured-image-genesis#how-do-i-stop-the-featured-image-action-from-showing-on-my-custom-post-types' )
			) . '</p>';

		$excerpts_help =
			'<h3>' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, all headlines, descriptions, and optional excerpts will display in a box overlaying the Featured Image.', 'display-featured-image-genesis' ) . '</p>';


		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_less_header-help',
			'title'   => __( 'Height', 'display-featured-image-genesis' ),
			'content' => $height_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_default-help',
			'title'   => __( 'Default Featured Image', 'display-featured-image-genesis' ),
			'content' => $default_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_exclude_front-help',
			'title'   => __( 'Show on Front Page', 'display-featured-image-genesis' ),
			'content' => $skipfront_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_excerpts-help',
			'title'   => __( 'Move Excerpts', 'display-featured-image-genesis' ),
			'content' => $excerpts_help,
		) );

	}

	/**
	 * enqueue admin scripts
	 * @return scripts to use image uploader
	 *
	 * @since  1.2.1
	 */
	public function enqueue_scripts() {
		$version = Display_Featured_Image_Genesis_Common::$version;

		wp_register_script( 'displayfeaturedimage-upload', plugins_url( '/includes/js/settings-upload.js', dirname( __FILE__ ) ), array( 'jquery', 'media-upload', 'thickbox' ), $version );

		if ( 'appearance_page_displayfeaturedimagegenesis' === get_current_screen()->id ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
			wp_localize_script( 'displayfeaturedimage-upload', 'objectL10n', array(
				'text' => __( 'Choose Image', 'display-featured-image-genesis' ),
			) );
		}

	}

}
