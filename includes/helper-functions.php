<?php
/**
 * Helper functions for Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * Helper function to retrieve the term image ID, whether as term_meta or wp_options
 * @param  int $term_id  term ID
 * @param  string $image_id image ID
 * @return int           image ID
 * @since 2.4.0
 */
function displayfeaturedimagegenesis_get_term_image( $term_id, $image_id = '' ) {
	if ( function_exists( 'get_term_meta' ) ) {
		$image_id = get_term_meta( $term_id, 'displayfeaturedimagegenesis', true );
	}
	if ( ! $image_id ) {
		$term_meta = get_option( "displayfeaturedimagegenesis_$term_id" );
		if ( $term_meta ) {
			$image_id = displayfeaturedimagegenesis_check_image_id( $term_meta['term_image'] );
		}
	}
	return $image_id;
}

/**
 * gets the term image ID
 * @return image_id reusable function to get a post's term image, if it exists
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_term_image_id( $image_id = '' ) {

	$taxonomies = get_taxonomies();
	$args       = array( 'orderby' => 'count', 'order' => 'DESC' );
	$terms      = wp_get_object_terms( get_the_ID(), $taxonomies, $args );

	foreach ( $terms as $term ) {
		$term_id  = $term->term_id;
		$image_id = displayfeaturedimagegenesis_get_term_image( $term_id );
		if ( $image_id ) {
			break;
		}
	}

	return (int) $image_id;

}

/**
 * Helper function to get the term image URL.
 * @param  string $size image size
 * @return url       URL associated with the term image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_term_image_url( $size = 'displayfeaturedimage_backstretch' ) {

	$image_id  = display_featured_image_genesis_get_term_image_id();
	$image_url = wp_get_attachment_image_src( $image_id, $size );

	return esc_url( $image_url[0] );

}

/**
 * Helper function to get the default image ID.
 * @return ID ID associated with the fallback/default image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_default_image_id( $image_id = '' ) {

	$displaysetting = get_option( 'displayfeaturedimagegenesis' );
	$fallback       = $displaysetting['default'];
	$image_id       = displayfeaturedimagegenesis_check_image_id( $fallback );

	return (int) $image_id;

}

/**
 * Helper function to get the default image URL.
 * @param  image size $size image size to retrieve
 * @return URL       URL associated with the term image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_default_image_url( $size = 'displayfeaturedimage_backstretch' ) {

	$image_id  = display_featured_image_genesis_get_default_image_id();
	$image_url = wp_get_attachment_image_src( $image_id, $size );

	return esc_url( $image_url[0] );

}

/**
 * Get custom post type featured image ID.
 * @return ID Gets the ID of the image assigned as the custom post type featured image.
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_cpt_image_id( $image_id = '' ) {

	$post_type      = '';
	$displaysetting = get_option( 'displayfeaturedimagegenesis' );
	$object         = get_queried_object();
	if ( ! $object || is_admin() ) {
		return;
	}
	if ( $object->name ) { // results in post type on cpt archive
		$post_type = $object->name;
	} elseif ( $object->taxonomy ) { // on a tax/term/category
		$tax_object = get_taxonomy( $object->taxonomy );
		$post_type  = $tax_object->object_type[0];
	} elseif ( $object->post_type ) { // on singular
		$post_type = $object->post_type;
	}
	if ( ! empty( $displaysetting['post_type'][ $post_type ] ) ) {
		$image_id = displayfeaturedimagegenesis_check_image_id( $displaysetting['post_type'][ $post_type ] );
	}

	return (int) $image_id;
}

/**
 * Get the custom post type featured image URL.
 * @param  string $size image size
 * @return URL       returns the image URL for the custom post type featured image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_cpt_image_url( $size = 'displayfeaturedimage_backstretch' ) {

	$image_id  = display_featured_image_genesis_get_cpt_image_id();
	$image_url = wp_get_attachment_image_src( $image_id, $size );

	return esc_url( $image_url[0] );

}

/**
 * Add term/default image to blog/archive pages. Use:
 * add_action( 'genesis_entry_content', 'display_featured_image_genesis_add_archive_thumbnails', 5 );
 * @return image If a post doesn't have its own thumbnail, you can use this function to add one to archive pages.
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_add_archive_thumbnails() {

	$show_thumbs = genesis_get_option( 'content_archive_thumbnail' );

	if ( is_singular() || is_admin() || is_404() || ! $show_thumbs ) {
		return;
	}

	$args = array(
		'post_mime_type' => 'image',
		'post_parent'    => get_the_ID(),
		'post_type'      => 'attachment',
	);
	$attached_images = get_children( $args );

	if ( has_post_thumbnail() || $attached_images ) {
		return;
	}

	$image_id = display_featured_image_genesis_get_term_image_id();
	if ( empty( $image_id ) ) {
		$image_id = display_featured_image_genesis_get_cpt_image_id();
		if ( empty( $image_id ) ) {
			$image_id = display_featured_image_genesis_get_default_image_id();
		}
	}

	if ( empty( $image_id ) ) {
		return;
	}

	$image = genesis_get_image( array(
		/**
		 * Filter the fallback image ID
		 *
		 * @since 2.2.0
		 */
		'fallback' => apply_filters( 'display_featured_image_genesis_fallback_archive_thumbnail', $image_id ),
		'size'     => genesis_get_option( 'image_size' ),
		'attr'     => genesis_parse_attr( 'entry-image', array( 'alt' => get_the_title() ) ),
		'context'  => 'archive',
	) );

	$permalink = get_permalink();
	printf( '<a href="%1$s" aria-hidden="true">%2$s</a>',
		esc_url( $permalink ),
		wp_kses_post( $image )
	);

}

/**
 * function to check image_id value, convert from URL if necessary
 * @param  string $image_id int or URL string
 * @return int           image ID
 *
 * @since 2.3.0
 */
function displayfeaturedimagegenesis_check_image_id( $image_id = '' ) {
	$image_id = is_numeric( $image_id ) ? $image_id : Display_Featured_Image_Genesis_Common::get_image_id( $image_id );
	return $image_id;
}

/**
 * Helper function to get the plugin settings.
 * @return mixed|void
 *
 * @since 2.4.2
 */
function displayfeaturedimagegenesis_get_setting() {
	return apply_filters( 'displayfeaturedimagegenesis_get_setting', false );
}

/**
 * Get the term meta (generally headline or intro text). Backwards compatible,
 * but uses new term meta (as of Genesis 2.2.7)
 * @param $term object the term
 * @param $key string meta key to retrieve
 * @param string $value string output of the term meta
 *
 * @return mixed|string
 *
 * @ since 2.5.0
 */
function displayfeaturedimagegenesis_get_term_meta( $term, $key, $value = '' ) {
	if ( ! $term ) {
		return $value;
	}
	if ( function_exists( 'get_term_meta' ) ) {
		$value = get_term_meta( $term->term_id, $key, true );
	}
	if ( ! $value && isset( $term->meta[ $key ] ) ) {
		$value = $term->meta[ $key ];
	}
	return $value;
}
