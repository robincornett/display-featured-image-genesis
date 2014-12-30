<?php

/**
 * settings for taxonomy pages
 *
 * @package DisplayFeaturedImageGenesis
 * @since x.y.z
 */
class Display_Featured_Image_Genesis_Taxonomies {

	function set_taxonomy_meta() {
		$args       = array( 'public' => true );
		$output     = 'objects';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( $taxonomy->name . '_add_form_fields', array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( $taxonomy->name . '_edit_form_fields', array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( 'edited_' . $taxonomy->name, array( $this, 'save_taxonomy_custom_meta' ), 10, 2 );
			add_action( 'create_' . $taxonomy->name, array( $this, 'save_taxonomy_custom_meta' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}


	function add_taxonomy_meta_fields() {

		//* this will add the custom meta field to the add new term page
		echo '<div class="form-field">';
			echo '<label for="term_meta[dfig_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label>';
			echo '<input type="url" id="default_image_url" name="term_meta[dfig_image]" value="" />';
			echo '<input id="upload_default_image" type="button" class="upload_term_meta_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
			echo '<p>' . __( 'Set Featured Image for Taxonomy','display-featured-image-genesis' ) . '</p>';
		echo '</div>';

	}

	//* Edit term page
	function edit_taxonomy_meta_fields( $term ) {

		//* put the term ID into a variable
		$t_id = $term->term_id;

		//* retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "taxonomy_$t_id" );
		//* this will add the custom meta field to the add new term page
		echo '<tr class="form-field">';
			echo '<th scope="row" valign="top"><label for="term_meta[dfig_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label></th>';
				echo '<td>';
					if ( ! empty( $term_meta['dfig_image'] ) ) {
						$id      = Display_Featured_Image_Genesis_Common::get_image_id( $term_meta['dfig_image'] );
						$preview = wp_get_attachment_image_src( $id, 'medium' );
						echo '<div id="upload_logo_preview">';
						echo '<img src="' . esc_url( $preview[0] ) . '" width="300" />';
						echo '</div>';
					}
					echo '<input type="url" id="default_image_url" name="term_meta[dfig_image]" value="' . esc_url( $term_meta['dfig_image'] ) . '" />';
					echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
					echo '<p>' . __( 'Set Featured Image for Taxonomy', 'display-featured-image-genesis' ) . '</p>';
				echo '</td>';
		echo '</tr>';
	}

	//* Save extra taxonomy fields callback function.
	function save_taxonomy_custom_meta( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$t_id      = $term_id;
			$term_meta = get_option( "taxonomy_$t_id" );
			$cat_keys  = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			//* Save the option array.
			update_option( "taxonomy_$t_id", $term_meta );
		}
	}

}