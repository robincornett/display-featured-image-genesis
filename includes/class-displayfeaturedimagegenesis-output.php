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
		$fallback = get_option( 'displayfeaturedimage_default' );
		if ( ( empty( $fallback ) && !is_home() && !is_singular() ) || ( in_array( get_post_type(), Display_Featured_Image_Genesis_Common::get_skipped_posttypes() ) ) ) {
			return;
		}
		else {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_filter( 'body_class', array( $this, 'add_body_class' ) );
		}
	}


	/**
	 * enqueue plugin styles and scripts.
	 * @return enqueue
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();
		if ( ( !empty( $item->original ) && $item->content === false ) || ( !is_singular() && !empty( $item->original ) ) ) {

			wp_enqueue_style( 'displayfeaturedimage-style', plugins_url( 'includes/css/display-featured-image-genesis.css', dirname( __FILE__ ) ), array(), 1.0 );

			if ( $item->original[1] > $item->large ) {
				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0' );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), '1.0.0' );

				wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', array(
					'src'    => esc_url( $item->original[0] ),
					'height' => esc_attr( $item->reduce )
				) );

				add_action( 'genesis_after_header', array( $this, 'do_backstretch_image_title' ) );
				add_action( 'genesis_before_loop', array( $this, 'move_titles' ) );

			}

			elseif ( ( $item->original[1] <= $item->large ) && ( $item->original[1] > $item->medium ) ) {
				add_action( 'genesis_before_entry', array( $this, 'do_large_image' ) ); // HTML5
				add_action( 'genesis_before_post', array( $this, 'do_large_image' ) );  // XHTML
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
		global $post;

		$item = Display_Featured_Image_Genesis_Common::get_image_variables();

		if ( ( !empty( $item->original ) && $item->content === false ) || ( !is_singular() && !empty( $item->original ) ) ) {
			if ( $item->original[1] > $item->large ) {
				$classes[] = 'has-leader';
			}
			elseif ( ( $item->original[1] <= $item->large ) && ( $item->original[1] > $item->medium ) ) {
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

		if ( is_singular() && !is_front_page() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
		}

		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );


		echo '<div class="big-leader"><div class="wrap">';
		$move_excerpts = get_option( 'displayfeaturedimage_excerpts' );

		if ( $move_excerpts ) {

			if ( !empty( $item->description ) && !in_array( get_post_type(), Display_Featured_Image_Genesis_Common::omit_excerpt() ) ) {
				Display_Featured_Image_Genesis_Description::do_excerpt();
				genesis_do_taxonomy_title_description();
				genesis_do_author_title_description();
				genesis_do_cpt_archive_title_description();
			}
			else {
				echo '<h1 class="entry-title featured-image-overlay">' . $item->title . '</h1>';
			}

		}

		else {
			if ( !empty( $item->title ) ) {
				echo '<h1 class="entry-title featured-image-overlay">' . $item->title . '</h1>';
			}

			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
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
		global $post;
		echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'aligncenter featured', 'alt' => the_title_attribute( 'echo=0' ) ) );
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
		$move_excerpts = get_option( 'displayfeaturedimage_excerpts' );

		if ( $move_excerpts ) {
			return;
		}
		else {
			Display_Featured_Image_Genesis_Description::do_tax_description();
			Display_Featured_Image_Genesis_Description::do_author_description();
			Display_Featured_Image_Genesis_Description::do_cpt_archive_description();
		}
	}

}
