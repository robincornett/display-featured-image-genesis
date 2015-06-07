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
	protected $common;
	protected $page;
	protected $displaysetting;
	protected $post_types;
	protected $fields;

	public function __construct( $common ) {
		$this->common = $common;
	}

	/**
	 * add a submenu page under Appearance
	 * @return submenu Display Featured image settings page
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		$this->page = 'displayfeaturedimagegenesis';

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image Settings', 'display-featured-image-genesis' ),
			'manage_options',
			$this->page,
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
			echo '<h2>' . esc_attr( $page_title ) . '</h2>';
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

		$sections = array(
			'main' => array(
				'id'       => 'display_featured_image_section',
				'title'    => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
				'callback' => 'section_description',
			),
		);

		$this->fields = array(
			array(
				'id'       => '[less_header]',
				'title'    => __( 'Height' , 'display-featured-image-genesis' ),
				'callback' => 'header_size',
				'section'  => $sections['main']['id'],
			),
			array(
				'id'       => '[default]',
				'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'callback' => 'set_default_image',
				'section'  => $sections['main']['id'],
			),
			array(
				'id'       => '[exclude_front]',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'exclude_front', 'label' => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => '[keep_titles]',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'keep_titles', 'label' => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => '[move_excerpts]',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'move_excerpts', 'label' => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => '[is_paged]',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'is_paged', 'label' => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => '[feed_image]',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'feed_image', 'label' => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ) ),
			),
		);

		$args = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output = 'objects';

		$this->post_types = get_post_types( $args, $output );

		if ( $this->post_types ) {

			$sections['cpt'] = array(
				'id'       => 'display_featured_image_custom_post_types',
				'title'    => __( 'Featured Images for Custom Post Types', 'display-featured-image-genesis' ),
				'callback' => 'cpt_section_description',
			);

			foreach ( $this->post_types as $post ) {
				$this->fields[] = array(
					'id'       => '[post_types]' . esc_attr( $post->name ),
					'title'    => esc_attr( $post->label ),
					'callback' => 'set_cpt_image',
					'section'  => $sections['cpt']['id'],
					'args'     => array( 'post_type' => $post ),
				);
			}
		}

		foreach ( $sections as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				array( $this, $section['callback'] ),
				$this->page
			);
		}

		foreach ( $this->fields as $field ) {
			add_settings_field(
				$this->page . $field['id'],
				'<label for="' . $field['id'] . '">' . $field['title'] . '</label>',
				array( $this, $field['callback'] ),
				$this->page,
				$field['section'],
				empty( $field['args'] ) ? array() : $field['args']
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
		printf( '<p>%s</p>', esc_html__( 'The Display Featured Image for Genesis plugin has just a few optional settings. Check the Help tab for more information. ', 'display-featured-image-genesis' ) );
	}

	/**
	 * Section description
	 * @return section description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		printf( '<p>%s</p>', esc_html__( 'Since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' ) );
	}

	/**
	 * Setting for reduction amount
	 * @return number of pixels to remove in backstretch-set.js
	 *
	 * @since 1.1.0
	 */
	public function header_size() {

		printf( '<label for="displayfeaturedimagegenesis[less_header]">%s</label>',
			esc_html__( 'Pixels to remove ', 'display-featured-image-genesis' )
		);
		echo '<input type="number" step="1" min="0" max="400" id="displayfeaturedimagegenesis[less_header]" name="displayfeaturedimagegenesis[less_header]" value="' . esc_attr( $this->displaysetting['less_header'] ) . '" class="small-text" />';
		printf( '<p class="description">%s</p>',
			esc_html__( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' )
		);

	}

	/**
	 * Default image uploader
	 *
	 * @return  image
	 *
	 * @since  1.2.1
	 */
	public function set_default_image() {

		$large = $this->common->minimum_backstretch_width();
		$id    = $this->displaysetting['default'] ? $this->displaysetting['default'] : '';
		$name  = 'displayfeaturedimagegenesis[default]';
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id ) );
		}
		$this->render_buttons( $id, $name );
		echo '<p class="description">';
		printf(
			esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		);
		echo '</p>';
	}

	/**
	 * generic checkbox function (for all checkbox settings)
	 * @return 0 1 checkbox
	 *
	 * @since  2.3.0
	 */
	public function do_checkbox( $args ) {
		printf( '<input type="hidden" name="displayfeaturedimagegenesis[%s]" value="0" />', esc_attr( $args['setting'] ) );
		printf( '<label for="displayfeaturedimagegenesis[%1$s]"><input type="checkbox" name="displayfeaturedimagegenesis[%1$s]" id="displayfeaturedimagegenesis[%1$s]" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $args['setting'] ),
			checked( 1, esc_attr( $this->displaysetting[ $args['setting'] ] ), false ),
			esc_attr( $args['label'] )
		);
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @return  image
	 *
	 * @since  2.0.0
	 */
	public function set_cpt_image( $args ) {

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		$post_type = $args['post_type']->name;
		if ( empty( $this->displaysetting['post_type'][ $post_type ] ) ) {
			$this->displaysetting['post_type'][ $post_type ] = $id = '';
		}

		$id   = $this->displaysetting['post_type'][ $post_type ];
		$name = 'displayfeaturedimagegenesis[post_type][' . esc_attr( $post_type ) . ']';
		if ( $id ) {
			echo wp_kses_post( $this->render_image_preview( $id ) );
		}

		$this->render_buttons( $id, $name );

		if ( empty( $id ) ) {
			return;
		}
		echo '<p class="description">';
		printf( __( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( get_post_type_archive_link( $post_type ) ),
			esc_attr( $args['post_type']->label )
		);
		echo '</p>';
	}

	/**
	 * display image preview
	 * @param  variable $id featured image ID
	 * @return $image     image preview
	 *
	 * @since x.y.z
	 */
	public function render_image_preview( $id ) {
		if ( empty( $id ) ) {
			return;
		}

		if ( ! is_numeric( $id ) ) {
			$id = Display_Featured_Image_Genesis_Common::get_image_id( $id );
		}

		$preview = wp_get_attachment_image_src( absint( $id ), 'medium' );
		$image   = '<div class="upload_logo_preview">';
		$image  .= '<img src="' . esc_url( $preview[0] ) . '" />';
		$image  .= '</div>';
		return $image;
	}

	/**
	 * show image select/delete buttons
	 * @param  variable $id   image ID
	 * @param  varable $name name for value/ID/class
	 * @return $buttons       select/delete image buttons
	 *
	 * @since x.y.z
	 */
	public function render_buttons( $id, $name ) {
		$id = is_numeric( $id ) ? $id : Display_Featured_Image_Genesis_Common::get_image_id( $id );
		$id = $id ? (int) $id : '';
		printf( '<input type="hidden" class="upload_image_id" id="%1$s" name="%1$s" value="%2$s" />', esc_attr( $name ), $id );
		printf( '<input id="%s" type="button" class="upload_default_image button-secondary" value="%s" />',
			esc_attr( $name ),
			__( 'Select Image', 'display-featured-image-genesis' )
		);
		if ( ! empty( $id ) ) {
			printf( ' <input type="button" class="delete_image button-secondary" value="%s" />',
				__( 'Delete Image', 'display-featured-image-genesis' )
			);
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
				if ( isset( $_POST['displayfeaturedimagegenesis'][ $key ] ) ) {
					$displaysetting[ $key ] = $_POST['displayfeaturedimagegenesis'][ $key ];
					if ( $_POST['displayfeaturedimagegenesis']['term_image'] === $displaysetting[ $key ] ) {
						$displaysetting[ $key ] = $this->validate_taxonomy_image( $_POST['displayfeaturedimagegenesis'][ $key ] );
						if ( false !== $displaysetting[ $key ] ) {
							$is_updated = true;
						}
					}
				}
			}
			// Save the option array.
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
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce' );

		$new_value['less_header']   = absint( $new_value['less_header'] );

		// validate all checkbox fields
		foreach ( $this->fields as $field ) {
			if ( 'do_checkbox' !== $field['callback'] ) {
				continue;
			}
			$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
		}

		// extra variables to pass through to image validation
		$old_value     = $this->displaysetting['default'];
		$label         = 'Default';
		$size_to_check = $this->common->minimum_backstretch_width();

		// validate default image
		$new_value['default'] = $this->validate_image( $new_value['default'], $old_value, $label, $size_to_check );

		foreach ( $this->post_types as $post_type ) {

			// extra variables to pass through to image validation
			$old_value     = $this->displaysetting['post_type'][ $post_type->name ];
			$label         = $post_type->label;
			$size_to_check = get_option( 'medium_size_w' );

			// sanitize
			$new_value['post_type'][ $post_type->name ] = $this->validate_image( $new_value['post_type'][ $post_type->name ], $old_value, $label, $size_to_check );
		}

		return $new_value;

	}

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	protected function validate_image( $new_value, $old_value, $label, $size_to_check ) {

		$new_value = is_numeric( $new_value ) ? $new_value : (int) Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		$old_value = is_numeric( $old_value ) ? $old_value : (int) Display_Featured_Image_Genesis_Common::get_image_id( $old_value );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];
		$reset     = sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $label );

		// ok for field to be empty
		if ( ! $new_value ) {
			return '';
		}

		if ( $valid && $width > $size_to_check ) {
			return $new_value;
		}

		$new_value = $old_value;
		if ( ! $valid ) {
			$message = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' );
			$class   = 'invalid';
		} elseif ( $width <= $size_to_check ) {
			$message = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' );
			$class   = 'weetiny';
		}

		add_settings_error(
			$old_value,
			esc_attr( $class ),
			esc_attr( $message . $reset ),
			'error'
		);

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
		$new_value = is_numeric( $new_value ) ? $new_value : Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		$new_value = (int) $new_value;
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
	 * Returns old value for author image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New value or old, depending on allowed image size.
	 * @since  x.y.z
	 */
	public function validate_author_image( $new_value, $old_value ) {

		$medium    = get_option( 'medium_size_w' );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];

		if ( ! $new_value  || ( $new_value && $valid && $width > $medium ) ) {
			return $new_value;
		}

		add_filter( 'user_profile_update_errors', array( $this, 'user_profile_error_message' ), 10, 3 );

		return $old_value;

	}

	/**
	 * User profile error message
	 * @param  var $errors error message depending on what's wrong
	 * @param  var $update whether or not to update
	 * @param  var $user   user being updated
	 * @return error message
	 *
	 * @since x.y.z
	 */
	public function user_profile_error_message( $errors, $update, $user ) {
		$new_value = (int) $_POST['displayfeaturedimagegenesis'];
		$medium    = get_option( 'medium_size_w' );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];
		$reset     = sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $user->display_name );

		if ( ! $valid ) {
			$error = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' );
		} elseif ( $width <= $medium ) {
			$error = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' );
		}
		$errors->add( 'profile_error', $error . $reset );
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

		$is_valid_types = (array) apply_filters( 'displayfeaturedimage_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif' ) );

		return ( $file_ext && in_array( $file_ext, $is_valid_types ) );
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
		$large  = $this->common->minimum_backstretch_width();

		$height_help  = '<h3>' . __( 'Height', 'display-featured-image-genesis' ) . '</h3>';
		$height_help .= '<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>';
		$height_help .= '<p>' . __( 'The plugin determines the size of your backstretch image based on the size of the user\'s browser window. Changing the "Height" setting tells the plugin to subtract that number of pixels from the measured height of the user\'s window, regardless of the size of that window.', 'display-featured-image-genesis' ) . '</p>';
		$height_help .= '<p>' . __( 'If you need to control the size of the backstretch Featured Image output with more attention to the user\'s screen size, you will want to consider a CSS approach instead. Check the readme for an example.', 'display-featured-image-genesis' ) . '</p>';

		$default_help  = '<h3>' . __( 'Default Featured Image', 'display-featured-image-genesis' ) . '</h3>';
		$default_help .= '<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>';
		$default_help .= '<p>' . sprintf(
			__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		) . '</p>';

		$skipfront_help  = '<h3>' . __( 'Skip Front Page', 'display-featured-image-genesis' ) . '</h3>';
		$skipfront_help .= '<p>' . __( 'If you set a Default Featured Image, it will show on every post/page of your site. This may not be desirable on child themes with a front page constructed with widgets, so you can select this option to prevent the Featured Image from showing on the front page. Checking this will prevent the Featured Image from showing on the Front Page, even if you have set an image for that page individually.', 'display-featured-image-genesis' ) . '</p>';
		$skipfront_help .= '<p>' . sprintf(
			__( 'If you want to prevent entire groups of posts from not using the Featured Image, you will want to <a href="%s" target="_blank">add a filter</a> to your theme functions.php file.', 'display-featured-image-genesis' ),
			esc_url( 'https://github.com/robincornett/display-featured-image-genesis#how-do-i-stop-the-featured-image-action-from-showing-on-my-custom-post-types' )
		) . '</p>';

		$keeptitles_help  = '<h3>' . __( 'Do Not Move Titles', 'display-featured-image-genesis' ) . '</h3>';
		$keeptitles_help .= '<p>' . __( 'This setting applies to the backstretch Featured Image only. It allows you to keep the post/page titles in their original location, instead of overlaying the new image.', 'display-featured-image-genesis' ) . '</p>';

		$excerpts_help  = '<h3>' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</h3>';
		$excerpts_help .= '<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, all headlines, descriptions, and optional excerpts will display in a box overlaying the Featured Image.', 'display-featured-image-genesis' ) . '</p>';

		$paged_help  = '<h3>' . __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ) . '</h3>';
		$paged_help .= '<p>' . __( 'Featured Images do not normally show on the second (and following) page of term/blog/post archives. Check this setting to ensure that they do.', 'display-featured-image-genesis' ) . '</p>';

		$feed_help  = '<h3>' . __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ) . '</h3>';
		$feed_help .= '<p>' . __( 'This plugin does not add the Featured Image to your content, so normally you will not see your Featured Image in the feed. If you select this option, however, the Featured Image (if it is set) will be added to each entry in your RSS feed.', 'display-featured-image-genesis' ) . '</p>';
		$feed_help .= '<p>' . __( 'If your RSS feed is set to Full Text, the Featured Image will be added to the entry content. If it is set to Summary, the Featured Image will be added to the excerpt instead.', 'display-featured-image-genesis' ) . '</p>';

		$cpt_help  = '<h3>' . __( 'Featured Images for Custom Post Types', 'display-featured-image-genesis' ) . '</h3>';
		$cpt_help .= '<p>' . __( 'Some plugins and/or developers extend the power of WordPress by using Custom Post Types to create special kinds of content.', 'display-featured-image-genesis' ) . '</p>';
		$cpt_help .= '<p>' . __( 'Since your site uses Custom Post Types, you may optionally set a Featured Image for each archive.', 'display-featured-image-genesis' ) . '</p>';
		$cpt_help .= '<p>' . __( 'Featured Images for archives can be smaller than the Default Featured Image, but still need to be larger than your site\'s "medium" image size.', 'display-featured-image-genesis' ) . '</p>';

		$help_tabs = array(
			array(
				'id'      => 'displayfeaturedimage_less_header-help',
				'title'   => __( 'Height', 'display-featured-image-genesis' ),
				'content' => $height_help,
			),
			array(
				'id'      => 'displayfeaturedimage_default-help',
				'title'   => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'content' => $default_help,
			),
			array(
				'id'      => 'displayfeaturedimage_exclude_front-help',
				'title'   => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'content' => $skipfront_help,
			),
			array(
				'id'      => 'displayfeaturedimage_keep_titles-help',
				'title'   => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'content' => $keeptitles_help,
			),
			array(
				'id'      => 'displayfeaturedimage_excerpts-help',
				'title'   => __( 'Move Excerpts', 'display-featured-image-genesis' ),
				'content' => $excerpts_help,
			),
			array(
				'id'      => 'displayfeaturedimage_paged-help',
				'title'   => __( 'Subsequent Pages', 'display-featured-image-genesis' ),
				'content' => $paged_help,
			),
			array(
				'id'      => 'displayfeaturedimage_feed-help',
				'title'   => __( 'RSS Feed', 'display-featured-image-genesis' ),
				'content' => $feed_help,
			),
		);
		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}

		if ( $this->post_types ) {
			$screen->add_help_tab( array(
				'id'      => 'displayfeaturedimage_cpt-help',
				'title'   => __( 'Custom Post Types', 'display-featured-image-genesis' ),
				'content' => $cpt_help,
			) );
		}

	}

}
