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

	function __construct( $common, $description, $output, $settings ) {
		$this->common   = $common;
		$this->archive  = $description;
		$this->output   = $output;
		$this->settings = $settings;
	}

	public function run() {
		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'deactivate' ) );
			add_action( 'admin_notices', array( $this, 'error_message' ) );
			return;
		}

		add_action( 'init', array( $this, 'add_plugin_supports' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_action( 'get_header', array( $this->output, 'manage_output' ) );
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
		if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
			echo '<div class="error"><p>' . sprintf(
				__( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. It has been deactivated.', 'display-featured-image-genesis' ) ) . '</p></div>';
		}
		else {
			echo '<div class="error"><p>' . sprintf(
				__( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. It has been deactivated. But since we&#39;re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'display-featured-image-genesis' ),
				PHP_VERSION
			) . '</p></div>';
		}

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
	 * Set up text domain for translations
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'display-featured-image-genesis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

}
