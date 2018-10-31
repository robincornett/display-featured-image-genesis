<?php
/**
 * Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2014-2017 Robin Cornett
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
	 * Common class: sets image ID, post title, handles database query
	 * @var Display_Featured_Image_Genesis_Common $common
	 */
	protected $common;

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
	 * Manages help tabs for settings page.
	 * @var $helptabs Display_Featured_Image_Genesis_HelpTabs
	 */
	protected $helptabs;

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
	 * @param $common
	 * @param $customizer
	 * @param $description
	 * @param $helptabs
	 * @param $output
	 * @param $rss
	 * @param $settings
	 * @param $taxonomies
	 */
	public function __construct( $admin, $author, $common, $customizer, $description, $helptabs, $output, $post_meta, $rss, $settings, $taxonomies, $widgets ) {
		$this->admin       = $admin;
		$this->author      = $author;
		$this->common      = $common;
		$this->customizer  = $customizer;
		$this->description = $description;
		$this->helptabs    = $helptabs;
		$this->output      = $output;
		$this->post_meta   = $post_meta;
		$this->rss         = $rss;
		$this->settings    = $settings;
		$this->taxonomies  = $taxonomies;
		$this->widgets     = $widgets;
	}

	/**
	 * Main plugin function. Starts up all the things.
	 */
	public function run() {
		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'deactivate' ) );
			return;
		}

		require plugin_dir_path( __FILE__ ) . 'helper-functions.php';

		// Plugin setup
		add_action( 'after_setup_theme', array( $this, 'add_plugin_supports' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . DISPLAYFEATUREDIMAGEGENESIS_BASENAME, array( $this, 'add_settings_link' ) );

		// Admin
		add_action( 'admin_init', array( $this->admin, 'set_up_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Widgets
		add_action( 'widgets_init', array( $this->widgets, 'register_widgets' ) );
		add_action( 'widgets_init', array( $this->widgets, 'register_shortcodes' ) );
		add_action( 'admin_enqueue_scripts', array( $this->widgets, 'enqueue_scripts' ) );

		// Taxonomies, Authors
		add_filter( 'displayfeaturedimagegenesis_get_taxonomies', array( $this->taxonomies, 'remove_post_status_terms' ) );
		add_action( 'admin_init', array( $this->taxonomies, 'set_taxonomy_meta' ) );
		add_action( 'admin_init', array( $this->author, 'set_author_meta' ) );

		// Post Meta
		add_action( 'enqueue_block_editor_assets', array( $this->post_meta, 'maybe_add_metabox' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this->post_meta, 'meta_box' ), 10, 2 );
		add_action( 'save_post', array( $this->post_meta, 'save_meta' ) );

		// Settings
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_filter( 'displayfeaturedimagegenesis_get_setting', array( $this->settings, 'get_display_setting' ) );
		add_action( 'load-appearance_page_displayfeaturedimagegenesis', array( $this->helptabs, 'help' ) );

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
	 * add plugin support for new image size and excerpts on pages, if move excerpts option is enabled
	 *
	 * @since 1.3.0
	 */
	public function add_plugin_supports() {

		$args = apply_filters( 'displayfeaturedimagegenesis_custom_image_size', array(
			'width'  => 2000,
			'height' => 2000,
			'crop'   => false,
		) );
		add_image_size( 'displayfeaturedimage_backstretch', (int) $args['width'], (int) $args['height'], (bool) $args['crop'] );

		$displaysetting = displayfeaturedimagegenesis_get_setting();
		if ( $displaysetting['move_excerpts'] ) {
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

		$version = $this->common->version;

		wp_register_script( 'displayfeaturedimage-upload', plugins_url( '/includes/js/settings-upload.js', dirname( __FILE__ ) ), array( 'jquery', 'media-upload', 'thickbox' ), $version );
		wp_register_script( 'widget_selector', plugins_url( '/includes/js/widget-selector.js', dirname( __FILE__ ) ), array( 'jquery' ), $version );

		$screen     = get_current_screen();
		$screen_ids = array(
			'appearance_page_displayfeaturedimagegenesis',
			'profile',
			'user-edit',
		);

		if ( in_array( $screen->id, $screen_ids, true ) || ! empty( $screen->taxonomy ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
			wp_localize_script( 'displayfeaturedimage-upload', 'objectL10n', array(
				'text' => __( 'Select Image', 'display-featured-image-genesis' ),
			) );
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
}
