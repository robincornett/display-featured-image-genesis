<?php
/**
 * Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2014 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * Main plugin class.
 *
 * @package DisplayFeaturedImageGenesis
 */
class Display_Featured_Image_Genesis {

	function __construct( $admin, $common, $cpt, $description, $output, $rss, $settings, $taxonomies ) {
		$this->admin      = $admin;
		$this->common     = $common;
		$this->cpt        = $cpt;
		$this->archive    = $description;
		$this->output     = $output;
		$this->rss        = $rss;
		$this->settings   = $settings;
		$this->taxonomies = $taxonomies;
	}

	public function run() {
		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'deactivate' ) );
			add_action( 'admin_notices', array( $this, 'error_message' ) );
			return;
		}

		add_action( 'init', array( $this, 'add_plugin_supports' ) );
		add_action( 'admin_init', array( $this, 'check_settings' ) );
		add_action( 'admin_init', array( $this->taxonomies, 'set_taxonomy_meta' ) );
		add_action( 'admin_init', array( $this->admin, 'set_up_taxonomy_columns' ) );
		add_action( 'admin_init', array( $this->admin, 'set_up_post_type_columns' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_action( 'admin_menu', array( $this->cpt, 'set_up_cpts' ) );
		add_action( 'get_header', array( $this->output, 'manage_output' ) );
		add_action( 'template_redirect', array( $this->rss, 'maybe_do_feed' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * deactivates the plugin if Genesis isn't running
	 *
	 *  @since 1.1.2
	 *
	 */
	public function deactivate() {
		if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
			deactivate_plugins( plugin_basename( dirname( __DIR__ ) ) . '/display-featured-image-genesis.php' ); // __DIR__ is a magic constant introduced in PHP 5.3
		}
		else {
			deactivate_plugins( plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/display-featured-image-genesis.php' );
		}
	}

	/**
	 * Error message if we're not using the Genesis Framework.
	 *
	 * @since 1.1.0
	 */
	public function error_message() {

		$error = sprintf(
			__( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. It has been deactivated.', 'display-featured-image-genesis' ) );

		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			$error = $error . sprintf(
				__( ' But since we\'re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'display-featured-image-genesis' ),
				PHP_VERSION
			);
		}

		echo '<div class="error"><p>' . $error . '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

	}


	/**
	 * add plugin support for new image size and excerpts on pages, if move excerpts option is enabled
	 *
	 * @since 1.3.0
	 */
	function add_plugin_supports() {
		add_image_size( 'displayfeaturedimage_backstretch', 2000, 2000, false );

		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		if ( $displaysetting['move_excerpts'] ) {
			add_post_type_support( 'page', 'excerpt' );
		}
	}

	/**
	 * check existing settings array to see if a setting is in the array
	 * @return updated setting updates to default (0)
	 * @since  1.5.0
	 */
	public function check_settings() {

		$displaysetting = get_option( 'displayfeaturedimagegenesis' );

		//* return early if the option doesn't exist yet
		if ( empty( $displaysetting ) ) {
			return;
		}

		if ( empty( $displaysetting['feed_image'] ) ) {
			$this->update_settings( array(
				'feed_image' => 0
			) );
		}

		//* new setting for titles added in x.y.z
		if ( empty( $displaysetting['keep_titles'] ) ) {
			$this->update_settings( array(
				'keep_titles' => 0
			) );
		}

	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 1.5.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is displayfeaturedimagegenesis.
	 */
	protected function update_settings( $new = '', $setting = 'displayfeaturedimagegenesis' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'display-featured-image-genesis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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

		// if ( 'appearance_page_displayfeaturedimagegenesis' === get_current_screen()->id || ! empty( get_current_screen()->taxonomy ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
			wp_localize_script( 'displayfeaturedimage-upload', 'objectL10n', array(
				'text' => __( 'Choose Image', 'display-featured-image-genesis' ),
			) );
		// }

	}

}
