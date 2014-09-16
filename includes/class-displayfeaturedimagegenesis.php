<?php
/**
 * Send Images to RSS
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
	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
		add_action( 'genesis_before', array( $this, 'do_featured_image' ) );
	}

	/**
	 * set and retreive variables for the featured image.
	 * @return $image
	 *
	 * @since  1.0.0
	 */
	protected function get_image_variables() {
		$image = new stdClass();
		global $post;
		if ( is_home() ) {
			$postspage       = get_option( 'page_for_posts' );
			$image->original = wp_get_attachment_image_src( get_post_thumbnail_id( $postspage ), 'original' );
		}
		else {
			$image->original = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'original' );
		}
		$image->large   = get_option( 'large_size_w' );
		$image->medium  = get_option( 'medium_size_w' );
		$image->content = strpos( $post->post_content, $image->original[0] );

		return $image;

	}

	/**
	 * enqueue plugin styles and scripts.
	 * @return enqueue
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {
		$image = $this->get_image_variables();

		if ( has_post_thumbnail() && $image->content === false ) {

			wp_enqueue_style( 'displayfeaturedimage-style', plugins_url( 'includes/css/display-featured-image-genesis.css', dirname( __FILE__ ) ), array(), 1.0 );

			if ( ( $image->original[1] ) >= $image->large ) {
				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0' );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), '1.0.0' );

				wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchImg', array( 'src' => $image->original[0] ) );

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

		$image = $this->get_image_variables();

		if ( $image->content === false ) {
			if ( has_post_thumbnail( $post->ID ) && $image->original[1] > $image->large ) {
				$classes[] = 'has-leader';
			}
			elseif ( has_post_thumbnail( $post->ID ) && ( ( $image->original[1] <= $image->large ) && ( $image->original[1] > $image->medium ) ) ) {
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

		$image = $this->get_image_variables();

		if ( $image->content === false ) {
			if ( $image->original[1] > $image->large ) {
				add_action( 'genesis_after_header', array( $this, 'do_backstretch_image' ) );
			}
			elseif ( ( $image->original[1] <= $image->large ) && ( $image->original[1] > $image->medium ) ) {
				add_action( 'genesis_before_entry', array( $this, 'do_large_image' ) );
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
		global $post;

		$image = $this->get_image_variables();

		if ( ! is_home() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
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
		echo get_the_post_thumbnail( $post->ID, 'original', array( 'class' => 'aligncenter', 'alt' => the_title_attribute( 'echo=0' ) ) );
	}

}
