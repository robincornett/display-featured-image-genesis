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
	public $version = '2.5.0';

	/**
	 * set and retrieve variables for the featured image.
	 * @return $item
	 *
	 * @since  1.1.0
	 */
	public static function get_image_variables() {

		$item = new stdClass();

		// variables internal to this function
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$fallback       = $displaysetting['default']; // url only

		// sitewide variables used outside this function
		$item->backstretch = '';

		add_filter( 'jetpack_photon_override_image_downsize', '__return_true' ); // turn Photon off so we can get the correct image

		$image_id   = self::set_image_id();
		$image_size = self::image_size();
		$item->backstretch = wp_get_attachment_image_src( $image_id, $image_size );

		// set a content variable so backstretch doesn't show if full size image exists in post.
		$item->content = '';
		// declare this last so that $item->backstretch is set.
		if ( ! is_admin() && is_singular() ) {
			$fullsize      = wp_get_attachment_image_src( $image_id, 'full' );
			$post          = get_post();
			$item->content = strpos( $post->post_content, 'src="' . $fullsize[0] );

			if ( false !== $item->content ) {
				$source_id     = '';
				$term_image    = display_featured_image_genesis_get_term_image_id();
				$default_image = display_featured_image_genesis_get_default_image_id();
				// reset backstretch image source to term image if it exists and the featured image is being used in content.
				if ( ! empty( $term_image ) ) {
					$source_id = $term_image;
				} elseif ( ! empty( $fallback ) ) {
					// else, reset backstretch image source to fallback.
					$source_id = $default_image;
				}
				$item->backstretch = wp_get_attachment_image_src( $source_id, $image_size );
				$item->content     = strpos( $post->post_content, 'src="' . $item->backstretch[0] );
			}
		}

		// $title is set by new title function
		$title = self::set_item_title();

		/**
		 * Optional filter to change the title text
		 * @since 2.2.0
		 */
		$item->title = apply_filters( 'display_featured_image_genesis_title', $title );

		return $item;

	}

	/**
	 * retrieve image ID for output
	 * @param string $image_id variable, ID of featured image
	 *
	 * @since 2.2.1
	 */
	public static function set_image_id( $image_id = '' ) {

		$setting     = get_option( 'displayfeaturedimagegenesis' );
		$fallback    = $setting['default'];
		$fallback_id = displayfeaturedimagegenesis_check_image_id( $fallback );

		// set here with fallback preemptively, if it exists
		if ( ! empty( $fallback ) ) {
			/**
			 * Creates display_featured_image_genesis_use_default filter to check
			 * whether get_post_type array should use default image.
			 * @uses is_in_array()
			 */
			$image_id = $fallback_id;
			if ( self::is_in_array( 'use_default' ) ) {
				return (int) $image_id;
			}
		}

		// outlier: if it's a home page with a static front page, and there is a featured image set on the home page
		// also provisionally sets featured image for posts, similar to CPT archives
		$frontpage = get_option( 'show_on_front' ); // either 'posts' or 'page'
		if ( 'page' === $frontpage ) {
			$postspage                    = get_option( 'page_for_posts' );
			$postspage_image              = get_post_thumbnail_id( $postspage );
			$setting['post_type']['post'] = (int) $postspage_image;
		}

		// if a post type image exists, it takes priority over the fallback. check that next.
		$post_type = get_post_type();
		if ( ! empty( $setting['post_type'][ $post_type ] ) ) {
			/**
			 * Creates display_featured_image_genesis_use_post_type_image filter to check
			 * whether get_post_type array should use the post type image.
			 * @uses is_in_array()
			 */
			$image_id = displayfeaturedimagegenesis_check_image_id( $setting['post_type'][ $post_type ] );
			if ( self::is_in_array( 'use_post_type_image' ) ) {
				return (int) $image_id;
			}
		}
		if ( is_author() ) {
			$image_id = get_the_author_meta( 'displayfeaturedimagegenesis', (int) get_query_var( 'author' ) );
		}
		// taxonomy
		if ( is_category() || is_tag() || is_tax() ) {
			$object = get_queried_object();
			$image  = displayfeaturedimagegenesis_get_term_image( $object->term_id );
			if ( $image ) {
				$image_id = $image;
			}
		}

		// search page
		if ( is_search() && isset( $setting['post_type']['search'] ) ) {
			$image_id = $setting['post_type']['search'];
		}

		// 404
		if ( is_404() && isset( $setting['post_type']['fourohfour'] ) ) {
			$image_id = $setting['post_type']['fourohfour'];
		}

		// any singular post/page/CPT
		if ( is_singular() ) {

			$term_image = display_featured_image_genesis_get_term_image_id();
			if ( ! empty( $term_image ) ) {
				/**
				 * Creates display_featured_image_genesis_use_taxonomy filter to check
				 * whether get_post_type array should use the term image.
				 * @uses is_in_array()
				 */
				$image_id = $term_image;
				if ( self::is_in_array( 'use_taxonomy' ) ) {
					return (int) $image_id;
				}
			}

			if ( isset( $setting['fallback'][ $post_type ] ) && ! $setting['fallback'][ $post_type ] ) {
				$thumb_metadata = wp_get_attachment_metadata( get_post_thumbnail_id( get_the_ID() ) ); // needed only for the next line
				$width          = $thumb_metadata ? $thumb_metadata['width'] : '';
				$medium         = (int) apply_filters( 'displayfeaturedimagegenesis_set_medium_width', get_option( 'medium_size_w' ) );
				if ( has_post_thumbnail() && $width > $medium ) {
					$image_id = get_post_thumbnail_id( get_the_ID() );
				}
			}
		}

		/**
		 * filter to use a different image id
		 * @var $image_id
		 *
		 * @since 2.2.0
		 */
		$image_id = apply_filters( 'display_featured_image_genesis_image_id', $image_id );

		// make sure the image id is an integer
		$image_id = is_numeric( $image_id ) ? (int) $image_id : '';

		return $image_id;

	}

	/**
	 * @param string $title
	 *
	 * @return mixed|void
	 */
	protected static function set_item_title( $title = '' ) {

		$frontpage = get_option( 'show_on_front' ); // either 'posts' or 'page'
		$postspage = get_option( 'page_for_posts' );
		$a11ycheck = current_theme_supports( 'genesis-accessibility', array( 'headings' ) );

		if ( is_singular() ) {
			$title = get_the_title();
		} elseif ( is_home() && 'page' === $frontpage ) {
			$title = get_post( $postspage )->post_title;
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : get_queried_object();
			if ( ! $term ) {
				return;
			}
			$title = displayfeaturedimagegenesis_get_term_meta( $term, 'headline' );
			if ( empty( $title ) && $a11ycheck ) {
				$title = $term->name;
			}
		} elseif ( is_author() ) {
			$title = get_the_author_meta( 'headline', (int) get_query_var( 'author' ) );
			if ( empty( $title ) && $a11ycheck ) {
				$title = get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) );
			}
		} elseif ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
			$title = genesis_get_cpt_option( 'headline' );
			if ( empty( $title ) && $a11ycheck ) {
				$title = post_type_archive_title( '', false );
			}
		}
		return apply_filters( 'display_featured_image_genesis_title_text', $title );

	}

	/**
	 * Get the ID of each image dynamically.
	 *
	 * @since 1.2.0
	 *
	 * @author Philip Newcomer
	 * @link   http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	public static function get_image_id( $attachment_url = '' ) {

		$attachment_id = false;

		// as of 2.2.0, if a (new) image id is passed to the function, or if it's empty, return it as is.
		if ( is_numeric( $attachment_url ) || '' === $attachment_url ) {
			return $attachment_url;
		}
		// if we're running 4.0 or later, we can do this all using a new core function.
		if ( function_exists( 'attachment_url_to_postid' ) ) {
			$url_stripped = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			return attachment_url_to_postid( $url_stripped );
		}

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();
		$base_url         = wp_make_link_relative( $upload_dir_paths['baseurl'] );
		$attachment_url   = wp_make_link_relative( $attachment_url );

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $base_url ) ) {

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $base_url . '/', '', $attachment_url );

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$url_stripped   = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id  = self::fetch_image_id_query( $url_stripped, $attachment_url );

		}

		return $attachment_id;
	}

	/**
	 * Fetch image ID from database
	 * @param  var $url_stripped   image url without WP resize string (eg 150x150)
	 * @param  var $attachment_url image url
	 * @return int (image id)                 image ID, or false
	 *
	 * @since 2.2.0
	 *
	 * @author hellofromtonya
	 */
	protected static function fetch_image_id_query( $url_stripped, $attachment_url ) {

		global $wpdb;

		$query_sql = $wpdb->prepare(
			"
				SELECT wposts.ID
				FROM {$wpdb->posts} wposts, {$wpdb->postmeta} wpostmeta
				WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value IN ( %s, %s ) AND wposts.post_type = 'attachment'
			",
			$url_stripped, $attachment_url
		);

		$result = $wpdb->get_col( $query_sql );

		return empty( $result ) || ! is_numeric( $result[0] ) ? false : intval( $result[0] );
	}

	/**
	 * Set up filter to check for post type rules. Variable, based on $value passed in.
	 * @param $value string for filter name
	 * @param array $post_types affected post types (empty array by default)
	 * @return bool
	 *
	 * @since 2.5.0
	 */
	public static function is_in_array( $value, $post_types = array() ) {
		$post_types = apply_filters( "display_featured_image_genesis_$value", $post_types );
		return in_array( get_post_type(), $post_types, true );
	}

	/**
	 * add a filter to change the minimum width required for backstretch image
	 * @return integer sets the minimum width for backstretch effect
	 *
	 * @since 2.2.0
	 */
	public function minimum_backstretch_width() {
		$large = apply_filters( 'display_featured_image_genesis_set_minimum_backstretch_width', get_option( 'large_size_w' ) );
		if ( ! is_numeric( $large ) ) {
			$large = get_option( 'large_size_w' );
		}
		return absint( intval( $large ) );
	}

	/**
	 * Select which image size to use. Can be filtered to use a custom size.
	 * @return mixed|string|void
	 * @since 2.5.0
	 */
	protected static function image_size() {
		$image_size = 'displayfeaturedimage_backstretch';
		/**
		 * Creates display_featured_image_genesis_use_large_image filter to check
		 * whether get_post_type array should use large image instead of backstretch.
		 * @uses is_in_array()
		 */
		if ( self::is_in_array( 'use_large_image' ) ) {
			$image_size = 'large';
		}
		$image_size = apply_filters( 'displayfeaturedimagegenesis_image_size', $image_size );
		return $image_size;
	}
}
