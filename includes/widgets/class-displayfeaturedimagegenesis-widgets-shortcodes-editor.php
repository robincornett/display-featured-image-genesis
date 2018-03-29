<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsShortcodesEditor
 * @package DisplayFeaturedImageGenesis
 */
class DisplayFeaturedImageGenesisWidgetsShortcodesEditor {

	/**
	 * Add media shortcode buttons to the editor.
	 */
	public function shortcode_buttons() {
		$widgets = array(
			'displayfeaturedimagegenesis_term'      => __( 'Add Featured Term Widget', 'display-featured-image-genesis' ),
			'displayfeaturedimagegenesis_author'    => __( 'Add Featured Author Widget', 'display-featured-image-genesis' ),
			'displayfeaturedimagegenesis_post_type' => __( 'Add Featured Post Type Widget', 'display-featured-image-genesis' ),
		);
		foreach ( $widgets as $widget => $button_label ) {
			sixtenpress_shortcode_register( $widget, array(
				'modal'  => $widget,
				'button' => array(
					'id'    => "{$widget}-create",
					'class' => "displayfeaturedimagegenesis {$widget}-create",
					'label' => $button_label,
				),
				'self'   => true,
				'labels' => array(
					'title'  => __( 'Create Widget', 'display-featured-image-genesis' ),
					'insert' => __( 'Insert Widget', 'display-featured-image-genesis' ),
				),
			) );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 50 );
	}

	/**
	 * Enqueue the scripts needed for the term widget.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->base ) {
			return;
		}
		wp_enqueue_script( 'widget_selector' );
		wp_localize_script( 'widget_selector', 'displayfeaturedimagegenesis_ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );

		if ( ! wp_script_is( 'sixtenpress-shortcode-editor', 'enqueued' ) ) {
			return;
		}
		wp_enqueue_script( 'displayfeaturedimagegenesis-editor', plugin_dir_url( dirname( __FILE__ ) ) . 'js/editor.js', array( 'sixtenpress-shortcode-editor' ), '0.1.0beta', true );
		wp_localize_script( 'displayfeaturedimagegenesis-editor', 'DisplayFeaturedImageVar', array(
			'text' => __( 'Image Shortcodes', 'display-featured-image-genesis' ),
		) );
	}

	/**
	 * Add the widget forms to the modal.
	 *
	 * @param $shortcode
	 */
	public function do_modal( $shortcode ) {
		$widgets = $this->get_widgets();
		foreach ( $widgets as $shortcode_text => $widget ) {
			if ( $shortcode_text === $shortcode ) {
				$class = new $widget();
				$class->form( array() );
			}
		}
	}

	/**
	 * Modify our modals' CSS.
	 *
	 * @param $css
	 *
	 * @return string
	 */
	public function inline_css( $css ) {
		return $css . '.displayfeaturedimage-wrapper { display: inline-block; position: relative; }
		.displayfeaturedimage-buttons-wrap { display: none; width: 200px; position: absolute; z-index: 100; left: 50%; margin-left: -100px; }
		.displayfeaturedimage-buttons-wrap .button { width: 100%; }
		.displayfeaturedimagegenesis_term .media-modal-content, .displayfeaturedimagegenesis_post_type .media-modal-content {max-width: 500px;}
		.displayfeaturedimagegenesis_author .media-modal-content {max-width: 400px;}';
	}

	/**
	 * Get the plugin widgets/class names.
	 * @return array
	 */
	protected function get_widgets() {
		return array(
			'displayfeaturedimagegenesis_term'      => 'Display_Featured_Image_Genesis_Widget_Taxonomy',
			'displayfeaturedimagegenesis_author'    => 'Display_Featured_Image_Genesis_Author_Widget',
			'displayfeaturedimagegenesis_post_type' => 'Display_Featured_Image_Genesis_Widget_CPT',
		);
	}
}
