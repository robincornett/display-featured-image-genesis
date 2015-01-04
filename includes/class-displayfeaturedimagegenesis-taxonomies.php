<?php

/**
 * settings for taxonomy pages
 *
 * @package DisplayFeaturedImageGenesis
 * @since x.y.z
 */
class Display_Featured_Image_Genesis_Taxonomies {

	/**
	 * add featured image uploader to new taxonomy add
	 */
	public function add_taxonomy_meta_fields() {

		echo '<div class="form-field">';
			echo '<label for="term_meta[dfig_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label>';
			echo '<input type="url" class="upload_image_url" id="default_image_url" name="term_meta[dfig_image]" style="width:200px;" />';
			echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
			echo '<p>' . __( 'Set Featured Image for Taxonomy','display-featured-image-genesis' ) . '</p>';
		echo '</div>';

	}

	/**
	 * edit term page
	 * @param  term $term featured image input/display for individual term page
	 * @return preview/uploader       upload/preview featured image for term
	 */
	public function edit_taxonomy_meta_fields( $term ) {

		$t_id      = $term->term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$large     = get_option( 'large_size_w' );

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
					echo '<input type="url" class="upload_image_url" id="default_image_url" name="term_meta[dfig_image]" value="' . esc_url( $term_meta['dfig_image'] ) . '" style="width:200px;" />';
					echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
					echo '<p class="description">' . sprintf(
						__( 'Set Featured Image for %1$s. Must be at least %2$s pixels wide.', 'display-featured-image-genesis' ),
						esc_html( $term->name ),
						absint( $large + 1 )
					) . '</p>';
				echo '</td>';
		echo '</tr>';
	}

}
