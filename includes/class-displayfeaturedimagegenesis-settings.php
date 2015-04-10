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

	protected $post_types;

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
			'keep_titles'   => 0,
			'move_excerpts' => 0,
			'is_paged'      => 0,
			'feed_image'    => 0,
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
			'displayfeaturedimagegenesis[keep_titles]',
			'<label for="displayfeaturedimagegenesis[keep_titles]">' . __( 'Do Not Move Titles', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'keep_titles' ),
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

		add_settings_field(
			'displayfeaturedimagegenesis[is_paged]',
			'<label for="displayfeaturedimagegenesis[is_paged]">' . __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'check_is_paged' ),
			'displayfeaturedimagegenesis',
			'display_featured_image_section'
		);

		add_settings_field(
			'displayfeaturedimagegenesis[feed_image]',
			'<label for="displayfeaturedimagegenesis[feed_image]">' . __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'add_image_to_feed' ),
			'displayfeaturedimagegenesis',
			'display_featured_image_section'
		);

		$args = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output = 'objects';

		$this->post_types = get_post_types( $args, $output );

		if ( $this->post_types ) {
			add_settings_section(
				'display_featured_image_custom_post_types',
				__( 'Featured Images for Custom Post Types', 'display-featured-image-genesis' ),
				array( $this, 'cpt_section_description' ),
				'displayfeaturedimagegenesis'
			);


			add_settings_field(
				"displayfeaturedimagegenesis[post_types]",
				__( 'Featured Images', 'display-featured-image-genesis' ),
				array( $this, 'set_cpt_image' ),
				'displayfeaturedimagegenesis',
				'display_featured_image_custom_post_types'
			);

		}

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
	 * Section description
	 * @return section description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		echo '<p>' . __( 'Since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' ) . '</p>';
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

		$large = Display_Featured_Image_Genesis_Common::minimum_backstretch_width();

		if ( ! empty( $this->displaysetting['default'] ) ) {
			$id = $this->displaysetting['default'];
			if ( ! is_numeric( $this->displaysetting['default'] ) ) {
				$id = Display_Featured_Image_Genesis_Common::get_image_id( $this->displaysetting['default'] );
			}
			$preview = wp_get_attachment_image_src( absint( $id ), 'medium' );
			echo '<div id="upload_logo_preview">';
			echo '<img src="' . esc_url( $preview[0] ) . '" />';
			echo '</div>';
		}
		echo '<input type="hidden" class="upload_image_url" id="displayfeaturedimagegenesis[default]" name="displayfeaturedimagegenesis[default]" value="' . absint( $this->displaysetting['default'] ) . '" />';
		echo '<input type="button" class="upload_default_image button-secondary" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
		if ( ! empty( $this->displaysetting['default'] ) ) {
			echo '<input type="button" class="delete_image button-secondary" value="' . __( 'Delete Image', 'display-featured-image-genesis' ) . '" />';
		}
		echo '<p class="description">' . sprintf(
			__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
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
		echo '<label for="displayfeaturedimagegenesis[exclude_front]"><input type="checkbox" name="displayfeaturedimagegenesis[exclude_front]" id="displayfeaturedimagegenesis[exclude_front]" value="1"' . checked( 1, esc_attr( $this->displaysetting['exclude_front'] ), false ) . ' class="code" />' . __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * option to not move titles
	 * @return 0 1 checkbox
	 *
	 * @since  2.0.0
	 */
	public function keep_titles() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[keep_titles]" value="0" />';
		echo '<label for="displayfeaturedimagegenesis[keep_titles]"><input type="checkbox" name="displayfeaturedimagegenesis[keep_titles]" id="displayfeaturedimagegenesis[keep_titles]" value="1"' . checked( 1, esc_attr( $this->displaysetting['keep_titles'] ), false ) . ' class="code" />' . __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * option to move excerpts to leader image area
	 * @return 0 1 checkbox
	 *
	 * @since  1.3.0
	 */
	public function move_excerpts() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[move_excerpts]" value="0" />';
		echo '<label for="displayfeaturedimagegenesis[move_excerpts]"><input type="checkbox" name="displayfeaturedimagegenesis[move_excerpts]" id="displayfeaturedimagegenesis[move_excerpts]" value="1"' . checked( 1, esc_attr( $this->displaysetting['move_excerpts'] ), false ) . ' class="code" />' . __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * option to show featured image on page 2+ of blog/archives
	 * @return 0 1 checkbox
	 *
	 * @since  2.2.0
	 */
	public function check_is_paged() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[is_paged]" value="0" />';
		echo '<label for="displayfeaturedimagegenesis[is_paged]"><input type="checkbox" name="displayfeaturedimagegenesis[is_paged]" id="displayfeaturedimagegenesis[is_paged]" value="1"' . checked( 1, esc_attr( $this->displaysetting['is_paged'] ), false ) . ' class="code" />' . __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * option to add images to feed
	 * @return 0 1 checkbox
	 *
	 * @since  1.5.0
	 */
	public function add_image_to_feed() {
		echo '<input type="hidden" name="displayfeaturedimagegenesis[feed_image]" value="0" />';
		echo '<label for="displayfeaturedimagegenesis[feed_image]"><input type="checkbox" name="displayfeaturedimagegenesis[feed_image]" id="displayfeaturedimagegenesis[feed_image]" value="1"' . checked( 1, esc_attr( $this->displaysetting['feed_image'] ), false ) . ' class="code" />' . __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ) . '</label>';
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @return  image
	 *
	 * @since  2.0.0
	 */
	public function set_cpt_image() {

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		foreach ( $this->post_types as $post ) {

			$post_type = $post->name;
			if ( empty( $this->displaysetting['post_type'][$post_type] ) ) {
				$this->displaysetting['post_type'][$post_type] = '';
			}
			echo '<div>';
			echo '<h4>' . $post->label . '</h4>';
			if ( ! empty( $this->displaysetting['post_type'][$post_type] ) ) {
				$id = $this->displaysetting['post_type'][$post_type];
				if ( ! is_numeric( $this->displaysetting['post_type'][$post_type] ) ) {
					$id = Display_Featured_Image_Genesis_Common::get_image_id( $this->displaysetting['post_type'][$post_type] );
				}
				$preview = wp_get_attachment_image_src( absint( $id ), 'medium' );
				echo '<div id="upload_logo_preview">';
				echo '<img src="' . esc_url( $preview[0] ) . '" />';
				echo '</div>';
			}
			echo '<input type="hidden" class="upload_image_url" id="displayfeaturedimagegenesis[post_type][' . $post_type . ']" name="displayfeaturedimagegenesis[post_type][' . $post_type . ']" value="' . absint( $this->displaysetting['post_type'][$post_type] ) . '" />';
			echo '<input type="button" class="upload_default_image button-secondary" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
			if ( ! empty( $this->displaysetting['post_type'][$post_type] ) ) {
				echo '<input type="button" class="delete_image button-secondary" value="' . __( 'Delete Image', 'display-featured-image-genesis' ) . '" />';
				echo '<p class="description">' . sprintf(
					__( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
					esc_url( get_post_type_archive_link( $post_type ) ),
					$post->label
				) . '</p>';
			}
			echo '</div>';

		}

	}

	/**
	 * Save extra taxonomy fields callback function.
	 * @param  term id $term_id the id of the term
	 * @return updated option          updated option for term featured image
	 *
	 * @since 2.0.0
	 */
	public function save_taxonomy_custom_meta( $term_id ) {

		if ( isset( $_POST['displayfeaturedimagegenesis'] ) ) {
			$t_id           = $term_id;
			$displaysetting = get_option( "displayfeaturedimagegenesis_$t_id" );
			$cat_keys       = array_keys( $_POST['displayfeaturedimagegenesis'] );
			$is_updated     = false;
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['displayfeaturedimagegenesis'][$key] ) ) {
					$displaysetting[$key] = $_POST['displayfeaturedimagegenesis'][$key];
					if ( $_POST['displayfeaturedimagegenesis']['term_image'] === $displaysetting[$key] ) {
						$displaysetting[$key] = $this->validate_taxonomy_image( $_POST['displayfeaturedimagegenesis'][$key] );
						if ( false !== $displaysetting[$key] ) {
							$is_updated = true;
						}
					}
				}
			}
			//* Save the option array.
			if ( $is_updated ) {
				update_option( "displayfeaturedimagegenesis_$t_id", $displaysetting );
			}
		}

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

		$new_value['keep_titles']   = $this->one_zero( $new_value['keep_titles'] );

		$new_value['move_excerpts'] = $this->one_zero( $new_value['move_excerpts'] );

		$new_value['is_paged']      = $this->one_zero( $new_value['is_paged'] );

		$new_value['feed_image']    = $this->one_zero( $new_value['feed_image'] );

		foreach ( $this->post_types as $post_type ) {
			$new_value['post_type'][$post_type->name] = $this->validate_post_type_image( $new_value['post_type'][$post_type->name] );
			if ( false === $new_value['post_type'][$post_type->name] ) {
				$new_value['post_type'][$post_type->name] = $this->displaysetting['post_type'][$post_type->name];
			}
		}

		return $new_value;

	}

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	protected function validate_image( $new_value ) {

		// if the image was selected using the old URL method
		if ( ! is_numeric( $new_value ) ) {
			$new_value = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		}
		$new_value = absint( $new_value );
		$large     = Display_Featured_Image_Genesis_Common::minimum_backstretch_width();
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];
		$reset     = __( ' The Default Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' );

		// ok for field to be empty
		if ( $new_value ) {

			if ( ! $valid ) {
				$message   = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' ) . $reset;
				$new_value = $this->displaysetting['default'];

				add_settings_error(
					$this->displaysetting['default'],
					esc_attr( 'invalid' ),
					$message,
					'error'
				);
			}
			// if the image is external to the WP site, we cannot use it.
			elseif ( ! $source ) {
				$message   = __( 'Sorry, your image must be uploaded directly to your WordPress site.', 'display-featured-image-genesis' ) . $reset;
				$new_value = $this->displaysetting['default'];

				add_settings_error(
					$this->displaysetting['default'],
					esc_attr( 'external' ),
					$message,
					'error'
				);
			}
			// if file is an image, but is too small, throw it back
			elseif ( $width <= $large ) {
				$message   = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' ) . $reset;
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
	 * Returns empty value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  2.0.0
	 */
	protected function validate_post_type_image( $new_value ) {

		// if the image was selected using the old URL method
		if ( ! is_numeric( $new_value ) ) {
			$new_value = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		}
		$new_value = absint( $new_value );
		$medium    = get_option( 'medium_size_w' );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];

		// ok for field to be empty
		if ( $new_value ) {

			if ( ! $valid ) {
				$message   = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' );
				$new_value = false;

				add_settings_error(
					$this->displaysetting['post_type'],
					esc_attr( 'invalid' ),
					$message,
					'error'
				);
			}
			// if the image is external to the WP site, we cannot use it.
			elseif ( ! $source ) {
				$message   = __( 'Sorry, your image must be uploaded directly to your WordPress site.', 'display-featured-image-genesis' );
				$new_value = false;

				add_settings_error(
					$this->displaysetting['default'],
					esc_attr( 'external' ),
					$message,
					'error'
				);
			}
			// if file is an image, but is too small, throw it back
			elseif ( $width <= $medium ) {
				$message   = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' );
				$new_value = false;

				add_settings_error(
					$this->displaysetting['post_type'],
					esc_attr( 'weetiny' ),
					$message,
					'error'
				);
			}

		}

		return $new_value;
	}

	/**
	 * Returns false value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New value or false, depending on allowed image size.
	 * @since  2.0.0
	 */
	protected function validate_taxonomy_image( $new_value ) {

		// if the image was selected using the old URL method
		if ( ! is_numeric( $new_value ) ) {
			$new_value = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		}
		$new_value = absint( $new_value );
		$medium    = get_option( 'medium_size_w' );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];

		// ok for field to be empty
		if ( $new_value && ( ! $valid || $width <= $medium ) ) {
			$new_value = false;
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
		$large  = Display_Featured_Image_Genesis_Common::minimum_backstretch_width();

		$height_help =
			'<h3>' . __( 'Height', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . __( 'The plugin determines the size of your backstretch image based on the size of the user\'s browser window. Changing the "Height" setting tells the plugin to subtract that number of pixels from the measured height of the user\'s window, regardless of the size of that window.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . __( 'If you need to control the size of the backstretch Featured Image output with more attention to the user\'s screen size, you will want to consider a CSS approach instead. Check the readme for an example.', 'display-featured-image-genesis' ) . '</p>';

		$default_help =
			'<h3>' . __( 'Default Featured Image', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . sprintf(
				__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
				absint( $large + 1 )
			) . '</p>';

		$skipfront_help =
			'<h3>' . __( 'Skip Front Page', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'If you set a Default Featured Image, it will show on every post/page of your site. This may not be desirable on child themes with a front page constructed with widgets, so you can select this option to prevent the Featured Image from showing on the front page. Checking this will prevent the Featured Image from showing on the Front Page, even if you have set an image for that page individually.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . sprintf(
				__( 'If you want to prevent entire groups of posts from not using the Featured Image, you will want to <a href="%s" target="_blank">add a filter</a> to your theme functions.php file.', 'display-featured-image-genesis' ),
				esc_url( 'https://github.com/robincornett/display-featured-image-genesis#how-do-i-stop-the-featured-image-action-from-showing-on-my-custom-post-types' )
			) . '</p>';

		$keeptitles_help =
			'<h3>' . __( 'Do Not Move Titles', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'This setting applies to the backstretch Featured Image only. It allows you to keep the post/page titles in their original location, instead of overlaying the new image.', 'display-featured-image-genesis' ) . '</p>';

		$excerpts_help =
			'<h3>' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, all headlines, descriptions, and optional excerpts will display in a box overlaying the Featured Image.', 'display-featured-image-genesis' ) . '</p>';

		$feed_help =
			'<h3>' . __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'This plugin does not add the Featured Image to your content, so normally you will not see your Featured Image in the feed. If you select this option, however, the Featured Image (if it is set) will be added to each entry in your RSS feed.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . __( 'If your RSS feed is set to Full Text, the Featured Image will be added to the entry content. If it is set to Summary, the Featured Image will be added to the excerpt instead.', 'display-featured-image-genesis' ) . '</p>';

		$cpt_help =
			'<h3>' . __( 'Featured Images for Custom Post Types', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'Some plugins and/or developers extend the power of WordPress by using Custom Post Types to create special kinds of content.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . __( 'Since your site uses Custom Post Types, you may optionally set a Featured Image for each archive.', 'display-featured-image-genesis' ) . '</p>' .
			'<p>' . __( 'Featured Images for archives can be smaller than the Default Featured Image, but still need to be larger than your site\'s "medium" image size.', 'display-featured-image-genesis' ) . '</p>';


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
			'title'   => __( 'Skip Front Page', 'display-featured-image-genesis' ),
			'content' => $skipfront_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_keep_titles-help',
			'title'   => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
			'content' => $keeptitles_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_excerpts-help',
			'title'   => __( 'Move Excerpts', 'display-featured-image-genesis' ),
			'content' => $excerpts_help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_feed-help',
			'title'   => __( 'RSS Feed', 'display-featured-image-genesis' ),
			'content' => $feed_help,
		) );

		if ( $this->post_types ) {
			$screen->add_help_tab( array(
				'id'      => 'displayfeaturedimage_cpt-help',
				'title'   => __( 'Custom Post Types', 'display-featured-image-genesis' ),
				'content' => $cpt_help,
			) );
		}

	}

}
