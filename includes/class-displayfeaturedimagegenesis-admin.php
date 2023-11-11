<?php

/**
 * Dependent class to establish/display columns for featured images
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2015-2020 Robin Cornett Creative, LLC
 * @since 2.0.0
 */

class Display_Featured_Image_Genesis_Admin {

	public function set_up_columns() {
		$this->set_up_taxonomy_columns();
		$this->set_up_post_type_columns();
		$this->set_up_author_columns();
		add_action( 'admin_enqueue_scripts', array( $this, 'featured_image_column_width' ) );
		add_action( 'pre_get_posts', array( $this, 'orderby' ) );
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
		$post_types = $this->get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
				continue;
			}
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_post_columns' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'make_sortable' ) );
		}
	}

	/**
	 * Get a list of post types which can show a featured image column.
	 *
	 * @return array
	 * @since 3.2.1
	 */
	private function get_post_types() {
		$args               = array(
			'_builtin' => false,
			'show_ui'  => true,
		);
		$post_types         = get_post_types( $args, 'names' );
		$post_types['post'] = 'post';
		$post_types['page'] = 'page';

		return apply_filters( 'displayfeaturedimagegenesis_admin_column_post_types', $post_types );
	}

	/**
	 * Set up featured image column for users
	 *
	 * @since 2.3.0
	 */
	protected function set_up_author_columns() {
		add_filter( 'manage_users_columns', array( $this, 'add_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'manage_user_column' ), 10, 3 );
	}

	/**
	 * add featured image column
	 * @param $columns array set up new column to show featured image for taxonomies/posts/etc.
	 *
	 * @since 2.0.0
	 */
	public function add_column( $columns ) {

		$new_columns = $columns;
		array_splice( $new_columns, 1 );

		$new_columns['featured_image'] = __( 'Image', 'display-featured-image-genesis' );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * manage new taxonomy column
	 * @param  $value string  blank (because WP)
	 * @param  $column string  column id is featured_image
	 * @param  $term_id  int $term_id term_id for taxonomy
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

		$taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_SPECIAL_CHARS );
		$taxonomy = ! is_null( $taxonomy ) ? $taxonomy : get_current_screen()->taxonomy;
		$args     = array(
			'image_id' => $image_id,
			'context'  => 'term',
			'alt'      => get_term( $term_id, $taxonomy )->name,
		);

		echo wp_kses_post( $this->admin_featured_image( $args ) );
	}

	/**
	 * Manage new post_type column: display the featured image, if it exists.
	 *
	 * @param  $column string  column id is featured_image
	 * @param  $post_id int id of each post
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
	 * Add an inline stylesheet to set a width for the featured image column.
	 */
	public function featured_image_column_width() {
		$screen = get_current_screen();
		if ( in_array( $screen->base, array( 'edit', 'edit-tags', 'users' ), true ) ) { ?>
			<style type="text/css">
				.column-featured_image { width: 80px; }
				.edit-tags-php .column-featured_image { width: 60px; }
				.column-featured_image img { margin: 0 auto; height: auto; width: auto; max-width: 60px; max-height: 80px; }
				@media screen and (max-width: 782px) { .column-featured_image, .wp-list-table .is-expanded td.column-featured_image:not(.hidden) { display: table-cell !important; width: 52px; } .column-featured_image.hidden { display: none !important; } .column-featured_image img { margin: 0; max-width: 42px; } }
			</style>
			<?php
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
			return $value;
		}
		$image_id = get_the_author_meta( 'displayfeaturedimagegenesis', (int) $user_id );
		if ( ! $image_id ) {
			return '';
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
			return '';
		}
		return sprintf( '<img src="%1$s" alt="%2$s" />', $preview[0], $args['alt'] );
	}

	/**
	 * Make the featured image column sortable.
	 * @param $columns
	 * @return mixed
	 * @since 2.5.0
	 */
	public function make_sortable( $columns ) {
		$columns['featured_image'] = 'featured_image';
		return $columns;
	}

	/**
	 * Set a custom query to handle sorting by featured image
	 * @param $query WP_Query
	 * @since 2.5.0
	 */
	public function orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'featured_image' === $orderby ) {
			$query->set(
				'meta_query',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				)
			);
			$post_type       = $query->get( 'post_type' );
			$secondary_order = is_post_type_hierarchical( $post_type ) ? 'title' : 'date';
			$query->set( 'orderby', "meta_value_num $secondary_order" );
		}
	}
}
