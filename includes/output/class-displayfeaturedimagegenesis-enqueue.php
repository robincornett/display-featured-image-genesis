<?php

/**
 * This class handles all styles/scripts to be enqueued.
 * Class DisplayFeaturedImageGenesisEnqueue
 *
 * @since 3.1.0
 */
class DisplayFeaturedImageGenesisEnqueue {

	/**
	 * The plugin setting.
	 * @var $setting
	 */
	private $setting;

	/**
	 * The plugin version.
	 * @var $version
	 */
	private $version;

	/**
	 * The featured image object.
	 * @var $item;
	 */
	private $item;

	/**
	 * DisplayFeaturedImageGenesisEnqueue constructor.
	 *
	 * @param $setting
	 * @param $item
	 * @param $version
	 */
	public function __construct( $setting, $item, $version ) {
		$this->setting = $setting;
		$this->item    = $item;
		$this->version = $version;
	}

	/**
	 * Enqueue the plugin stylesheet.
	 * @since 3.1.0
	 */
	public function enqueue_style() {
		$css_file = apply_filters( 'display_featured_image_genesis_css_file', plugin_dir_url( __FILE__ ) . 'css/display-featured-image-genesis.css' );
		wp_enqueue_style( 'displayfeaturedimage-style', esc_url( $css_file ), array(), $this->version );
		$this->add_inline_style();
	}

	/**
	 * All actions required to output the backstretch image
	 * @since 2.3.4
	 */
	public function enqueue_scripts() {
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'backstretch', plugins_url( "/includes/js/backstretch{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), '2.1.16', true );
		wp_enqueue_script(
			'displayfeaturedimage-backstretch-set',
			plugins_url( "/includes/js/backstretch-set{$minify}.js", dirname( __FILE__ ) ),
			array(
				'jquery',
				'backstretch',
			),
			$this->version,
			true
		);

		add_action( 'wp_print_scripts', array( $this, 'localize_scripts' ) );
	}

	/**
	 * Pass variables through to our js
	 *
	 * @since 2.3.0
	 */
	public function localize_scripts() {
		// backstretch settings which can be filtered
		$backstretch_vars = apply_filters(
			'display_featured_image_genesis_backstretch_variables',
			array(
				'centeredX' => $this->setting['centeredX'] ? 'center' : 'left',
				'centeredY' => $this->setting['centeredY'] ? 'center' : 'top',
				'fade'      => $this->setting['fade'],
			)
		);

		$image_id = Display_Featured_Image_Genesis_Common::set_image_id();
		$output   = array(
			'height' => (int) $this->setting['less_header'],
			'alignX' => $backstretch_vars['centeredX'],
			'alignY' => $backstretch_vars['centeredY'],
			'fade'   => (int) $backstretch_vars['fade'],
			'title'  => esc_attr( $this->get_image_alt_text( $image_id ) ),
		);

		wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', array_merge( $this->localize_sizes(), $output ) );
	}

	/**
	 * Get the size related localization data.
	 * @return array
	 * @since 3.1.0
	 */
	private function localize_sizes() {
		$image_id     = Display_Featured_Image_Genesis_Common::set_image_id();
		$large        = wp_get_attachment_image_src( $image_id, 'large' );
		$medium_large = wp_get_attachment_image_src( $image_id, 'medium_large' );
		$responsive   = apply_filters( 'displayfeaturedimagegenesis_responsive_backstretch', true );

		return array(
			'source'       => array(
				'backstretch'  => esc_url( $this->item->backstretch[0] ),
				'large'        => $large[3] && $responsive ? esc_url( $large[0] ) : '',
				'medium_large' => $medium_large[3] && $responsive ? esc_url( $medium_large[0] ) : '',
			),
			'width'        => array(
				'backstretch'  => $this->item->backstretch[1],
				'large'        => $large[3] ? $large[1] : '',
				'medium_large' => $medium_large[3] ? $medium_large[1] : '',
			),
			'image_height' => array(
				'backstretch'  => $this->item->backstretch[2],
				'large'        => $large[3] ? $large[2] : '',
				'medium_large' => $medium_large[3] ? $medium_large[2] : '',
			),
		);
	}

	/**
	 * Get the alt text for the featured image. Use the image alt text if filter is true.
	 *
	 * @param string $image_id
	 *
	 * @return mixed
	 */
	protected function get_image_alt_text( $image_id = '' ) {
		$alt_text = $this->item->title;
		if ( $image_id && apply_filters( 'displayfeaturedimagegenesis_prefer_image_alt', false ) ) {
			$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( $image_alt ) {
				$alt_text = $image_alt;
			}
		}

		return $alt_text;
	}

	/**
	 * Add max_height and scriptless CSS to output via inline style.
	 *
	 * @since 2.6.0
	 */
	private function add_inline_style() {
		$css  = $this->get_max_height_css();
		$css .= $this->get_image_css();
		wp_add_inline_style( 'displayfeaturedimage-style', wp_strip_all_tags( $css ) );
	}

	/**
	 * Get the max-height CSS.
	 * @return string
	 */
	private function get_max_height_css() {
		$max_height = $this->setting['max_height'];
		return $max_height ? ".big-leader { max-height: {$max_height}px; }" : '';
	}

	/**
	 * Get the CSS to modify the scriptless banner image.
	 * @return string
	 */
	private function get_image_css() {
		$css    = $this->get_object_position();
		$height = $this->setting['less_header'];
		if ( $height ) {
			$css .= "height: calc( 100vh - {$height}px );";
		}
		if ( $css ) {
			$css = ".big-leader--scriptless img { {$css} }";
		}

		return $css;
	}

	/**
	 * Add object-position to scriptless banner if needed.
	 *
	 * @return string
	 * @since 3.1.0
	 */
	private function get_object_position() {
		$this->setting = displayfeaturedimagegenesis_get_setting();
		if ( ! $this->setting['scriptless'] ) {
			return '';
		}
		if ( $this->setting['centeredX'] && $this->setting['centeredY'] ) {
			return '';
		}
		$x = $this->setting['centeredX'] ? '50%' : '0';
		$y = $this->setting['centeredY'] ? '50%' : '0';

		return "object-position: {$x} {$y};";
	}
}
