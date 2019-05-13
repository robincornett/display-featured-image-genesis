<?php

/**
 * settings for taxonomy pages
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 *
 * @since 2.0.0
 */
class Display_Featured_Image_Genesis_Taxonomies extends Display_Featured_Image_Genesis_Helper {

	/**
	 * set up all actions for adding featured images to taxonomies
	 * @since  2.0.0
	 */
	public function set_taxonomy_meta() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			return;
		}

		register_meta( 'term', $this->page, array( $this, 'validate_taxonomy_image' ) );

		$args       = array(
			'public' => true,
		);
		$output     = 'names';
		$taxonomies = get_taxonomies( $args, $output );
		$taxonomies = apply_filters( 'displayfeaturedimagegenesis_get_taxonomies', $taxonomies );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( "create_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( "edit_{$taxonomy}", array( $this, 'save_taxonomy_custom_meta' ) );
			add_action( 'load-edit-tags.php', array( $this, 'help' ) );
		}

		add_action( 'split_shared_term', array( $this, 'split_shared_term' ) );

	}

	/**
	 * Remove Edit Flow's post statuses from the allowed taxonomies.
	 * @param $taxonomies
	 *
	 * @return mixed
	 */
	public function remove_post_status_terms( $taxonomies ) {
		unset( $taxonomies['post_status'] );
		return $taxonomies;
	}

	/**
	 * add featured image uploader to new term add
	 */
	public function add_taxonomy_meta_fields() {

		?>
		<div class="form-field term-image-wrap">
			<?php wp_nonce_field( $this->page, $this->page ); ?>
			<label for="<?php echo esc_attr( $this->page ); ?>[term_image]"><?php esc_attr_e( 'Featured Image', 'display-featured-image-genesis' ); ?></label>
			<?php $this->render_buttons( false, "{$this->page}[term_image]" ); ?>
			<p class="description"><?php esc_attr_e( 'Set Featured Image for new term.', 'display-featured-image-genesis' ); ?></p>
		</div>
		<?php
	}

	/**
	 * upload/preview featured image for term. edit term page
	 * @param  object $term featured image input/display for individual term page
	 *
	 * @since  2.0.0
	 */
	public function edit_taxonomy_meta_fields( $term ) {

		$term_id  = $term->term_id;
		$image_id = displayfeaturedimagegenesis_get_term_image( $term_id );

		echo '<tr class="form-field term-image-wrap">';
		printf(
			'<th scope="row" ><label for="%s[term_image]">%s</label></th>',
			esc_attr( $this->page ),
			esc_attr__( 'Featured Image', 'display-featured-image-genesis' )
		);
		echo '<td>';
		$name = "{$this->page}[term_image]";
		if ( $image_id ) {
			echo wp_kses_post( $this->render_image_preview( $image_id, $term->name ) );
		}
		$this->render_buttons( $image_id, $name );
		echo '<p class="description">';
		printf(
			/* translators: name of the term */
			esc_attr__( 'Set Featured Image for %1$s.', 'display-featured-image-genesis' ),
			esc_attr( $term->name )
		);
		echo '</p>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Save extra taxonomy fields callback function.
	 * @param $term_id int the id of the term
	 *
	 * @since 2.0.0
	 */
	public function save_taxonomy_custom_meta( $term_id ) {
		$input = filter_input( INPUT_POST, $this->page, FILTER_DEFAULT, FILTER_FORCE_ARRAY );
		if ( ! $input ) {
			return;
		}
		$displaysetting = get_option( "{$this->page}_{$term_id}", false );
		$this->update_term_meta( $term_id, $input, $displaysetting );
	}

	/**
	 * update/delete term meta
	 *
	 * @param int   $term_id        term id
	 * @param       $input
	 * @param array $displaysetting old option, if it exists
	 *
	 * @since 2.4.0
	 */
	protected function update_term_meta( $term_id, $input, $displaysetting ) {
		$new_image = $this->validate_taxonomy_image( $input['term_image'] );
		if ( null === $new_image ) {
			// if the new image is empty, delete term_meta and old option
			delete_term_meta( $term_id, $this->page );
			delete_option( "{$this->page}_{$term_id}" );
		} elseif ( false !== $new_image ) {
			// if the new image is different from the existing term meta
			$current_setting = get_term_meta( $term_id, $this->page );
			if ( $current_setting !== $new_image ) {
				update_term_meta( $term_id, $this->page, (int) $new_image );
			}
			// if there is a valid image, and the old setting exists
			if ( $displaysetting ) {
				delete_option( "{$this->page}_{$term_id}" );
			}
		}
	}

	/**
	 * update term option (for sites running WP below 4.4)
	 * @param  int $term_id        term id
	 * @param  array $displaysetting previous option, if it exists
	 *
	 * @since 2.4.0
	 */
	protected function update_options_meta( $term_id, $input, $displaysetting ) {
		_deprecated_function( __FUNCTION__, '3.1.0' );
		$cat_keys   = array_keys( $input );
		$is_updated = false;
		foreach ( $cat_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$displaysetting[ $key ] = $input[ $key ];
				if ( $input['term_image'] !== $displaysetting[ $key ] ) {
					break;
				}
				$displaysetting[ $key ] = $this->validate_taxonomy_image( $input[ $key ] );
				if ( null === $displaysetting[ $key ] ) {
					delete_option( "{$this->page}_{$term_id}" );
				} elseif ( false !== $displaysetting[ $key ] ) {
					$is_updated = true;
				}
			}
		}
		// Save the option array.
		if ( $is_updated ) {
			update_option( "{$this->page}_{$term_id}", $displaysetting );
		}
	}

	/**
	 * Returns false value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New value or false, depending on allowed image size.
	 * @since  2.0.0
	 */
	protected function validate_taxonomy_image( $new_value ) {

		if ( ! $new_value ) {
			return null;
		}

		$medium = get_option( 'medium_size_w' );
		$source = wp_get_attachment_image_src( $new_value, 'full' );
		$valid  = $this->is_valid_img_ext( $source[0] );
		$width  = $source[1];

		if ( $valid && $width > $medium ) {
			return (int) $new_value;
		}

		return false;
	}

	/**
	 * Help tab for media screen
	 *
	 * @since 2.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		$term_help  = '<h3>' . __( 'Set Featured Image', 'display-featured-image-genesis' ) . '</h3>';
		$term_help .= '<p>' . __( 'You may set a featured image for your terms. This image will be used on the term archive page, and as a fallback image on a single post page if it does not have a featured image of its own.', 'display-featured-image-genesis' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => 'displayfeaturedimage_term-help',
				'title'   => __( 'Featured Image', 'display-featured-image-genesis' ),
				'content' => $term_help,
			)
		);
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

		$old_setting = get_option( "{$this->page}_{$old_term_id}" );
		$new_setting = get_option( "{$this->page}_{$new_term_id}" );

		if ( ! isset( $old_setting ) ) {
			return;
		}

		$new_setting = $old_setting;

		update_option( "{$this->page}_{$new_term_id}", $new_setting );
	}
}
