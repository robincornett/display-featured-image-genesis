<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2017 Robin Cornett Creative, LLC
 */
class Display_Featured_Image_Genesis_Settings_Validate {

	/**
	 * @var
	 */
	protected $fields;

	/**
	 * @var
	 */
	protected $setting;

	/**
	 * Display_Featured_Image_Genesis_Settings_Validate constructor.
	 *
	 * @param $fields
	 * @param $setting
	 */
	public function __construct( $fields, $setting ) {
		$this->fields  = $fields;
		$this->setting = $setting;
	}

	/**
	 * validate all inputs
	 *
	 * @param  $new_value array
	 *
	 * @return array
	 *
	 * @since  1.4.0
	 */
	public function validate( $new_value ) {
		foreach ( $this->fields as $field ) {
			if ( empty( $field['type'] ) ) {
				$new_value[ $field['id'] ] = null;
			} elseif ( 'image' === $field['type'] ) {
				if ( 'default' === $field['id'] ) {
					$new_value[ $field['id'] ] = $this->validate_single_image( $new_value[ $field['id'] ], $field );
				} elseif ( isset( $new_value['post_type'][ $field['id'] ] ) ) {
					$new_value['post_type'][ $field['id'] ] = $this->validate_post_type_images( $new_value['post_type'][ $field['id'] ], $field );
				}
			} else {
				$new_value[ $field['id'] ] = $this->type_switcher( $new_value[ $field['id'] ], $field );
			}
		}

		return $new_value;
	}

	/**
	 * Cycle through field types and validate accordingly.
	 *
	 * @param $new_value
	 * @param $field
	 *
	 * @return int|string|void
	 * @since 3.1.0
	 *
	 */
	private function type_switcher( $new_value, $field ) {
		switch ( $field['type'] ) {
			case 'number':
				if ( 'max_height' === $field['id'] && empty( $new_value ) ) {
					continue;
				}
				$new_value = $this->check_value( $new_value, $this->setting[ $field['id'] ], $field['min'], $field['max'] );
				break;

			case 'checkbox':
				$new_value = $this->one_zero( $new_value );
				break;

			case 'radio':
				$new_value = absint( $new_value );
				break;

			case 'checkbox_array':
				foreach ( $field['options'] as $option => $label ) {
					$new_value[ $option ] = isset( $new_value[ $option ] ) ? $this->one_zero( $new_value[ $option ] ) : 0;
				}
				break;

			default:
				$new_value = is_numeric( $new_value ) ? (int) $new_value : esc_attr( $new_value );
		}

		return $new_value;
	}

	/**
	 * Validate a single image.
	 *
	 * @param $new_value
	 * @param $field
	 *
	 * @return string
	 * @since 3.1.0
	 */
	private function validate_single_image( $new_value, $field ) {
		return $this->validate_image( $new_value, $this->setting[ $field['id'] ], $field['title'], displayfeaturedimagegenesis_get()->minimum_backstretch_width() );
	}

	/**
	 * Validate all post type images.
	 *
	 * @param $new_value
	 * @param $field
	 *
	 * @return string
	 * @since 3.1.0
	 *
	 */
	private function validate_post_type_images( $new_value, $field ) {
		$size_to_check = get_option( 'medium_size_w' );
		$old_value     = isset( $this->setting['post_type'][ $field['id'] ] ) ? $this->setting['post_type'][ $field['id'] ] : '';

		return $this->validate_image( $new_value, $old_value, $field['title'], $size_to_check );
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the
	 * old value.
	 *
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min       int minimum value
	 * @param $max       int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}

		return (int) $old_value;
	}

	/**
	 * Returns previous value for image if not correct file type/size
	 *
	 * @param  string $new_value New value
	 *
	 * @return string            New or previous value, depending on allowed image size.
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
	protected function image_validation_message( $valid, $label ) {
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
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 *
	 * @return integer 1 or 0.
	 */
	public function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * check if file type is image. updated to use WP function.
	 * @return bool
	 * @since  1.2.2
	 * @since  2.5.0
	 */
	protected function is_valid_img_ext( $file ) {
		$valid = wp_check_filetype( $file );

		return (bool) in_array( $valid['ext'], $this->allowed_file_types(), true );
	}

	/**
	 * Define the array of allowed image/file types.
	 * @return array
	 * @since 2.5.0
	 */
	protected function allowed_file_types() {
		$allowed = apply_filters( 'displayfeaturedimage_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif' ) );

		return is_array( $allowed ) ? $allowed : explode( ',', $allowed );
	}
}
