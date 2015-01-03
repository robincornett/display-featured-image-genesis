<?php

class Display_Featured_Image_Genesis_Custom_Post_Types {

	protected $post_types;
	protected $post_type;

	public function set_up_cpts() {
		$args = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output = 'names';

		$this->post_types = get_post_types( $args, $output );

		foreach ( $this->post_types as $post_type ) {
			add_submenu_page(
				"edit.php?post_type=$post_type",
				__( 'Featured Image', 'display-featured-image-genesis' ),
				__( 'Featured Image', 'display-featured-image-genesis' ),
				'manage_categories',
				"displayfeaturedimagegenesis-$post_type",
				array( $this, 'do_settings_form' )
			);

			register_setting( 'displayfeaturedimagegenesis_cpt', "displayfeaturedimagegenesis-$post_type" /*, array( $this, 'do_validation_things' )*/ );

			$this->post_type = $post_type;

		}

		add_action( 'admin_init', array( $this, 'settings' ) );

	}

	/**
	 * create CPT settings form
	 * @return form Display Featured Image for Genesis for CPT
	 *
	 * @since  x.y.z
	 */
	public function do_settings_form() {
		$page_title = get_admin_page_title();

		echo '<div class="wrap">';
			echo '<h2>' . $page_title . '</h2>';
			echo '<form action="options.php" method="post">';
				settings_fields( 'displayfeaturedimagegenesis_cpt' );
				do_settings_sections( 'displayfeaturedimagegenesis_cpt' );
				// wp_nonce_field( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce', false );
				submit_button();
				// settings_errors();
			echo '</form>';
		echo '</div>';
	}

	public function settings() {

		add_settings_section(
			'display_featured_image_cpt_section',
			__( 'Optional Featured Image', 'display-featured-image-genesis' ),
			array( $this, 'section_description' ),
			'displayfeaturedimagegenesis_cpt'
		);

		add_settings_field(
			"displayfeaturedimagegenesis-$this->post_type",
			'<label for="displayfeaturedimagegenesis-cpt">' . __( 'Custom Post Type Featured Image', 'display-featured-image-genesis' ) . '</label>',
			array( $this, 'set_cpt_image' ),
			'displayfeaturedimagegenesis_cpt',
			'display_featured_image_cpt_section'
		);

	}

	/**
	 * Section description
	 * @return section description
	 *
	 * @since 1.1.0
	 */
	public function section_description() {
		echo '<p>' . __( 'You may optionally set a featured image for the custom post type archive to use.', 'display-featured-image-genesis' ) . '</p>';
	}

	/**
	 * Default image uploader
	 *
	 * @return  image
	 *
	 * @since  1.2.1
	 */
	public function set_cpt_image() {

		$item    = Display_Featured_Image_Genesis_Common::get_image_variables();
		$screen  = get_current_screen();
		$setting = get_option( "displayfeaturedimagegenesis-$this->post_type" );

		echo 'is there a ' . $setting;
		if ( ! empty( $setting['image'] ) ) {
			$preview = wp_get_attachment_image_src( $setting['image'], 'medium' );
			echo '<div id="upload_logo_preview">';
			echo '<img src="' . esc_url( $preview[0] ) . '" />';
			echo '</div>';
		}
		echo '<input type="url" id="default_image_url" name="displayfeaturedimagegenesis-$this->post_type" value="' . esc_url( $setting ) . '" />';
		echo '<input id="upload_default_image" type="button" class="upload_default_image button" value="' . __( 'Select Image', 'display-featured-image-genesis' ) . '" />';
		echo '<p class="description">' . sprintf(
			__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $item->large + 1 )
		) . '</p>';

	}

}
