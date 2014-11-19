<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Output {

	/**
	 * set parameters for scripts, etc. to run.
	 *
	 * @since 1.1.3
	 */
	public function manage_output() {
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$fallback       = $displaysetting['default'];
		if ( is_admin() || ( empty( $fallback ) && ! is_home() && ! is_singular() ) || ( in_array( get_post_type(), Display_Featured_Image_Genesis_Common::get_skipped_posttypes() ) ) ) {
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

		$version = Display_Featured_Image_Genesis_Common::$version;
		$item    = Display_Featured_Image_Genesis_Common::get_image_variables();

		// if there is no backstretch image set, or it is too small, die
		if ( empty( $item->backstretch ) || $item->width <= $item->medium ) {
			return;
		}
		// if the featured image is not part of the content, or we're not on a singular page, carry on
		if ( false === $item->content || ! is_singular() ) {

			wp_enqueue_style( 'displayfeaturedimage-style', plugins_url( 'includes/css/display-featured-image-genesis.css', dirname( __FILE__ ) ), array(), $version );

			//check if the image is large enough for backstretch
			if ( $item->width > $item->large ) {
				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), $version, true );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), $version, true );

				wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', array(
					'src'    => esc_url( $item->backstretch[0] ),
					'height' => esc_attr( $item->reduce )
				) );

				add_action( 'genesis_after_header', array( $this, 'do_backstretch_image_title' ) );

			}

			// otherwise it's a large image.
			elseif ( $item->width <= $item->large ) {
				add_action( 'genesis_before_loop', array( $this, 'do_large_image' ) ); // works for both HTML5 and XHTML
			}
		}
	}

	/**
	 * set body class if featured images are displayed using the plugin
	 * @param filter $classes body_class
	 *
	 * @since  1.0.0
	 */
	public function add_body_class( $classes ) {

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		if ( empty( $item->backstretch ) || $item->width <= $item->medium ) {
			return $classes;
		}

		if ( false === $item->content || ! is_singular() ) {
			if ( $item->width > $item->large ) {
				$classes[] = 'has-leader';
			}
			elseif ( $item->width <= $item->large ) {
				$classes[] = 'large-featured';
			}
		}
		return $classes;
	}

	/**
	 * backstretch image title (for images which are larger than Media Settings > Large )
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image_title() {

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		if ( is_singular() && ! is_front_page() && ! is_page_template( 'page_blog.php' ) ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
		}

		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );


		echo '<div class="big-leader"><div class="wrap">';
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$move_excerpts  = $displaysetting['move_excerpts'];

		// if move excerpts is enabled
		if ( $move_excerpts && ! in_array( get_post_type(), Display_Featured_Image_Genesis_Common::omit_excerpt() ) ) {

			Display_Featured_Image_Genesis_Description::do_front_blog_excerpt();
			Display_Featured_Image_Genesis_Description::do_excerpt();
			genesis_do_taxonomy_title_description();
			genesis_do_author_title_description();
			genesis_do_cpt_archive_title_description();

		}

		else {

			if ( ! empty( $item->title ) && ! is_front_page() ) {
				echo '<h1 class="entry-title featured-image-overlay">' . esc_attr( $item->title ) . '</h1>';
			}

			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );

			add_action( 'genesis_before_loop', array( $this, 'move_titles' ) );

		}
		echo '</div></div>';
	}

	/**
	 * Large image, centered above content
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_large_image() {
		$item  = Display_Featured_Image_Genesis_Common::get_image_variables();
		$image = sprintf( '<img src="%1$s" class="aligncenter featured" alt="%2$s" title="%2$s" />',
			esc_url( $item->backstretch[0] ),
			esc_attr( $item->title )
		);

		echo $image;
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

		Display_Featured_Image_Genesis_Description::do_tax_description();
		Display_Featured_Image_Genesis_Description::do_author_description();
		Display_Featured_Image_Genesis_Description::do_cpt_archive_description();

	}

}
