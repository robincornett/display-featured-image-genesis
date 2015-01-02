<?php

/**
 * Dependent class to establish/display columns for featured images
 *
 * @package DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 * @since x.y.z
 */

class Display_Featured_Image_Genesis_Admin {

	/**
	 * set up new column for all public taxonomies
	 *
	 * @since  x.y.z
	 */
	public function set_up_taxonomy_columns() {
		$args       = array(
			'public' => true
		);
		$output     = 'names';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$taxonomy}_custom_column", array( $this, 'manage_taxonomy_column' ), 10, 3 );
		}
	}

	/**
	 * set up new column for all public post types
	 *
	 * @since  x.y.z
	 */
	public function set_up_post_type_columns() {
		$args       = array(
			'public'   => true,
			'_builtin' => false,
		);
		$output     = 'names';
		$post_types = get_post_types( $args, $output );
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'thumbnail' ) ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_column' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_post_columns' ), 10, 2 );
			}
		}
	}

	/**
	 * add featured image column
	 * @param column $columns set up new column to show featured image for taxonomies/posts/etc.
	 *
	 * @since x.y.z
	 */
	public function add_column( $columns ) {

		$new_columns = $columns;
		array_splice( $new_columns, 1 );

		$new_columns['featured_image'] = __( 'Featured Image', 'display-featured-image-genesis' );

		return array_merge( $new_columns, $columns );

	}

	/**
	 * manage new taxonomy column
	 * @param  blank $value   blank (because WP)
	 * @param  column id $column  column id is featured_image
	 * @param  term id $term_id term_id for taxonomy
	 * @return featured image          display featured image, if it exists, for each term in a public taxonomy
	 *
	 * @since x.y.z
	 */
	public function manage_taxonomy_column( $value, $column, $term_id ) {

		if ( 'featured_image' === $column ) {
			$term_meta = get_option( "taxonomy_$term_id" );
			if ( ! empty( $term_meta['dfig_image'] ) ) {
				$id      = Display_Featured_Image_Genesis_Common::get_image_id( $term_meta['dfig_image'] );
				$preview = wp_get_attachment_image_src( $id, 'thumbnail' );
				echo '<img src="' . $preview[0] . '" width="60" />';
			}
		}

	}

	/**
	 * manage new post_type column
	 * @param  column id $column  column id is featured_image
	 * @param  post id $post_id id of each post
	 * @return featured image          display featured image, if it exists, for each post
	 *
	 * @since x.y.z
	 */
	public function custom_post_columns( $column, $post_id ) {

		if ( 'featured_image' === $column ) {
			$image = get_the_post_thumbnail( $post_id, array( 60,60 ) );
			if ( $image ) {
				echo $image;
			}
		}

	}

}
