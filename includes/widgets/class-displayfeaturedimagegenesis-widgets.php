<?php

class DisplayFeaturedImageGenesisWidgets {

	/**
	 * Register widgets for plugin
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {

		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() && ! function_exists( 'genesis' ) ) {
			return;
		}

		$widgets = array(
			'author'      => 'Display_Featured_Image_Genesis_Author_Widget',
			'cpt-archive' => 'Display_Featured_Image_Genesis_Widget_CPT',
			'taxonomy'    => 'Display_Featured_Image_Genesis_Widget_Taxonomy',
		);

		require_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-widgets-form.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-widgets-update.php';
		foreach ( $widgets as $file => $widget ) {
			require_once plugin_dir_path( __FILE__ ) . 'displayfeaturedimagegenesis-' . $file . '-widget.php';
			register_widget( $widget );
		}
	}

	/**
	 * Register widget shortcodes.
	 */
	public function register_shortcodes() {
		require_once plugin_dir_path( __FILE__ ) . 'class-displayfeaturedimagegenesis-widgets-shortcodes.php';
		$shortcode_class = new DisplayFeaturedImageGenesisWidgetsShortcodes();
		foreach ( array( 'author', 'post_type', 'term' ) as $shortcode ) {
			add_shortcode( "displayfeaturedimagegenesis_{$shortcode}", array( $shortcode_class, "shortcode_{$shortcode}" ) );
		}
		$setting = displayfeaturedimagegenesis_get_setting();
		if ( ! $setting['shortcodes'] ) {
			return;
		}
		add_filter( 'sixtenpress_shortcode_inline_css', array( $shortcode_class, 'inline_css' ) );
		add_action( 'sixtenpress_shortcode_init', array( $shortcode_class, 'shortcode_buttons' ) );
		add_action( 'sixtenpress_shortcode_modal', array( $shortcode_class, 'do_modal' ) );
		add_action( 'sixtenpress_shortcode_before_media_button', array( $shortcode_class, 'button_open' ) );
		add_action( 'sixtenpress_shortcode_after_media_button', array( $shortcode_class, 'button_close' ) );
		add_action( 'admin_enqueue_scripts', array( $shortcode_class, 'inline_script' ), 1000 );
	}

	/**
	 * Enqueue and localize widget scripts.
	 */
	public function enqueue_scripts() {
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() && ! function_exists( 'genesis' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( in_array( $screen->id, array( 'widgets', 'customize' ), true ) ) {
			wp_enqueue_script( 'widget_selector' );
			wp_localize_script( 'widget_selector', 'displayfeaturedimagegenesis_ajax_object', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}
}
