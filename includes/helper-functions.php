<?php
/**
 * Helper functions for Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2015 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * gets the term image ID
 * @return image_id reusable function to get a post's term image, if it exists
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_term_image_id() {

	$image_id   = '';
	$taxonomies = get_taxonomies();
	$args       = array( 'orderby' => 'count', 'order' => 'DESC' );
	$terms      = wp_get_object_terms( get_the_ID(), $taxonomies, $args );

	foreach ( $terms as $term ) {
		$t_id      = $term->term_id;
		$term_meta = get_option( "displayfeaturedimagegenesis_$t_id" );
		if ( ! empty( $term_meta['term_image'] ) ) {
			$image_id = $term_meta['term_image'];
			if ( ! is_numeric( $term_meta['term_image'] ) ) {
				$image_id = Display_Featured_Image_Genesis_Common::get_image_id( $term_meta['term_image'] );
			}
			break;
		}
	}

	return absint( $image_id );

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
function display_featured_image_genesis_get_default_image_id() {

	$image_id       = '';
	$displaysetting = get_option( 'displayfeaturedimagegenesis' );
	$fallback       = $displaysetting['default'];
	$image_id       = $fallback;
	if ( ! is_numeric( $fallback ) ) {
		$image_id = self::get_image_id( $fallback ); // gets image id with attached metadata
	}

	return absint( $image_id );

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
function display_featured_image_genesis_get_cpt_image_id() {

	$image_id = $post_type = '';
	$displaysetting = get_option( 'displayfeaturedimagegenesis' );
	$object         = get_queried_object();
	if ( ! $object || is_admin() ) {
		return;
	}
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
	if ( ! empty( $displaysetting['post_type'][ $post_type ] ) ) {
		$image_id = $displaysetting['post_type'][ $post_type ];
		if ( ! is_numeric( $displaysetting['post_type'][ $post_type ] ) ) {
			$image_id = Display_Featured_Image_Genesis_Common::get_image_id( $displaysetting['post_type'][ $post_type ] );
		}
	}

	return absint( $image_id );
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
