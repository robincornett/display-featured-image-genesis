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
	$taxonomies = get_taxonomies();
	$args       = array( 'orderby' => 'count', 'order' => 'DESC' );
	$terms      = wp_get_object_terms( get_the_ID(), $taxonomies, $args );
	$image_id   = '';

	foreach ( $terms as $term ) {
		$t_id      = $term->term_id;
		$term_meta = get_option( "displayfeaturedimagegenesis_$t_id" );
		if ( ! empty( $term_meta['term_image'] ) ) {
			$image_id = Display_Featured_Image_Genesis_Common::get_image_id( $term_meta['term_image'] );
			break;
		}
	}

	return $image_id;

}

/**
 * Helper function to get the term image URL.
 * @param  string $size image size
 * @return url       URL associated with the term image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_term_image_url( $size='displayfeaturedimage_backstretch' ) {

	$image_id  = display_featured_image_genesis_get_term_image_id();

	$image_url = wp_get_attachment_image_src( $image_id, $size );

	return $image_url[0];

}

/**
 * Helper function to get the default image ID.
 * @return ID ID associated with the fallback/default image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_default_image_id() {

	$item     = Display_Featured_Image_Genesis_Common::get_image_variables();
	$image_id = $item->fallback_id;

	return $image_id;

}

/**
 * Helper function to get the default image URL.
 * @param  image size $size image size to retrieve
 * @return URL       URL associated with the term image
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_get_default_image_url( $size='displayfeaturedimage_backstretch' ) {

	$image_id  = display_featured_image_genesis_get_default_image_id();

	$image_url = wp_get_attachment_image_src( $image_id, $size );

	return $image_url[0];

}

/**
 * Add term/default image to blog/archive pages.
 * @return image If a post doesn't have its own thumbnail, you can use this function to add one to archive pages.
 *
 * @since  2.1.0
 */
function display_featured_image_genesis_add_archive_thumbnails() {

	if ( ! is_home() && ! is_archive() && ! is_page_template( 'page_blog.php' ) ) {
		return;
	}

	if ( has_post_thumbnail() ) {
		return;
	}

	$size      = genesis_get_option( 'image_size' );
	$image_url = display_featured_image_genesis_get_term_image_url( $size );
	if ( empty( $image_url ) ) {
		$image_url = display_featured_image_genesis_get_default_image_url( $size );
	}

	if ( empty( $image_url ) ) {
		return;
	}

	$permalink = get_the_permalink();
	$alignment = genesis_get_option( 'image_alignment' );
	$image     = '<a href="' . esc_url( $permalink ) . '"><img src="' . esc_url( $image_url ) . '" class="' . $alignment . '" /></a>';
	echo $image;

}
