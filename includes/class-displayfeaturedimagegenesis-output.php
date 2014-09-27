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
	 * set and retreive variables for the featured image.
	 * @return $item
	 *
	 * @since  1.1.0
	 */
	protected function get_image_variables() {
		$item = new stdClass();
		global $post;
		if ( is_home() ) {
			$postspage      = get_option( 'page_for_posts' );
			$item->original = wp_get_attachment_image_src( get_post_thumbnail_id( $postspage ), 'original' );
		}
		else {
			$item->original = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'original' );
		}
		$item->large   = get_option( 'large_size_w' );
		$item->medium  = get_option( 'medium_size_w' );
		$item->reduce  = get_option( 'displayfeaturedimage_less_header', 0 );
		$item->content = strpos( $post->post_content, $item->original[0] );

		return $item;

	}

	/**
	 * skip certain post types
	 * @return filter creates a new filter for themes/plugins to use to skip certain post types
	 *
	 * @since 1.0.1
	 */
	public function get_skipped_posttypes() {
		return apply_filters( 'display_featured_image_genesis_skipped_posttypes', array( 'attachment', 'revision', 'nav_menu_item' ) );
	}

	/**
	 * enqueue plugin styles and scripts.
	 * @return enqueue
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {

		if ( !is_home() && in_array( get_post_type(), $this->get_skipped_posttypes() ) ) {
			return;
		}

		$item = $this->get_image_variables();
		if ( ( has_post_thumbnail() && $item->content === false ) || is_home() ) {

			wp_enqueue_style( 'displayfeaturedimage-style', plugins_url( 'includes/css/display-featured-image-genesis.css', dirname( __FILE__ ) ), array(), 1.0 );

			add_action( 'genesis_before', array( $this, 'do_featured_image' ) );

			if ( ( $item->original[1] ) > $item->large ) {
				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0' );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), '1.0.0' );

				wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', array(
					'src' => $item->original[0],
					'height' => esc_attr( $item->reduce )
				));

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

		$item = $this->get_image_variables();

		if ( $item->content === false ) {
			if ( ( has_post_thumbnail() || is_home() ) && $item->original[1] > $item->large ) {
				$classes[] = 'has-leader';
			}
			elseif ( has_post_thumbnail() && ( ( $item->original[1] <= $item->large ) && ( $item->original[1] > $item->medium ) ) ) {
				$classes[] = 'large-featured';
			}
		}
		return $classes;
	}

	/**
	 * do the featured image
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_featured_image() {
		global $post;

		$item = $this->get_image_variables();

		if ( $item->content === false ) {
			if ( $item->original[1] > $item->large ) {
				add_action( 'genesis_after_header', array( $this, 'do_backstretch_image' ) );
			}
			elseif ( ( $item->original[1] <= $item->large ) && ( $item->original[1] > $item->medium ) ) {
				add_action( 'genesis_before_entry', array( $this, 'do_large_image' ) ); // HTML5
				add_action( 'genesis_before_post', array( $this, 'do_large_image' ) );  // XHTML
			}
		}

	}

	/**
	 * backstretch image (for images which are larger than Media Settings > Large )
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image() {

		$item = $this->get_image_variables();

		if ( ! is_home() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
		}

		echo '<div class="big-leader"><div class="wrap">';
		if ( is_home() ) {
			$title = get_post( get_option( 'page_for_posts' ) )->post_title;
			echo '<h1 class="entry-title">' . $title . '</h1>';
		}
		else {
			echo '<h1 class="entry-title">' . get_the_title() . '</h1>';
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

}
