<?php

/**
 * Class Display_Featured_Image_Genesis_Author
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Author extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The key for the plugin user meta.
	 * @var string $name
	 */
	protected $name;

	/**
	 * Set new profile field for authors
	 *
	 * @since 2.3.0
	 */
	public function set_author_meta() {

		$this->name = 'displayfeaturedimagegenesis';
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
				printf( '<th scope="row"><label for="%s">%s</label></th>', esc_attr( $this->name ), __( 'Featured Image', 'display-featured-image-genesis' ) );

				echo '<td>';
				if ( $id ) {
					echo wp_kses_post( $this->render_image_preview( $id, $user->display_name ) );
				}

				$this->render_buttons( $id, $this->name );
				printf( '<p class="description">%s</p>', __( 'Upload an image to use as your author page featured image.', 'display-featured-image-genesis' ) );
				echo '</td>';
			echo '</tr>';

		echo '</table>';
	}

	/**
	 * Update the user meta.
	 * @param $user_id int The user being updated
	 *
	 * @return bool
	 */
	public function save_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( empty( $_POST['_wpnonce'] ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		$new_value = $_POST[ $this->name ];
		$old_value = get_the_author_meta( $this->name, $user_id );
		if ( $old_value !== $new_value ) {
			$new_value = $this->validate_author_image( $new_value, $old_value );

			update_user_meta( $user_id, $this->name, $new_value );
		}
	}

	/**
	 * Returns old value for author image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New value or old, depending on allowed image size.
	 * @since  2.3.0
	 */
	public function validate_author_image( $new_value, $old_value ) {

		$medium = get_option( 'medium_size_w' );
		$source = wp_get_attachment_image_src( $new_value, 'full' );
		$valid  = $this->is_valid_img_ext( $source[0] );
		$width  = $source[1];

		if ( ! $new_value || ( $new_value && $valid && $width > $medium ) ) {
			return $new_value;
		}

		add_filter( 'user_profile_update_errors', array( $this, 'user_profile_error_message' ), 10, 3 );

		return $old_value;

	}

	/**
	 * User profile error message
	 * @param  var $errors error message depending on what's wrong
	 * @param  var $update whether or not to update
	 * @param  object $user   user being updated
	 * @return error message
	 *
	 * @since 2.3.0
	 */
	public function user_profile_error_message( $errors, $update, $user ) {
		$new_value = (int) $_POST['displayfeaturedimagegenesis'];
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = $this->is_valid_img_ext( $source[0] );
		$reset     = sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $user->display_name );
		$error     = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' );

		if ( ! $valid ) {
			$error = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' );
		}
		$errors->add( 'profile_error', $error . $reset );
	}
}
