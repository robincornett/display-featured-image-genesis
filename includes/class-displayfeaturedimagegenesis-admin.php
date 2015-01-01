<?php

/**
 * admin columns
 *
 * @package DisplayFeaturedImageGenesis
 * @since x.y.z
 */
class Display_Featured_Image_Genesis_Admin {

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
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_post_columns' ), 10, 2 );
		}
	}

	public function add_column( $columns ) {

		$new_columns = $columns;
		array_splice( $new_columns, 1 );

		$new_columns['featured_image'] = __( 'Featured Image', 'display-featured-image-genesis' );

		return array_merge( $new_columns, $columns );

	}

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

	public function custom_post_columns( $column, $post_id ) {

		if ( 'featured_image' === $column ) {
			$image = get_the_post_thumbnail( $post_id, array( 60,60 ) );
			if ( $image ) {
				echo $image;
			}
		}

	}


}