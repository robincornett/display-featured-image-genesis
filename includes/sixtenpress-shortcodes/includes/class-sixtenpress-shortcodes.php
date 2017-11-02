<?php
/**
 * Copyright (c) 2017 Robin Cornett
 */

/**
 * Add a hook to initialize our code.
 */
add_action( 'admin_init', function () {
	if ( ! did_action( 'sixtenpress_shortcode_init' ) ) {
		do_action( 'sixtenpress_shortcode_init' );
	}
} );

/**
 * Call this function hooked into `sixtenpress_shortcode_init` for proper
 * timing and no failure.
 *
 * @param $shortcode
 * @param $args
 */
function sixtenpress_shortcode_register( $shortcode, $args ) {
	new SixTenPressShortcodes( $shortcode, $args );
}

/**
 * Class SixTenPressShortcodes
 */
class SixTenPressShortcodes {

	/**
	 * Flag to determine if media modal is loaded.
	 *
	 * @var object
	 */
	protected $loaded = false;

	/**
	 * The shortcode to register.
	 *
	 * @var string
	 */
	protected $shortcode;

	/**
	 * The custom args for the shortcode.
	 *
	 * @var array
	 */
	protected $shortcode_args;

	/**
	 * @var string
	 */
	protected $prefix = 'sixtenpress';

	/**
	 * SixTenPressShortcodes constructor.
	 *
	 * @param $shortcode
	 * @param $shortcode_args
	 */
	public function __construct( $shortcode, $shortcode_args ) {
		$this->shortcode      = $shortcode;
		$this->shortcode_args = $this->merge( $shortcode_args, $this->defaults() );
		if ( ! $this->shortcode_args ) {
			return;
		}
		foreach ( $this->hooks() as $hook ) {
			add_action( $hook, array( $this, 'start_editor' ) );
		}
	}

	/**
	 * Select which hooks to call the shortcode buttons on.
	 * load-{post}.php is the earliest.
	 *
	 * @return array
	 */
	protected function hooks() {
		return apply_filters( 'sixtenpress_shortcode_hooks', array( 'load-post.php', 'load-post-new.php' ) );
	}

	/**
	 * Merge any custom args with the defaults.
	 *
	 * @param $custom_args
	 * @param $defaults
	 *
	 * @return array
	 */
	protected function merge( $custom_args, $defaults ) {
		foreach ( array( 'button', 'labels' ) as $key ) {
			if ( array_key_exists( $key, $custom_args ) ) {
				$custom_args[ $key ] = wp_parse_args( $custom_args[ $key ], $defaults[ $key ] );
			}
		}
		$custom_args['slug'] = ! isset( $custom_args['slug'] ) && isset( $custom_args['modal'] ) ? $custom_args['modal'] : $custom_args['slug'];

		return wp_parse_args( $custom_args, $defaults );
	}

	/**
	 * Default shortcode args.
	 *
	 * @return array
	 */
	protected function defaults() {
		return array(
			'modal'  => false,
			'button' => array(
				'id'       => $this->prefix,
				'class'    => $this->prefix,
				'dashicon' => false,
				'label'    => __( 'Add Element', 'sixtenpress-shortcodes' ),
			),
			'self'   => true,
			'labels' => array(
				'title'  => __( 'Create', 'sixtenpress-shortcodes' ),
				'close'  => __( 'Close', 'sixtenpress-shortcodes' ),
				'cancel' => __( 'Cancel', 'sixtenpress-shortcodes' ),
				'insert' => __( 'Insert', 'sixtenpress-shortcodes' ),
			),
			'slug'   => $this->prefix,
			'group'  => array(),
		);
	}

	/**
	 * Load all needful functions for the editor.
	 */
	public function start_editor() {
		add_filter( 'sixtenpress_shortcode_localization', array( $this, 'localization_args' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'media_buttons', array( $this, 'media_buttons' ), 98 );
		add_action( 'admin_footer', array( $this, 'widget_builder_modal' ) );
		add_filter( 'sixtenpress_admin_color_picker', '__return_true' );
	}

	/**
	 * Adds a custom button beside the media uploader button.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The TinyMCE Editor ID
	 */
	public function media_buttons( $id ) {
		// Allow devs to override/cancel media button output.
		$show = apply_filters( 'sixtenpress_shortcode_media_button', true, $this->shortcode, $id );
		if ( ! $show ) {
			return;
		}
		do_action( 'sixtenpress_shortcode_before_media_button', $this->shortcode_args, $id );
		printf( '<button type="button" id="%1$s-%5$s" class="button %2$s" title="%4$s" data-editor="%5$s">%3$s%4$s</button>',
			esc_attr( $this->shortcode_args['button']['id'] ),
			esc_attr( $this->shortcode_args['button']['class'] ),
			$this->shortcode_args['button']['dashicon'] ? sprintf( '<span class="wp-media-buttons-icon dashicons %s"></span> ', esc_attr( $this->shortcode_args['button']['dashicon'] ) ) : '',
			esc_html( $this->shortcode_args['button']['label'] ),
			esc_attr( $id )
		);
		do_action( 'sixtenpress_shortcode_after_media_button', $this->shortcode_args, $id );
	}

	/**
	 * Enqueue the scripts needed for the modal.
	 */
	public function enqueue_script() {
		if ( wp_script_is( 'sixtenpress-editor-script', 'enqueued' ) ) {
			return;
		}
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'sixtenpress-shortcode-editor', plugin_dir_url( __FILE__ ) . "js/shortcode-editor{$minify}.js", array( 'jquery' ), SIXTENPRESSSHORTCODES_VERSION, true );
		add_action( 'admin_print_scripts', array( $this, 'localize' ) );
	}

	/**
	 * Enqueue the styles needed for the modal.
	 */
	public function enqueue_style() {
		if ( wp_style_is( 'sixtenpress-editor', 'enqueued' ) ) {
			return;
		}
		add_filter( 'sixtenpress_admin_style', '__return_true' );
		wp_enqueue_style( 'sixtenpress-shortcode-editor', plugin_dir_url( __FILE__ ) . 'css/sixtenpress-editor.css', array(), SIXTENPRESSSHORTCODES_VERSION, 'screen' );

		$css = apply_filters( 'sixtenpress_shortcode_inline_css', '' );
		if ( $css ) {
			wp_add_inline_style( 'sixtenpress-shortcode-editor', $this->minify_css( $css ) );
		}
	}

	/**
	 * Minify inline CSS a bit before outputting as inline style.
	 *
	 * @param $css
	 *
	 * @return string
	 */
	protected function minify_css( $css ) {
		$css = str_replace( "\t", '', $css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );

		return sanitize_text_field( strip_tags( $css ) );
	}

	/**
	 * Get the data for the script.
	 */
	public function localize() {
		wp_localize_script( 'sixtenpress-shortcode-editor', 'SixTenShortcodes', apply_filters( 'sixtenpress_shortcode_localization', array() ) );
	}

	/**
	 * Build the array of args for our script.
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function localization_args( $args ) {
		$new[ $this->shortcode ] = array(
			'modal'     => $this->shortcode_args['modal'],
			'button'    => $this->shortcode_args['button']['class'],
			'shortcode' => $this->shortcode,
			'self'      => $this->shortcode_args['self'],
			'slug'      => $this->shortcode_args['slug'],
			'group'     => (array) $this->shortcode_args['group'],
		);

		return array_merge( $args, $new );
	}

	/**
	 * Outputs the widget builder modal to insert a widget into an editor.
	 *
	 * @since 0.1.0
	 */
	public function widget_builder_modal() {

		if ( $this->loaded ) {
			return;
		}

		$this->loaded = true;
		include( plugin_dir_path( __FILE__ ) . 'modal.php' );
	}
}
