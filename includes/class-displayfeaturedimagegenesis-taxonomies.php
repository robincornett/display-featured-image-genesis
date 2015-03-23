<?php

/**
 * settings for taxonomy pages
 *
 * @package DisplayFeaturedImageGenesis
 *
 * @since 2.0.0
 */
class Display_Featured_Image_Genesis_Taxonomies {

	/**
	 * add featured image uploader to new term add
	 */
	public function add_taxonomy_meta_fields() {

		echo '<div class="form-field">';
			echo '<label for="displayfeaturedimagegenesis[term_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label>';
			echo '<input type="text" class="upload_image_url" id="default_image_url" name="displayfeaturedimagegenesis[term_image]" style="width:200px;" />';
			echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
			echo '<p>' . __( 'Set Featured Image for Taxonomy','display-featured-image-genesis' ) . '</p>';
		echo '</div>';

	}

	/**
	 * edit term page
	 * @param  term $term featured image input/display for individual term page
	 *
	 * @return preview/uploader       upload/preview featured image for term
	 *
	 * @since  2.0.0
	 */
	public function edit_taxonomy_meta_fields( $term ) {

		$t_id           = $term->term_id;
		$displaysetting = get_option( "displayfeaturedimagegenesis_$t_id" );
		$medium         = get_option( 'medium_size_w' );

		echo '<tr class="form-field">';
			echo '<th scope="row" valign="top"><label for="displayfeaturedimagegenesis[term_image]">' . __( 'Featured Image', 'display-featured-image-genesis' ) . '</label></th>';
				echo '<td>';
					if ( ! empty( $displaysetting['term_image'] ) ) {
						$id = $displaysetting['term_image'];
						if ( ! is_integer( $displaysetting['term_image'] ) ) {
							$id = Display_Featured_Image_Genesis_Common::get_image_id( $displaysetting['term_image'] );
						}
						$preview = wp_get_attachment_image_src( absint( $id ), 'medium' );
						echo '<div id="upload_logo_preview">';
						echo '<img src="' . esc_url( $preview[0] ) . '" width="300" />';
						echo '</div>';
					}
					echo '<input type="text" class="upload_image_url" id="default_image_url" name="displayfeaturedimagegenesis[term_image]" value="' . esc_url( $displaysetting['term_image'] ) . '" style="width:200px;" />';
					echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
					echo '<p class="description">' . sprintf(
						__( 'Set Featured Image for %1$s.', 'display-featured-image-genesis' ),
						esc_html( $term->name )
					) . '</p>';
				echo '</td>';
		echo '</tr>';
	}

	/**
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 2.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		$term_help =
			'<h3>' . __( 'Set a Featured Image', 'display-featured-image-genesis' ) . '</h3>' .
			'<p>' . __( 'You may set a featured image for your terms. This image will be used on the term archive page, and as a fallback image on a single post page if it does not have a featured image of its own.', 'display-featured-image-genesis' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_term-help',
			'title'   => __( 'Featured Image', 'display-featured-image-genesis' ),
			'content' => $term_help,
		) );

	}

}
