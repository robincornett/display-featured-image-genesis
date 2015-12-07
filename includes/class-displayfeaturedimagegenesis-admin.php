<?php

/**
 * Dependent class to establish/display columns for featured images
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2015 Robin Cornett Creative, LLC
 * @since 2.0.0
 */

class Display_Featured_Image_Genesis_Admin {

	protected $common;

	public function set_up_columns() {
		$this->common = new Display_Featured_Image_Genesis_Common();
		$this->set_up_taxonomy_columns();
		$this->set_up_post_type_columns();
		$this->set_up_author_columns();

		add_action( 'admin_enqueue_scripts', array( $this, 'featured_image_column_width' ) );
	}

	/**
	 * set up new column for all public taxonomies
	 *
	 * @since  2.0.0
	 */
	protected function set_up_taxonomy_columns() {
		$args       = array(
			'public' => true,
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
	 * @since  2.0.0
	 */
	protected function set_up_post_type_columns() {
		$args       = array(
			'public'   => true,
			'_builtin' => false,
		);
		$output     = 'names';
		$post_types = get_post_types( $args, $output );
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
				continue;
			}
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_post_columns' ), 10, 2 );
		}
	}

	/**
	 * Set up featured image column for users
	 *
	 * @since 2.3.0
	 */
	protected function set_up_author_columns() {
		add_filter( 'manage_users_columns', array( $this, 'add_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'manage_user_column' ), 10, 3 );
	}

	/**
	 * add featured image column
	 * @param column $columns set up new column to show featured image for taxonomies/posts/etc.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
	 */
	public function manage_taxonomy_column( $value, $column, $term_id ) {

		if ( 'featured_image' !== $column ) {
			return;
		}

		$image_id = displayfeaturedimagegenesis_get_term_image( $term_id );
		if ( ! $image_id ) {
			return;
		}

		$taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING );
		$taxonomy = ! is_null( $taxonomy ) ? $taxonomy : get_current_screen()->taxonomy;
		$image_id = displayfeaturedimagegenesis_check_image_id( $image_id );

		$args = array(
			'image_id' => $image_id,
			'context'  => 'term',
			'alt'      => get_term( $term_id, $taxonomy )->name,
		);

		echo wp_kses_post( $this->admin_featured_image( $args ) );

	}

	/**
	 * manage new post_type column
	 * @param  column id $column  column id is featured_image
	 * @param  post id $post_id id of each post
	 * @return featured image          display featured image, if it exists, for each post
	 *
	 * @since 2.0.0
	 */
	public function custom_post_columns( $column, $post_id ) {

		if ( 'featured_image' !== $column ) {
			return;
		}
		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			return;
		}

		$args = array(
			'image_id' => $image_id,
			'context'  => 'post',
			'alt'      => the_title_attribute( 'echo=0' ),
		);

		echo wp_kses_post( $this->admin_featured_image( $args ) );

	}

	/**
	 * sets a width for the featured image column
	 * @return stylesheet inline stylesheet to set featured image column width
	 */
	public function featured_image_column_width() {
		$screen = get_current_screen();
		if ( in_array( $screen->base, array( 'edit', 'edit-tags', 'users' ) ) ) { ?>
			<style type="text/css">
				.column-featured_image { width: 105px; }
				.column-featured_image img { margin: 0 auto; display: block; height: auto; width: auto; max-width: 60px; max-height: 80px; }
				@media screen and (max-width: 782px) { .column-featured_image img { margin: 0; } }
			</style> <?php
		}
	}

	/**
	 * User column output
	 * @param  string $value       image to be output to column
	 * @param  string $column_name column name (featured_image)
	 * @param  int $user_id     user id
	 * @return string              image
	 *
	 * @since 2.3.0
	 */
	public function manage_user_column( $value, $column_name, $user_id ) {
		if ( 'featured_image' !== $column_name ) {
			return;
		}
		$image_id = get_the_author_meta( 'displayfeaturedimagegenesis', (int) $user_id );
		if ( ! $image_id ) {
			return;
		}
		$args = array(
			'image_id' => $image_id,
			'context'  => 'author',
			'alt'      => get_the_author_meta( 'user_nicename', (int) $user_id ),
		);

		return $this->admin_featured_image( $args );

	}

	/**
	 * Generic function to return featured image
	 * @param  array $args array of values to pass to function ( image_id, context, alt_tag )
	 * @return string       image html
	 *
	 * @since 2.3.0
	 */
	protected function admin_featured_image( $args ) {
		$image_id = $args['image_id'];
		$preview  = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$preview  = apply_filters( "display_featured_image_genesis_admin_{$args['context']}_thumbnail", $preview, $image_id );
		if ( ! $preview ) {
			return;
		}
		return sprintf( '<img src="%1$s" alt="%2$s" />', $preview[0], $args['alt'] );
	}

}
