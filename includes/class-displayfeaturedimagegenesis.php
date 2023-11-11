<?php
/**
 * Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2014-2020 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * Main plugin class.
 *
 * @package DisplayFeaturedImageGenesis
 */
class Display_Featured_Image_Genesis {

	/**
	 * Admin area class: handles columns.
	 * @var Display_Featured_Image_Genesis_Admin $admin
	 */
	protected $admin;

	/**
	 * Adds new author meta.
	 * @var Display_Featured_Image_Genesis_Author $author
	 */
	protected $author;

	/**
	 * @var $customizer Display_Featured_Image_Genesis_Customizer
	 */
	protected $customizer;

	/**
	 * All archive description functions.
	 * @var Display_Featured_Image_Genesis_Description $description
	 */
	protected $description;

	/**
	 * Handles all image output functionality
	 * @var Display_Featured_Image_Genesis_Output $output
	 */
	protected $output;

	/**
	 * Updates metabox on post edit page
	 * @var Display_Featured_Image_Genesis_Post_Meta $post_meta
	 */
	protected $post_meta;

	/**
	 * Handles RSS feed output
	 * @var Display_Featured_Image_Genesis_RSS $rss
	 */
	protected $rss;

	/**
	 * Sets up settings page for the plugin.
	 * @var Display_Featured_Image_Genesis_Settings $settings
	 */
	protected $settings;

	/**
	 * Handles term meta.
	 * @var Display_Featured_Image_Genesis_Taxonomies $taxonomies
	 */
	protected $taxonomies;

	/**
	 * Register widgets and related shortcodes.
	 *
	 * @var \DisplayFeaturedImageGenesisWidgets
	 */
	protected $widgets;

	/**
	 * Display_Featured_Image_Genesis constructor.
	 *
	 * @param $admin
	 * @param $author
	 * @param $customizer
	 * @param $output
	 * @param $post_meta
	 * @param $rss
	 * @param $settings
	 * @param $taxonomies
	 * @param $widgets
	 */
	public function __construct( $admin, $author, $customizer, $output, $post_meta, $rss, $settings, $taxonomies, $widgets ) {
		$this->admin      = $admin;
		$this->author     = $author;
		$this->customizer = $customizer;
		$this->output     = $output;
		$this->post_meta  = $post_meta;
		$this->rss        = $rss;
		$this->settings   = $settings;
		$this->taxonomies = $taxonomies;
		$this->widgets    = $widgets;
	}

	/**
	 * Main plugin function. Starts up all the things.
	 */
	public function run() {
		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'deactivate' ) );
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'helper-functions.php';

		// Plugin setup
		add_action( 'after_setup_theme', array( $this, 'add_plugin_supports' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . DISPLAYFEATUREDIMAGEGENESIS_BASENAME, array( $this, 'add_settings_link' ) );
		add_action( 'init', array( $this, 'check_settings' ) );

		// Admin
		add_action( 'admin_init', array( $this->admin, 'set_up_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Widgets
		add_action( 'widgets_init', array( $this->widgets, 'register_widgets' ) );
		add_action( 'widgets_init', array( $this->widgets, 'register_shortcodes' ) );
		add_action( 'init', array( $this->widgets, 'register_blocks' ) );
		add_action( 'admin_enqueue_scripts', array( $this->widgets, 'enqueue_scripts' ) );

		// Taxonomies, Authors
		add_filter( 'displayfeaturedimagegenesis_get_taxonomies', array( $this->taxonomies, 'remove_post_status_terms' ) );
		add_action( 'admin_init', array( $this->taxonomies, 'set_taxonomy_meta' ) );
		add_action( 'admin_init', array( $this->author, 'set_author_meta' ) );

		// Post Meta
		add_action( 'add_meta_boxes', array( $this->post_meta, 'add_metabox' ), 10, 2 );
		add_filter( 'admin_post_thumbnail_html', array( $this->post_meta, 'meta_box' ), 10, 2 );
		add_action( 'save_post', array( $this->post_meta, 'save_meta' ) );

		// Settings
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_filter( 'displayfeaturedimagegenesis_get_setting', array( $this->settings, 'get_display_setting' ) );

		// Customizer
		add_action( 'customize_register', array( $this->customizer, 'customizer' ) );

		// Front End Output
		add_action( 'get_header', array( $this->output, 'manage_output' ) );
		add_filter( 'genesis_get_image_default_args', array( $this->output, 'change_thumbnail_fallback' ) );

		// RSS
		add_action( 'template_redirect', array( $this->rss, 'maybe_do_feed' ) );
	}

	/**
	 * deactivates the plugin if Genesis isn't running
	 *
	 *  @since 1.1.2
	 *
	 */
	public function deactivate() {
		deactivate_plugins( DISPLAYFEATUREDIMAGEGENESIS_BASENAME );
		add_action( 'admin_notices', array( $this, 'error_message' ) );
	}

	/**
	 * Error message if we're not using the Genesis Framework.
	 *
	 * @since 1.1.0
	 */
	public function error_message() {

		$error = sprintf( __( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. It has been deactivated.', 'display-featured-image-genesis' ) );

		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			$error = $error . sprintf(
				/* translators: placeholder is the user's PHP version. */
				__( ' But since we\'re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'display-featured-image-genesis' ),
				PHP_VERSION
			);
		}

		echo '<div class="error"><p>' . esc_attr( $error ) . '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}


	/**
	 * Register a custom image size for banners, and add excerpt support for pages if needed.
	 * @since 1.3.0
	 *
	 * If the Core image size has not been registered (pre-5.3), register it. The plugin is
	 * transitioning to using the new 2048x2048 image size.
	 * @since 3.2.0
	 */
	public function add_plugin_supports() {

		if ( ! has_image_size( '2048x2048' ) ) {
			$args = apply_filters(
				'displayfeaturedimagegenesis_custom_image_size',
				array(
					'width'  => 2048,
					'height' => 2048,
					'crop'   => false,
				)
			);
			add_image_size( '2048x2048', (int) $args['width'], (int) $args['height'], (bool) $args['crop'] );
		}

		$move_excerpts = displayfeaturedimagegenesis_get_setting( 'move_excerpts' );
		if ( $move_excerpts ) {
			add_post_type_support( 'page', 'excerpt' );
		}
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'display-featured-image-genesis' );
	}

	/**
	 * enqueue admin scripts
	 *
	 * @since  1.2.1
	 */
	public function enqueue_scripts() {

		$version = displayfeaturedimagegenesis_get()->version;
		$minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'displayfeaturedimage-upload', plugins_url( "/includes/js/settings-upload{$minify}.js", dirname( __FILE__ ) ), array( 'jquery', 'media-upload', 'thickbox' ), $version, true );
		wp_register_script( 'widget_selector', plugins_url( "/includes/js/widget-selector{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), $version, true );

		$screen     = get_current_screen();
		$screen_ids = array(
			'appearance_page_displayfeaturedimagegenesis',
			'profile',
			'user-edit',
		);

		if ( in_array( $screen->id, $screen_ids, true ) || ! empty( $screen->taxonomy ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
			wp_localize_script(
				'displayfeaturedimage-upload',
				'DisplayFeaturedImageGenesis',
				array(
					'text' => __( 'Select Image', 'display-featured-image-genesis' ),
				)
			);
		}
	}

	/**
	 * Add link to plugin settings page in plugin table
	 * @param $links array link to settings page
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function add_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'themes.php?page=displayfeaturedimagegenesis' ) ), esc_attr__( 'Settings', 'display-featured-image-genesis' ) );
		return $links;
	}

	/**
	 * Check the plugin setting and maybe update it to use the new image size,
	 * which is registered in Core in WP5.3.
	 *
	 * @since 3.2.0
	 */
	public function check_settings() {

		$setting = displayfeaturedimagegenesis_get_setting();
		if ( empty( $setting ) ) {
			return;
		}

		$new_setting = array();
		if ( 'displayfeaturedimage_backstretch' === $setting['image_size'] ) {
			$new_setting['image_size'] = '2048x2048';
		}

		if ( $new_setting ) {
			$this->update_settings(
				$new_setting,
				$setting
			);
		}
	}

	/**
	 * Takes an array of new settings, merges with the current setting, and updates the setting.
	 *
	 * @param array $new
	 * @param array $old_setting
	 * @return boolean
	 * @since 3.2.0
	 */
	private function update_settings( $new_setting = array(), $old_setting = array() ) {
		return update_option( 'displayfeaturedimagegenesis', wp_parse_args( $new_setting, $old_setting ) );
	}
}
