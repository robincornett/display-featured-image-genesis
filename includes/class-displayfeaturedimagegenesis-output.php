<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Output {

	protected $common;
	protected $description;
	protected $displaysetting;
	protected $item;

	public function __construct( $common, $description ) {
		$this->common         = $common;
		$this->description    = $description;
		$this->displaysetting = get_option( 'displayfeaturedimagegenesis' );
	}

	/**
	 * set parameters for scripts, etc. to run.
	 *
	 * @since 1.1.3
	 */
	public function manage_output() {

		$this->item    = Display_Featured_Image_Genesis_Common::get_image_variables();
		$skip          = $this->displaysetting['exclude_front'];
		$post_types    = array( 'attachment', 'revision', 'nav_menu_item' );
		$skipped_types = apply_filters( 'display_featured_image_genesis_skipped_posttypes', $post_types );

		if ( is_admin() || ( in_array( get_post_type(), $skipped_types ) ) || ( $skip && is_front_page() ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

	}


	/**
	 * enqueue plugin styles and scripts.
	 * @return enqueue
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {

		$version = $this->common->version;
		$large   = $this->common->minimum_backstretch_width();
		$medium  = absint( get_option( 'medium_size_w' ) );
		$width   = absint( $this->item->backstretch[1] );

		// check if they have enabled display on subsequent pages
		$is_paged = ! empty( $this->displaysetting['is_paged'] ) ? $this->displaysetting['is_paged'] : 0;

		// if there is no backstretch image set, or it is too small, or the image is in the content, or it's page 2+ and they didn't change the setting, die
		if ( empty( $this->item->backstretch ) || $width <= $medium || ( is_paged() && ! $is_paged ) || ( is_singular() && false !== $this->item->content ) ) {
			return;
		}

		$css_file = apply_filters( 'display_featured_image_genesis_css_file', plugin_dir_url( __FILE__ ) . 'css/display-featured-image-genesis.css' );
		wp_enqueue_style( 'displayfeaturedimage-style', esc_url( $css_file ), array(), $version );

		$force_backstretch = apply_filters( 'display_featured_image_genesis_force_backstretch', array() );
		// check if the image is large enough for backstretch
		if ( $width > $large || in_array( get_post_type(), $force_backstretch ) ) {

			wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), $version, true );
			wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), $version, true );

			$hook = apply_filters( 'display_featured_image_move_backstretch_image', 'genesis_after_header' );
			add_action( esc_attr( $hook ), array( $this, 'do_backstretch_image_title' ) );

		} elseif ( $width <= $large ) { // otherwise it's a large image.

			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			add_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description', 15 );

			$hook = 'genesis_before_loop';
			if ( is_singular() && ! is_page_template( 'page_blog.php' ) ) {
				$hook = apply_filters( 'display_featured_image_genesis_move_large_image', $hook );
			}
			add_action( esc_attr( $hook ), array( $this, 'do_large_image' ), 12 ); // works for both HTML5 and XHTML
		}
	}

	/**
	 * set body class if featured images are displayed using the plugin
	 * @param filter $classes body_class
	 *
	 * @since  1.0.0
	 */
	public function add_body_class( $classes ) {

		$large  = $this->common->minimum_backstretch_width();
		$medium = absint( get_option( 'medium_size_w' ) );
		$width  = absint( $this->item->backstretch[1] );

		// check if they have enabled display on subsequent pages
		$is_paged = ! empty( $this->displaysetting['is_paged'] ) ? $this->displaysetting['is_paged'] : 0;

		// if there is no backstretch image set, or it is too small, or it's page 2+ and they didn't change the setting, die
		if ( empty( $this->item->backstretch ) || $width <= $medium || ( is_paged() && ! $is_paged ) ) {
			return $classes;
		}

		if ( false === $this->item->content || ! is_singular() ) {
			if ( $width > $large ) {
				$classes[] = 'has-leader';
			} elseif ( $width <= $large ) {
				$classes[] = 'large-featured';
			}
		}
		return apply_filters( 'display_featured_image_genesis_classes', $classes );
	}

	/**
	 * backstretch image title ( for images which are larger than Media Settings > Large )
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image_title() {

		$keep_titles = $this->displaysetting['keep_titles'];

		// backstretch settings from plugin/featured image settings
		$backstretch_settings = array(
			'src'    => esc_url( $this->item->backstretch[0] ),
			'height' => absint( $this->displaysetting['less_header'] ),
		);
		// backstretch settings which can be filtered
		$backstretch_variables = array(
			'centeredX' => true,
			'centeredY' => true,
			'fade'      => 750,
		);

		$backstretch_variables = apply_filters( 'display_featured_image_genesis_backstretch_variables', $backstretch_variables );
		$output = array_merge( $backstretch_settings, $backstretch_variables );

		wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', $output );

		/**
		 * filter to maybe move titles, or not
		 * @var filter
		 * @since 2.2.0
		 */
		$do_not_move_title = apply_filters( 'display_featured_image_genesis_do_not_move_titles', array() );

		// if titles will be moved to overlay backstretch image
		if ( ! $keep_titles && ! in_array( get_post_type(), $do_not_move_title ) ) {
			if ( is_singular() && ! is_front_page() && ! is_page_template( 'page_blog.php' ) ) {
				remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
				remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
			}

			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
		}

		echo '<div class="big-leader">';
		echo '<div class="wrap">';

		do_action( 'display_featured_image_genesis_before_title' );

		$move_excerpts = $this->displaysetting['move_excerpts'];
		/**
		 * create a filter to not move excerpts if move excerpts is enabled
		 * @var filter
		 * @since  2.0.0 (deprecated old function from 1.3.3)
		 */
		$omit_excerpt = apply_filters( 'display_featured_image_genesis_omit_excerpt', array() );

		// if move excerpts is enabled
		if ( $move_excerpts && ! in_array( get_post_type(), $omit_excerpt ) ) {

			$this->description->do_front_blog_excerpt();
			$this->description->do_excerpt();
			genesis_do_taxonomy_title_description();
			genesis_do_author_title_description();
			genesis_do_cpt_archive_title_description();

		} elseif ( ! $keep_titles && ! in_array( get_post_type(), $do_not_move_title ) ) { // if titles are being moved to overlay the image

			if ( ! empty( $this->item->title ) && ! is_front_page() ) {

				$class = 'archive-title';
				if ( is_singular() ) {
					$class = 'entry-title';
				}

				$itemprop = '';
				if ( genesis_html5() ) {
					$itemprop = 'itemprop="headline"';
				}
				$title = $this->item->title;
				$title_output = sprintf( '<h1 class="%s featured-image-overlay" %s>%s</h1>', $class, $itemprop, $title );

				$title_output = apply_filters( 'display_featured_image_genesis_modify_title_overlay', $title_output, esc_attr( $class ), esc_attr( $itemprop ), $title );

				echo wp_kses_post( $title_output );

			}

			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );

			add_action( 'genesis_before_loop', array( $this, 'move_titles' ) );

		}

		do_action( 'display_featured_image_genesis_after_title' );

		// close wrap
		echo '</div>';

		// if javascript not enabled, do a fallback background image
		printf( '<noscript><div class="backstretch no-js" style="background-image: url(%s); }"></div></noscript>', esc_url( $this->item->backstretch[0] ) );

		// close big-leader
		echo '</div>';
	}

	/**
	 * Large image, centered above content
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_large_image() {
		$image = sprintf( '<img src="%1$s" class="aligncenter featured" alt="%2$s" />',
			esc_url( $this->item->backstretch[0] ),
			esc_attr( $this->item->title )
		);

		$image = apply_filters( 'display_featured_image_genesis_large_image_output', $image );

		echo wp_kses_post( $image );
	}

	/**
	 * Separate archive titles from descriptions. Titles show in leader image
	 * area; descriptions show before loop.
	 *
	 * @return descriptions
	 *
	 * @since  1.3.0
	 *
	 */
	public function move_titles() {

		$this->description->do_tax_description();
		$this->description->do_author_description();
		$this->description->do_cpt_archive_description();

	}

}
