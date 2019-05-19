<?php

/**
 * Class Display_Featured_Image_Genesis_Author
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2019 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Author extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The key for the plugin user meta.
	 * @var string $name
	 */
	protected $name = 'displayfeaturedimagegenesis';

	/**
	 * Set new profile field for authors
	 *
	 * @since 2.3.0
	 */
	public function set_author_meta() {

		// current user
		add_action( 'profile_personal_options', array( $this, 'do_author_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		// not current user
		add_action( 'edit_user_profile', array( $this, 'do_author_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
	}

	/**
	 * Add the featured image row to the user profile.
	 * @param $user object the user being edited.
	 */
	public function do_author_fields( $user ) {

		$id = get_the_author_meta( $this->name, $user->ID );

		echo '<table class="form-table">';

		echo '<tr class="user-featured-image-wrap">';
		printf( '<th scope="row"><label for="%s">%s</label></th>', esc_attr( $this->name ), esc_html__( 'Featured Image', 'display-featured-image-genesis' ) );

		echo '<td>';
		$images = $this->get_images_class();
		if ( $id ) {
			echo wp_kses_post( $images->render_image_preview( $id, $user->display_name ) );
		}

		$images->render_buttons( $id, $this->name );
		printf( '<p class="description">%s</p>', esc_html__( 'Upload an image to use as your author page featured image.', 'display-featured-image-genesis' ) );
		echo '</td>';
		echo '</tr>';

		echo '</table>';
	}

	/**
	 * Update the user meta.
	 * @param $user_id int The user being updated
	 *
	 */
	public function save_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if ( ! filter_input( INPUT_POST, '_wpnonce', FILTER_DEFAULT ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		$new_value = filter_input( INPUT_POST, $this->name, FILTER_DEFAULT );
		$old_value = get_the_author_meta( $this->name, $user_id );
		if ( $old_value !== $new_value ) {
			$user_object = get_userdata( $user_id );
			$medium      = get_option( 'medium_size_w' );
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings/class-displayfeaturedimagegenesis-settings-validate-image.php';
			$validator = new DisplayFeaturedImageGenesisSettingsValidateImage();
			$new_value = $validator->validate_image( $new_value, $old_value, $user_object->display_name, $medium );

			update_user_meta( $user_id, $this->name, $new_value );
		}
	}
}
