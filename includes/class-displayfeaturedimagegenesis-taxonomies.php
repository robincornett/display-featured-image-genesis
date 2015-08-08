<?php

/**
 * settings for taxonomy pages
 *
 * @package DisplayFeaturedImageGenesis
 *
 * @since 2.0.0
 */
class Display_Featured_Image_Genesis_Taxonomies {

	protected $settings;

	/**
	 * set up all actions for adding featured images to taxonomies
	 * @since  2.0.0
	 */
	public function set_taxonomy_meta() {

		$this->settings = new Display_Featured_Image_Genesis_Settings();

		$args       = array(
			'public' => true,
		);
		$output     = 'names';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( "edited_{$taxonomy}", array( $this->settings, 'save_taxonomy_custom_meta' ), 10, 2 );
			add_action( "create_{$taxonomy}", array( $this->settings, 'save_taxonomy_custom_meta' ), 10, 2 );
			add_action( 'load-edit-tags.php', array( $this, 'help' ) );
		}

		add_action( 'split_shared_term', array( $this, 'split_shared_term' ) );

	}

	/**
	 * add featured image uploader to new term add
	 */
	public function add_taxonomy_meta_fields() {

		echo '<div class="form-field term-image-wrap">';
			printf( '<label for="displayfeaturedimagegenesis[term_image]">%s</label>',
				esc_attr__( 'Featured Image', 'display-featured-image-genesis' )
			);
			echo '<input type="hidden" class="upload_image_id" id="term_image_id" name="displayfeaturedimagegenesis[term_image]" />';
			printf( '<input id="upload_default_image" type="button" class="upload_default_image button-secondary" value="%s" />',
				esc_attr__( 'Select Image', 'display-featured-image-genesis' )
			);
			printf( '<input type="button" class="delete_image button-secondary" value="%s" />',
				esc_attr__( 'Delete Image', 'display-featured-image-genesis' )
			);
			echo '<p class="description">';
			printf( esc_attr__( 'Set Featured Image for new term.', 'display-featured-image-genesis' ) );
			echo '</p>';
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
		$id             = '';

		echo '<tr class="form-field term-image-wrap">';
			printf( '<th scope="row" valign="top"><label for="displayfeaturedimagegenesis[term_image]">%s</label></th>',
				esc_attr__( 'Featured Image', 'display-featured-image-genesis' )
			);
			echo '<td>';
				$id   = $displaysetting['term_image'];
				$name = 'displayfeaturedimagegenesis[term_image]';
				if ( ! empty( $id ) ) {
					echo wp_kses_post( $this->settings->render_image_preview( $id ) );
				}
				$this->settings->render_buttons( $id, $name );
				echo '<p class="description">';
				printf(
					esc_attr__( 'Set Featured Image for %1$s.', 'display-featured-image-genesis' ),
					esc_attr( $term->name )
				);
				echo '</p>';
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

		$term_help  = '<h3>' . __( 'Set a Featured Image', 'display-featured-image-genesis' ) . '</h3>';
		$term_help .= '<p>' . __( 'You may set a featured image for your terms. This image will be used on the term archive page, and as a fallback image on a single post page if it does not have a featured image of its own.', 'display-featured-image-genesis' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => 'displayfeaturedimage_term-help',
			'title'   => __( 'Featured Image', 'display-featured-image-genesis' ),
			'content' => $term_help,
		) );

	}

	/**
	 * Create new term meta record for split terms.
	 *
	 * When WordPress splits terms, ensure that the term meta gets preserved for the newly created term.
	 *
	 * @since 2.3.0
	 *
	 * @param integer @old_term_id The ID of the term being split.
	 * @param integer @new_term_id The ID of the newly created term.
	 *
	 */
	function split_shared_term( $old_term_id, $new_term_id ) {

		$old_setting = get_option( "displayfeaturedimagegenesis_$old_term_id" );
		$new_setting = get_option( "displayfeaturedimagegenesis_$new_term_id" );

		if ( ! isset( $old_setting ) ) {
			return;
		}

		$new_setting = $old_setting;

		update_option( "displayfeaturedimagegenesis_$new_term_id", $new_setting );

	}


}
