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
 */
function display_featured_image_genesis_get_term_image() {
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
