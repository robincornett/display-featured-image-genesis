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
		if ( ( empty( $fallback ) && !is_home() && !is_singular() ) || ( in_array( get_post_type(), $this->get_skipped_posttypes() ) ) ) {
			return;
		}
		else {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_filter( 'body_class', array( $this, 'add_body_class' ) );
		}
	}

	/**
	 * set and retreive variables for the featured image.
	 * @return $item
	 *
	 * @since  1.1.0
	 */
	protected function get_image_variables() {
		$item = new stdClass();
		global $post;
		$fallback        = get_option( 'displayfeaturedimage_default' );
		$frontpage       = get_option( 'show_on_front' ); // either 'posts' or 'page'
		$postspage       = get_option( 'page_for_posts' );
		$postspage_image = get_post_thumbnail_id( $postspage );

		// Set Featured Image Source
		if ( is_home() && $frontpage === 'page' && !empty( $postspage_image ) ) {
			$item->original = wp_get_attachment_image_src( $postspage_image, 'original' );
		}
		elseif ( is_singular() && has_post_thumbnail() ) {
			$item->original = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'original' );
		}
		elseif ( !empty( $fallback ) ) {
			$fallback_id    = $this->get_image_id( $fallback );
			$item->original = wp_get_attachment_image_src( $fallback_id, 'original' );
		}
		else {
			$item->original = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'original' );
		}

		// Set Post/Page Title
		if ( is_singular() && ! is_front_page() ) {
			$item->title = get_the_title();
		}
		elseif ( is_home() && $frontpage === 'page' ) {
			$item->title = get_post( $postspage )->post_title;
		}
		else {
			$item->title = '';
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

		$item = $this->get_image_variables();
		if ( ( !empty( $item->original ) && $item->content === false ) || is_home() ) {

			wp_enqueue_style( 'displayfeaturedimage-style', plugins_url( 'includes/css/display-featured-image-genesis.css', dirname( __FILE__ ) ), array(), 1.0 );

			if ( ( ( $item->original[1] ) > $item->large ) ) {
				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0' );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), '1.0.0' );

				wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', array(
					'src'    => $item->original[0],
					'height' => esc_attr( $item->reduce )
				));

				add_action( 'genesis_after_header', array( $this, 'do_backstretch_image_title' ) );

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

		$item = $this->get_image_variables();

		if ( !empty( $item->original ) && $item->content === false ) {
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

		$item = $this->get_image_variables();

		if ( is_singular() || !is_front_page() ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
		}

		echo '<div class="big-leader"><div class="wrap">';
		echo '<h1 class="entry-title">' . $item->title . '</h1>';
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
	 * Get the ID of each image dynamically.
	 *
	 * @since 1.2.0
	 *
	 * @author Philip Newcomer
	 * @link   http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	protected function get_image_id( $attachment_url ) {
		global $wpdb;
		$attachment_id = false;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '(-\d{3,4}x\d{3,4}.)', '.', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}

}
