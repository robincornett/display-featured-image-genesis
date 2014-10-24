<?php

/**
 * Common functions for plugin
 *
 * @package DisplayFeaturedImageGenesis
 * @since 1.2.1
 */
class Display_Featured_Image_Genesis_Common {

	/**
	 * set and retreive variables for the featured image.
	 * @return $item
	 *
	 * @since  1.1.0
	 */
	static function get_image_variables() {
		$item = new stdClass();
		global $post;

		// variables internal to this function
		$frontpage          = get_option( 'show_on_front' ); // either 'posts' or 'page'
		$postspage          = get_option( 'page_for_posts' );
		$postspage_image    = get_post_thumbnail_id( $postspage );
		if ( is_singular() ) {
			$post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'displayfeaturedimage_backstretch' );
		}

		// variables used outside this function
		$item->fallback    = get_option( 'displayfeaturedimage_default' );
		$item->fallback_id = self::get_image_id( $item->fallback );
		$item->large       = get_option( 'large_size_w' );
		$item->medium      = get_option( 'medium_size_w' );
		$item->reduce      = get_option( 'displayfeaturedimage_less_header', 0 );

		// Set Featured Image Source
		if ( is_home() && $frontpage === 'page' && !empty( $postspage_image ) ) { // if on the blog page and it has a post_thumbnail
			$item->original = wp_get_attachment_image_src( $postspage_image, 'displayfeaturedimage_backstretch' );
		}
		// any singular post/page/CPT with either a post_thumbnail larger than medium size OR there is no $item->fallback
		elseif ( is_singular() && ( $post_thumbnail[1] > $item->medium || empty( $item->fallback ) ) && !in_array( get_post_type(), self::use_fallback_image() ) ) {
			$item->original = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'displayfeaturedimage_backstretch' );
		}
		// otherwise use $item->fallback. should include !is_singular AND $item->fallback, and is_singular with either a small image or no post_thumbnail
		else {
			$item->original = wp_get_attachment_image_src( $item->fallback_id, 'displayfeaturedimage_backstretch' );
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

		// declare this last so that $item->original is set.
		if ( !empty( $post->post_content ) ) {
			$item->content = strpos( $post->post_content, $item->original[0] );
		}
		else {
			$item->content = '';
		}

		return $item;

	}


	/**
	 * Get the ID of each image dynamically.
	 *
	 * @since 1.2.0
	 *
	 * @author Philip Newcomer
	 * @link   http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	static function get_image_id( $attachment_url ) {
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


	/**
	 * skip certain post types
	 * @return filter creates a new filter for themes/plugins to use to skip certain post types
	 *
	 * @since 1.0.1
	 */
	static function get_skipped_posttypes() {
		return apply_filters( 'display_featured_image_genesis_skipped_posttypes', array( 'attachment', 'revision', 'nav_menu_item' ) );
	}

	/**
	 * use fallback image as backstretch
	 * @return filter creates a new filter for themes/plugins to use to use the fallback image even if a large featured image is in place
	 *
	 * @since 1.2.0
	 */
	static function use_fallback_image() {
		return apply_filters( 'display_featured_image_genesis_use_default', array( 'attachment', 'revision', 'nav_menu_item' ) );
	}

}
