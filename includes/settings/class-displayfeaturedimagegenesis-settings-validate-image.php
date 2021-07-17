<?php

/**
 * Class DisplayFeaturedImageGenesisSettingsValidateImage
 * @since     3.1.0
 * @copyright 2019-2020 Robin Cornett
 */
class DisplayFeaturedImageGenesisSettingsValidateImage {

	/**
	 * Returns previous value for image if not correct file type/size
	 *
	 * @param string $new_value New value
	 * @param string $old_value
	 * @param string $label
	 * @param int    $size_to_check
	 *
	 * @return int|mixed            New or previous value, depending on allowed image size.
	 * @since  1.2.2
	 */
	public function validate_image( $new_value, $old_value, $label, $size_to_check ) {

		// ok for field to be empty
		if ( ! $new_value ) {
			return '';
		}

		$source = wp_get_attachment_image_src( $new_value, 'full' );
		$valid  = (bool) $this->is_valid_img_ext( $source[0] );
		$width  = $source[1];

		if ( $valid && $width > $size_to_check ) {
			return (int) $new_value;
		}

		$class   = 'invalid';
		$message = $this->image_validation_message( $valid, $label );
		if ( ! is_customize_preview() ) {
			add_settings_error(
				$old_value,
				esc_attr( $class ),
				esc_attr( $message ),
				'error'
			);
			add_filter( 'user_profile_update_errors', array( $this, 'user_profile_error_message' ), 10, 3 );
		} elseif ( method_exists( 'WP_Customize_Setting', 'validate' ) ) {
			return new WP_Error( esc_attr( $class ), esc_attr( $message ) );
		}

		return (int) $old_value;
	}

	/**
	 * Define the error message for invalid images.
	 *
	 * @param $valid bool false if the filetype is invalid.
	 * @param $label string which context the image is from.
	 *
	 * @return string
	 */
	private function image_validation_message( $valid, $label ) {
		$message = __( 'Sorry, your image is too small.', 'display-featured-image-genesis' );
		if ( ! $valid && ! is_customize_preview() ) {
			$message = __( 'Sorry, that is an invalid file type.', 'display-featured-image-genesis' );
		}

		if ( is_customize_preview() ) {
			/* translators: the placeholder is the name of the featured image; eg. default, search, or the name of a content type. */
			$message .= sprintf( __( ' The %s Featured Image must be changed to a valid, well sized image file.', 'display-featured-image-genesis' ), $label );
		} else {
			/* translators: the placeholder is the name of the featured image; eg. default, search, or the name of a content type. */
			$message .= sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $label );
		}

		return $message;
	}

	/**
	 * User profile error message
	 *
	 * @param object  $errors        error message depending on what's wrong
	 * @param bool    $existing_user existing user
	 * @param object  $user          user being updated
	 *
	 * @since 2.3.0
	 */
	public function user_profile_error_message( $errors, $existing_user, $user ) {
		/* translators: the user display name */
		$reset = sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $user->display_name );

		$errors->add( 'profile_error', $reset );
	}

	/**
	 * check if file type is image. updated to use WP function.
	 * @return bool
	 * @since  1.2.2
	 * @since  2.5.0
	 */
	private function is_valid_img_ext( $file ) {
		$valid = wp_check_filetype( $file );

		return (bool) in_array( $valid['ext'], $this->allowed_file_types(), true );
	}

	/**
	 * Define the array of allowed image/file types.
	 * @return array
	 * @since 2.5.0
	 */
	private function allowed_file_types() {
		$allowed = apply_filters( 'displayfeaturedimage_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ) );

		return is_array( $allowed ) ? $allowed : explode( ',', $allowed );
	}
}
