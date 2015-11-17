<?php

/**
 * settings for taxonomy pages
 *
 * @package DisplayFeaturedImageGenesis
 *
 * @since 2.0.0
 */
class Display_Featured_Image_Genesis_Taxonomies extends Display_Featured_Image_Genesis_Helper {

	protected $settings;

	/**
	 * set up all actions for adding featured images to taxonomies
	 * @since  2.0.0
	 */
	public function set_taxonomy_meta() {

		register_meta( 'term', 'displayfeaturedimagegenesis', array( $this, 'validate_taxonomy_image' ) );

		$args       = array(
			'public' => true,
		);
		$output     = 'names';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( "create_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( 'edit_{$taxonomy}', array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( 'load-edit-tags.php', array( $this, 'help' ) );
		}

		add_action( 'split_shared_term', array( $this, 'split_shared_term' ) );

	}

	/**
	 * add featured image uploader to new term add
	 */
	public function add_taxonomy_meta_fields() {

		?>
		<div class="form-field term-image-wrap">
			<?php wp_nonce_field( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis' ); ?>
			<label for="displayfeaturedimagegenesis[term_image]"><?php esc_attr_e( 'Featured Image', 'display-featured-image-genesis' ); ?></label>
			<input type="hidden" class="upload_image_id" id="term_image_id" name="displayfeaturedimagegenesis[term_image]" />
			<input id="upload_default_image" type="button" class="upload_default_image button-secondary" value="<?php esc_attr_e( 'Select Image', 'display-featured-image-genesis' ); ?>" />
			<input type="button" class="delete_image button-secondary" value="<?php esc_attr_e( 'Delete Image', 'display-featured-image-genesis' ); ?>" />
			<p class="description"><?php esc_attr_e( 'Set Featured Image for new term.', 'display-featured-image-genesis' ); ?></p>
		</div>
	<?php

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

		$term_id  = $term->term_id;
		$image_id = displayfeaturedimagegenesis_term_image( $term_id );

		echo '<tr class="form-field term-image-wrap">';
			wp_nonce_field( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis' );
			printf( '<th scope="row" valign="top"><label for="displayfeaturedimagegenesis[term_image]">%s</label></th>',
				esc_attr__( 'Featured Image', 'display-featured-image-genesis' )
			);
			echo '<td>';
				$name = 'displayfeaturedimagegenesis[term_image]';
				if ( $image_id ) {
					echo wp_kses_post( $this->render_image_preview( $image_id ) );
				}
				$this->render_buttons( $image_id, $name );
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
	 * Save extra taxonomy fields callback function.
	 * @param  term id $term_id the id of the term
	 * @return updated option          updated option for term featured image
	 *
	 * @since 2.0.0
	 */
	public function save_taxonomy_custom_meta( $term_id ) {

		if ( ! isset( $_POST['displayfeaturedimagegenesis'] ) ) {
			return;
		}
		$displaysetting = displayfeaturedimagegenesis_term_image( $term_id );
		if ( $GLOBALS['wp_version'] >= '4.4' ) {
			$new_image = $this->validate_taxonomy_image( $_POST['displayfeaturedimagegenesis']['term_image'] );

			if ( $displaysetting && '' === $new_image ) {
				delete_term_meta( $term_id, 'displayfeaturedimagegenesis' );
			} else if ( $displaysetting !== $new_image ) {
				update_term_meta( $term_id, 'displayfeaturedimagegenesis', $new_image );
			}
		} else {
			$cat_keys   = array_keys( $_POST['displayfeaturedimagegenesis'] );
			$is_updated = false;
			foreach ( $cat_keys as $key ) {
				if ( isset( $_POST['displayfeaturedimagegenesis'][ $key ] ) ) {
					$displaysetting[ $key ] = $_POST['displayfeaturedimagegenesis'][ $key ];
					if ( $_POST['displayfeaturedimagegenesis']['term_image'] === $displaysetting[ $key ] ) {
						$displaysetting[ $key ] = $this->validate_taxonomy_image( $_POST['displayfeaturedimagegenesis'][ $key ] );
						if ( false !== $displaysetting[ $key ] ) {
							$is_updated = true;
						}
					}
				}
			}
			// Save the option array.
			if ( $is_updated ) {
				update_option( "displayfeaturedimagegenesis_$term_id", $displaysetting );
			}
		}
	}

	/**
	 * Returns false value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New value or false, depending on allowed image size.
	 * @since  2.0.0
	 */
	protected function validate_taxonomy_image( $new_value ) {

		// if the image was selected using the old URL method
		$new_value = displayfeaturedimagegenesis_check_image_id( $new_value );
		$medium    = get_option( 'medium_size_w' );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];

		// ok for field to be empty
		if ( $new_value && ( ! $valid || $width <= $medium ) ) {
			$new_value = false;
		}

		return (int) $new_value;
	}

	/**
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 2.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		$term_help  = '<h3>' . __( 'Set Featured Image', 'display-featured-image-genesis' ) . '</h3>';
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
	public function split_shared_term( $old_term_id, $new_term_id ) {

		$old_setting = get_option( "displayfeaturedimagegenesis_$old_term_id" );
		$new_setting = get_option( "displayfeaturedimagegenesis_$new_term_id" );

		if ( ! isset( $old_setting ) ) {
			return;
		}

		$new_setting = $old_setting;

		update_option( "displayfeaturedimagegenesis_$new_term_id", $new_setting );

	}
}
