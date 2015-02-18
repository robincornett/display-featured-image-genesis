<?php

/**
 * Common functions for plugin
 *
 * @package DisplayFeaturedImageGenesis
 * @since 1.2.1
 */
class Display_Featured_Image_Genesis_Common {

	/**
	 * current plugin version
	 * @var string
	 * @since  1.4.3
	 */
	public static $version = '2.1.0';

	protected static $post_types;

	/**
	 * set and retreive variables for the featured image.
	 * @return $item
	 *
	 * @since  1.1.0
	 */
	public static function get_image_variables() {

		self::$post_types = array();

		$item = new stdClass();

		// variables internal to this function
		$frontpage       = get_option( 'show_on_front' ); // either 'posts' or 'page'
		$postspage       = get_option( 'page_for_posts' );
		$displaysetting  = get_option( 'displayfeaturedimagegenesis' );
		$move_excerpts   = $displaysetting['move_excerpts'];
		$postspage_image = get_post_thumbnail_id( $postspage );

		if ( is_singular() ) { // just checking for handling conditional variables set by width
			$thumb_metadata = wp_get_attachment_metadata( get_post_thumbnail_id( get_the_ID() ) ); // needed only for the next line
			$width = '';
			if ( $thumb_metadata ) {
				$width = $thumb_metadata['width'];
			}
		}

		// sitewide variables used outside this function
		$item->backstretch = '';
		$item->fallback    = esc_attr( $displaysetting['default'] ); // url only
		$item->fallback_id = self::get_image_id( $item->fallback ); // gets image id with attached metadata
		$item->large       = absint( get_option( 'large_size_w' ) );
		$item->medium      = absint( get_option( 'medium_size_w' ) );
		$item->reduce      = absint( $displaysetting['less_header'] );

		// Set Featured Image source ID
		$image_id = ''; // blank if nothing else

		/**
		 * create a filter to use the fallback image
		 * @var filter
		 * @since  2.0.0 (deprecated old use_fallback_image function from 1.2.2)
		 */
		$use_fallback = apply_filters( 'display_featured_image_genesis_use_default', self::$post_types );

		// set here with fallback preemptively, if it exists
		if ( ! empty( $item->fallback ) ) {
			$image_id = $item->fallback_id;
		}

		// outlier: if it's a home page with a static front page, and there is a featured image set on the home page
		if ( is_home() && 'page' === $frontpage && ! empty( $postspage_image ) ) {
			$image_id = $postspage_image;
		}

		$object = get_queried_object();
		if ( ! is_author() && ! is_admin() ) {
			if ( $object->name ) { // results in post type on cpt archive
				$post_type = $object->name;
			}
			elseif ( $object->taxonomy ) { // on a tax/term/category
				$tax_object = get_taxonomy( $object->taxonomy );
				$post_type  = $tax_object->object_type[0];
			}
			elseif ( $object->post_type ) { // on singular
				$post_type = $object->post_type;
			}
			if ( ! empty( $displaysetting['post_type'][$post_type] ) ) {
				$image_id = self::get_image_id( $displaysetting['post_type'][$post_type] );
			}
		}
		// taxonomy
		if ( is_category() || is_tag() || is_tax() ) {
			$t_id      = $object->term_id;
			$term_meta = get_option( "displayfeaturedimagegenesis_$t_id" );
			// if there is a term image
			if ( ! empty( $term_meta['term_image'] ) ) {
				$image_id = self::get_image_id( $term_meta['term_image'] );
			}
		}
		// any singular post/page/CPT or there is no $item->fallback
		elseif ( is_singular() && ! in_array( get_post_type(), $use_fallback ) ) {
			/**
			 * create filter to use taxonomy image if single post doesn't have a thumbnail, but one of its terms does.
			 * @var filter
			 */
			$use_tax_image = apply_filters( 'display_featured_image_genesis_use_taxonomy', self::$post_types );

			if ( has_post_thumbnail() && $width > $item->medium ) {
				$image_id = get_post_thumbnail_id( get_the_ID() );
			}

			elseif ( ! has_post_thumbnail() || in_array( get_post_type(), $use_tax_image ) ) {
				$term_image = display_featured_image_genesis_get_term_image();
				if ( ! empty( $term_image ) ) {
					$image_id = $term_image;
				}
			}
		}

		// turn Photon off so we can get the correct image
		$photon_removed = '';
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'photon' ) ) {
			$photon_removed = remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );
		}

		/**
		 * create a filter for user to optionally force post types to use the large image instead of backstretch
		 * @var filter
		 *
		 * @since  2.0.0
		 */
		$use_large_image = apply_filters( 'display_featured_image_genesis_use_large_image', self::$post_types );
		$image_size      = 'displayfeaturedimage_backstretch';
		if ( in_array( get_post_type(), $use_large_image ) ) {
			$image_size = 'large';
		}
		$item->backstretch = wp_get_attachment_image_src( $image_id, $image_size );

		$item->width = '';
		if ( ! empty( $item->backstretch ) ) {
			$item->width = $item->backstretch[1];
		}

		// set a content variable so backstretch doesn't show if full size image exists in post.
		$item->content = '';
		// declare this last so that $item->backstretch is set.
		if ( ! is_admin() && is_singular() ) {
			$fullsize      = wp_get_attachment_image_src( $image_id, 'original' );
			$post          = get_post();
			$item->content = strpos( $post->post_content, 'src="' . $fullsize[0] );

			if ( false !== $item->content ) {
				$term_image = display_featured_image_genesis_get_term_image();
				// reset backstretch image source to term image if it exists and the featured image is being used in content.
				if ( ! empty( $term_image ) ) {
					$item->backstretch = wp_get_attachment_image_src( $term_image, $image_size );
					$item->content     = strpos( $post->post_content, 'src="' . $item->backstretch[0] );
				}
				// else, reset backstretch image source to fallback.
				elseif ( ! empty( $item->fallback ) ) {
					$item->backstretch = wp_get_attachment_image_src( $item->fallback_id, $image_size );
					$item->content     = strpos( $post->post_content, 'src="' . $item->backstretch[0] );
				}
			}
		}

		// turn Photon back on
		if ( $photon_removed ) {
			add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );
		}

		// Set Post/Page Title
		$title = '';

		if ( is_singular() ) {
			$title = get_the_title();
		}
		elseif ( is_home() && 'page' === $frontpage ) {
			$title = get_post( $postspage )->post_title;
		}
		elseif ( is_category() || is_tag() || is_tax() ) {
			$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : get_queried_object();
			if ( ! $term || ! isset( $term->meta ) ) {
				return;
			}
			$title = $term->meta['headline'];
		}
		elseif ( is_author() ) {
			$title = get_the_author_meta( 'headline', (int) get_query_var( 'author' ) );
		}
		elseif ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			$title = genesis_get_cpt_option( 'headline' );
		}
		$item->title = apply_filters( 'display_featured_image_genesis_title', $title );

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
	public static function get_image_id( $attachment_url ) {
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
