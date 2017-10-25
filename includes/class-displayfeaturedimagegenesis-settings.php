<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Settings extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The common plugin class.
	 * @var $commmon Display_Featured_Image_Genesis_Common
	 */
	protected $common;

	/**
	 * The plugin admin page.
	 * @var $page string
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * The plugin setting.
	 * @var $setting array
	 */
	protected $setting;

	/**
	 * Public post types on the site.
	 * @var $post_types array
	 */
	protected $post_types;

	/**
	 * The plugin settings fields.
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * add a submenu page under Appearance
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		$this->common     = new Display_Featured_Image_Genesis_Common();
		$this->setting    = $this->get_display_setting();
		$this->post_types = $this->get_content_types();

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		$sections     = $this->register_sections();
		$this->fields = $this->register_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * create settings form
	 *
	 * @since  1.4.0
	 */
	public function do_settings_form() {
		$page_title = get_admin_page_title();
		echo '<div class="wrap">';
			printf( '<h1>%s</h1>', esc_attr( $page_title ) );
			$this->check_and_maybe_update_terms();
			$active_tab = $this->get_active_tab();
			echo $this->do_tabs( $active_tab );
			echo '<form action="options.php" method="post">';
				settings_fields( 'displayfeaturedimagegenesis' );
				do_settings_sections( 'displayfeaturedimagegenesis_' . $active_tab );
				wp_nonce_field( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce', false );
				submit_button();
				settings_errors();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Check if term images need to be updated because they were added before WP 4.4 and this plugin 2.4.
	 * @since 2.6.1
	 */
	protected function check_and_maybe_update_terms() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			return;
		}
		if ( $this->terms_have_been_updated() ) {
			return;
		}
		$previous_user = get_option( 'displayfeaturedimagegenesis', false );
		if ( ! $previous_user ) {
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );

			return;
		}

		include_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-settings-terms.php';
		$terms = new Display_Featured_Image_Genesis_Settings_Terms();
		$terms->maybe_update_terms();
	}

	/**
	 * Output tabs.
	 *
	 * @param $active_tab
	 *
	 * @return string
	 * @since 2.5.0
	 */
	protected function do_tabs( $active_tab ) {
		$tabs    = $this->define_tabs();
		$output  = '<div class="nav-tab-wrapper">';
		$output .= sprintf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'display-featured-image-genesis' ) );
		$output .= '<ul>';
		foreach ( $tabs as $tab ) {
			$class   = $active_tab === $tab['id'] ? ' nav-tab-active' : '';
			$output .= sprintf( '<li><a href="themes.php?page=%s&tab=%s" class="nav-tab%s">%s</a></li>', $this->page, $tab['id'], $class, $tab['tab'] );
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Settings for options screen
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {
		register_setting( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis', array(
			$this,
			'do_validation_things',
		) );
	}

	/**
	 * Define tabs for the settings page.
	 * @return array
	 * @since 2.6.0
	 */
	protected function define_tabs() {
		return array(
			'main'  => array(
				'id'  => 'main',
				'tab' => __( 'Main', 'display-featured-image-genesis' ),
			),
			'style' => array(
				'id'  => 'style',
				'tab' => __( 'Backstretch Output', 'display-featured-image-genesis' ),
			),
			'cpt'   => array(
				'id'  => 'cpt',
				'tab' => __( 'Content Types', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Register plugin settings page sections
	 *
	 * @since 2.3.0
	 */
	protected function register_sections() {
		return array(
			'main'  => array(
				'id'    => 'main',
				'title' => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
			),
			'style' => array(
				'id'    => 'style',
				'title' => __( 'Display Settings', 'display-featured-image-genesis' ),
			),
			'cpt'   => array(
				'id'    => 'cpt',
				'title' => __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Register plugin settings fields
	 * @return array           all settings fields
	 *
	 * @since 2.3.0
	 */
	protected function register_fields() {

		return array_merge( $this->define_main_fields(), $this->define_style_fields(), $this->define_cpt_fields() );
	}

	/**
	 * Define the fields for the main/first tab.
	 * @return array
	 */
	protected function define_main_fields() {
		return array(
			array(
				'id'       => 'default',
				'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'callback' => 'set_default_image',
				'section'  => 'main',
			),
			array(
				'id'       => 'always_default',
				'title'    => __( 'Always Use Default', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'always_default',
					'label'   => __( 'Always use the default image, even if a featured image is set.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'exclude_front',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'exclude_front',
					'label'   => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'keep_titles',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'keep_titles',
					'label'   => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'move_excerpts',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'move_excerpts',
					'label'   => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'is_paged',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'is_paged',
					'label'   => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'feed_image',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'feed_image',
					'label'   => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'thumbnails',
				'title'    => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'thumbnails',
					'label'   => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'shortcode',
				'title'    => __( 'Add Shortcode Buttons', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'main',
				'args'     => array(
					'setting' => 'shortcode',
					'options' => array(
						'displayfeaturedimagegenesis_term'      => __( 'Featured Term Widget', 'display-featured-image-genesis' ),
						'displayfeaturedimagegenesis_author'    => __( 'Featured Author Widget', 'display-featured-image-genesis' ),
						'displayfeaturedimagegenesis_post_type' => __( 'Featured Post Type Widget', 'display-featured-image-genesis' ),
					),
				),
			),
		);
	}

	/**
	 * Define the fields for the style tab.
	 * @return array
	 */
	protected function define_style_fields() {
		return array(
			array(
				'id'       => 'less_header',
				'title'    => __( 'Height', 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'style',
				'args'     => array(
					'setting' => 'less_header',
					'label'   => __( 'pixels to remove', 'display-featured-image-genesis' ),
					'min'     => 0,
					'max'     => 400,
				),
			),
			array(
				'id'       => 'max_height',
				'title'    => __( 'Maximum Height', 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'style',
				'args'     => array(
					'setting' => 'max_height',
					'label'   => __( 'pixels', 'display-featured-image-genesis' ),
					'min'     => 100,
					'max'     => 1000,
				),
			),
			array(
				'id'       => 'centeredX',
				'title'    => __( 'Center Horizontally', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'args'     => array(
					'setting' => 'centeredX',
					'buttons' => $this->pick_center(),
					'legend'  => __( 'Center the backstretch image on the horizontal axis?', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'centeredY',
				'title'    => __( 'Center Vertically', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'args'     => array(
					'setting' => 'centeredY',
					'buttons' => $this->pick_center(),
					'legend'  => __( 'Center the backstretch image on the vertical axis?', 'display-featured-image-genesis' ),
				),
			),
			array(
				'id'       => 'fade',
				'title'    => __( 'Fade', 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'style',
				'args'     => array(
					'setting' => 'fade',
					'label'   => __( 'milliseconds', 'display-featured-image-genesis' ),
					'min'     => 0,
					'max'     => 20000,
				),
			),
		);
	}

	/**
	 * Define the fields for the content types tab.
	 * @return array
	 */
	protected function define_cpt_fields() {
		$fields = array(
			array(
				'id'       => 'post_types][search',
				'title'    => __( 'Search Results', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'search' ),
			),
			array(
				'id'       => 'post_types][fourohfour',
				'title'    => __( '404 Page', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'fourohfour' ),
			),
			array(
				'id'       => 'skip',
				'title'    => __( 'Skip Content Types', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'cpt',
				'args'     => array(
					'setting' => 'skip',
					'options' => $this->get_post_types(),
				),
			),
		);

		if ( $this->post_types ) {

			foreach ( $this->post_types as $post ) {
				$object   = get_post_type_object( $post );
				$fields[] = array(
					'id'       => 'post_types][' . esc_attr( $object->name ),
					'title'    => esc_attr( $object->label ),
					'callback' => 'set_cpt_image',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $object ),
				);
			}
		}

		return $fields;
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function main_section_description() {
		$description = __( 'Use these settings to modify the plugin behavior throughout your site. Check the Help tab for more information. ', 'display-featured-image-genesis' );
		$this->print_section_description( $description );
	}

	/**
	 * Style section description
	 */
	public function style_section_description() {
		$description = __( 'These settings modify the output style/methods for the backstretch image.', 'display-featured-image-genesis' );
		$this->print_section_description( $description );
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		$description = __( 'Optional: set a custom image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' );
		if ( $this->post_types ) {
			$description .= __( ' Additionally, since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' );
		}
		$this->print_section_description( $description );
	}

	/**
	 * Description for less_header setting.
	 * @return string description
	 *
	 * @since 2.3.0
	 */
	protected function less_header_description() {
		return __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' );
	}

	/**
	 * Description for the max_height setting.
	 * @return string|void description
	 * @since 2.6.0
	 */
	protected function max_height_description() {
		return __( 'Optionally, set a max-height value for the header image; it will be added to your CSS.', 'display-featured-image-genesis' );
	}

	/**
	 * Default image uploader
	 *
	 * @since  1.2.1
	 */
	public function set_default_image() {

		$id   = $this->setting['default'] ? $this->setting['default'] : '';
		$name = 'displayfeaturedimagegenesis[default]';
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id, 'default' ) );
		}
		$this->render_buttons( $id, $name );
		$this->do_description( 'default_image' );

	}

	/**
	 * Description for default image setting
	 * @return string
	 *
	 * @since 2.3.0
	 */
	protected function default_image_description() {
		$large = $this->common->minimum_backstretch_width();

		return sprintf(
			esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		);
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @since  2.0.0
	 */
	public function set_cpt_image( $args ) {

		$post_type = is_object( $args['post_type'] ) ? $args['post_type']->name : $args['post_type'];
		if ( empty( $this->setting['post_type'][ $post_type ] ) ) {
			$this->setting['post_type'][ $post_type ] = $id = '';
		}

		if ( is_object( $args['post_type'] ) ) {
			$fallback_args = array(
				'setting'      => "fallback][{$post_type}",
				'label'        => sprintf( __( 'Always use a fallback image for %s.', 'display-featured-image-genesis' ), esc_attr( $args['post_type']->label ) ),
				'setting_name' => 'fallback',
				'name'         => $post_type,
			);
			echo '<p>';
			$this->do_checkbox( $fallback_args );
			echo '</p>';
		}
		$id   = $this->setting['post_type'][ $post_type ];
		$name = 'displayfeaturedimagegenesis[post_type][' . esc_attr( $post_type ) . ']';
		if ( $id ) {
			echo wp_kses_post( $this->render_image_preview( $id, $post_type ) );
		}

		$this->render_buttons( $id, $name );

		if ( empty( $id ) || ! is_object( $args['post_type'] ) ) {
			return;
		}
		$description = sprintf( __( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( get_post_type_archive_link( $post_type ) ),
			esc_attr( $args['post_type']->label )
		);
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * @return array
	 */
	public function pick_center() {
		return array(
			1 => __( 'Center', 'display-featured-image-genesis' ),
			0 => __( 'Do Not Center', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Get the post types as options.
	 * @return array
	 */
	protected function get_post_types() {
		$post_types = $this->get_content_types_built_in();
		$options    = array();
		foreach ( $post_types as $post_type ) {
			$object                = get_post_type_object( $post_type );
			$options[ $post_type ] = $object->label;
		}

		return $options;
	}

	/**
	 * validate all inputs
	 *
	 * @param $new_value array
	 *
	 * @return array
	 *
	 * @since  1.4.0
	 */
	public function do_validation_things( $new_value ) {

		$action = 'displayfeaturedimagegenesis_save-settings';
		$nonce  = 'displayfeaturedimagegenesis_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( $action, $nonce );
		$new_value = array_merge( $this->setting, $new_value );

		include_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-settings-validate.php';

		$validation = new Display_Featured_Image_Genesis_Settings_Validate( $this->register_fields(), $this->setting );
		return $validation->validate( $new_value );
	}

	/**
	 * Check whether terms need to be updated
	 * @return boolean true if on 4.4 and wp_options for terms exist; false otherwise
	 *
	 * @since 2.4.0
	 */
	protected function terms_have_been_updated() {
		$updated = get_option( 'displayfeaturedimagegenesis_updatedterms', false );

		return (bool) $updated;
	}
}
