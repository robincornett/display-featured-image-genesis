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

		register_meta(
			'term',
			$this->page,
			array(
				'type'   => 'integer',
				'single' => true,
			)
		);

		$args       = array(
			'public' => true,
		);
		$output     = 'names';
		$taxonomies = get_taxonomies( $args, $output );
		$taxonomies = apply_filters( 'displayfeaturedimagegenesis_get_taxonomies', $taxonomies );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_meta_fields' ), 5, 2 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_meta_fields' ), 5, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save_term_meta' ) );
			add_action( "create_{$taxonomy}", array( $this, 'save_term_meta' ) );
			add_action( "edit_{$taxonomy}", array( $this, 'save_term_meta' ) );
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

		echo '<div class="form-field term-image-wrap">';
		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		printf(
			'<label for="%s">%s</label>',
			esc_attr( $this->page ),
			esc_attr( 'Featured Image', 'display-featured-image-genesis' )
		);
		$images = $this->get_images_class();
		$images->render_buttons( false, $this->page );
		printf(
			'<p class="description">%s</p>',
			esc_attr( 'Set Featured Image for new term.', 'display-featured-image-genesis' )
		);
		echo '</div>';
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

		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		echo '<tr class="form-field term-image-wrap">';
		printf(
			'<th scope="row" ><label for="%s">%s</label></th>',
			esc_attr( $this->page ),
			esc_attr__( 'Featured Image', 'display-featured-image-genesis' )
		);
		echo '<td>';
		$images = $this->get_images_class();
		if ( $image_id ) {
			echo wp_kses_post( $images->render_image_preview( $image_id, $term->name ) );
		}
		$images->render_buttons( $image_id, $this->page );
		echo '<p class="description">';
		$width = get_option( 'medium_size_w' ) + 1;
		printf(
			/* translators: 1. name of the term */
			esc_attr__( 'Set Featured Image for %1$s. It must be at least %2$s pixels wide.', 'display-featured-image-genesis' ),
			esc_attr( $term->name ),
			esc_attr( $width )
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
	public function save_term_meta( $term_id ) {
		if ( ! $this->user_can_save( "{$this->page}_save-settings", "{$this->page}_nonce" ) ) {
			return;
		}
		$input = filter_input( INPUT_POST, $this->page, FILTER_DEFAULT );
		if ( ! $input ) {
			return;
		}

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings/class-displayfeaturedimagegenesis-settings-validate-image.php';
		$medium          = get_option( 'medium_size_w' );
		$validator       = new DisplayFeaturedImageGenesisSettingsValidateImage();
		$current_setting = $this->get_current_term_setting( $term_id );
		$new_image       = $validator->validate_image( $input, $current_setting, 'term name', $medium );
		if ( $new_image ) {
			update_term_meta( $term_id, $this->page, $new_image );
		} else {
			delete_term_meta( $term_id, $this->page );
		}
	}

	/**
	 * Get the current term meta or option, if it exists.
	 *
	 * @param $term_id
	 *
	 * @return mixed|void
	 * @since 3.1.0
	 */
	private function get_current_term_setting( $term_id ) {
		$meta = get_term_meta( $term_id, $this->page, true );
		if ( $meta ) {
			return $meta;
		}
		$setting = get_option( "{$this->page}_{$term_id}", false );
		if ( $setting ) {
			delete_option( "{$this->page}_{$term_id}" );
		}

		return $setting;
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
