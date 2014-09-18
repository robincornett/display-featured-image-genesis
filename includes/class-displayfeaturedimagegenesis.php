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
	function __construct( $output, $settings ) {
		$this->output   = $output;
		$this->settings = $settings;
	}

	public function run() {
		if ( basename( get_template_directory() ) !== 'genesis' ) {
			add_action( 'admin_notices', array( $this, 'error_message' ) );
			return;
		}
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this->settings, 'register_settings' ) );
		add_action( 'load-options-media.php', array( $this->settings, 'help' ) );
		add_action( 'wp_enqueue_scripts', array( $this->output, 'load_scripts' ) );
		add_filter( 'body_class', array( $this->output, 'add_body_class' ) );
	}

	/**
	 * Error message if we're not using the Genesis Framework.
	 *
	 * @since 1.1.0
	 */
	public function error_message() {
		echo '<div class="error"><p>' . sprintf(
			__( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. You can <a href="%1$s">deactivate the plugin</a>, since it is not working anyway, or you can <a href="%2$s">activate a Genesis child theme</a>.', 'display-featured-image-genesis' ),
			esc_url( admin_url( 'plugins.php' ) ),
			esc_url( admin_url( 'themes.php' ) )
			) . '</p></div>';
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
