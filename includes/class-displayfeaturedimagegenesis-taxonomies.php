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
		}
	}


	function add_taxonomy_meta_fields() {

		//* this will add the custom meta field to the add new term page
		echo '<div class="form-field">';
			echo '<label for="term_meta[dfig_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label>';
			echo '<input type="url" id="default_image_url" name="term_meta[dfig_image]" style="width:200px;" />';
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
					echo '<input type="url" id="default_image_url" name="term_meta[dfig_image]" value="' . esc_url( $term_meta['dfig_image'] ) . '" style="width:200px;" />';
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
					if ( ['term_meta']['dfig_image'] === $term_meta[$key] ) {
						$term_meta[$key] = $this->validate_image( $_POST['term_meta'][$key] );
					}
				}
			}
			//* Save the option array.
			update_option( "taxonomy_$t_id", $term_meta );
		}
	}

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	protected function validate_image( $new_value ) {

		$new_value = esc_url( $new_value );
		$valid     = $this->is_valid_img_ext( $new_value );
		$large     = get_option( 'large_size_w' );
		$id        = Display_Featured_Image_Genesis_Common::get_image_id( $new_value );
		$metadata  = wp_get_attachment_metadata( $id );
		$width     = $metadata['width'];
		$t_id      = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );

		// ok for field to be empty
		if ( $new_value ) {

			if ( ! $valid || $width <= $large ) {
				$new_value = $term_meta['dfig_image'];
			}

		}

		return $new_value;
	}

	/**
	 * returns file extension
	 * @since  1.2.2
	 */
	protected function get_file_ext( $file ) {
		$parsed = @parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * check if file type is image
	 * @return file       check file extension against list
	 * @since  1.2.2
	 */
	protected function is_valid_img_ext( $file ) {
		$file_ext = $this->get_file_ext( $file );

		$this->valid = empty( $this->valid )
			? (array) apply_filters( 'displayfeaturedimage_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif' ) )
			: $this->valid;

		return ( $file_ext && in_array( $file_ext, $this->valid ) );
	}

}