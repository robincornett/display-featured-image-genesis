<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2017 Robin Cornett Creative, LLC
 */
class Display_Featured_Image_Genesis_Settings_Validate extends Display_Featured_Image_Genesis_Helper {

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

		// validate all checkbox fields
		foreach ( $this->fields as $field ) {
			if ( 'do_checkbox' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
			} elseif ( 'do_number' === $field['callback'] ) {
				if ( 'max_height' === $field['id'] && empty( $new_value[ $field['id'] ] ) ) {
					continue;
				}
				$new_value[ $field['id'] ] = $this->check_value( $new_value[ $field['id'] ], $this->setting[ $field['id'] ], $field['min'], $field['max'] );
			} elseif ( 'do_radio_buttons' === $field['callback'] ) {
				$new_value[ $field['id'] ] = absint( $new_value[ $field['id'] ] );
			} elseif ( 'do_checkbox_array' === $field['callback'] ) {
				foreach ( $field['options'] as $option => $label ) {
					$new_value[ $field['id'] ][ $option ] = $this->one_zero( $new_value[ $field['id'] ][ $option ] );
				}
			}
		}

		// extra variables to pass through to image validation
		$common        = new Display_Featured_Image_Genesis_Common();
		$size_to_check = $common->minimum_backstretch_width();

		// validate default image
		$new_value['default'] = $this->validate_image( $new_value['default'], $this->setting['default'], __( 'Default', 'display-featured-image-genesis' ), $size_to_check );

		// search/404
		$size_to_check = get_option( 'medium_size_w' );
		$custom_pages  = array(
			array(
				'id'    => 'search',
				'label' => __( 'Search Results', 'display-featured-image-genesis' ),
			),
			array(
				'id'    => 'fourohfour',
				'label' => __( '404 Page', 'display-featured-image-genesis' ),
			),
		);
		foreach ( $custom_pages as $page ) {
			$setting_to_check = isset( $this->setting['post_type'][ $page['id'] ] ) ? $this->setting['post_type'][ $page['id'] ] : '';
			if ( isset( $new_value['post_type'][ $page ['id'] ] ) ) {
				$new_value['post_type'][ $page ['id'] ] = $this->validate_image( $new_value['post_type'][ $page['id'] ], $setting_to_check, $page['label'], $size_to_check );
			}
		}

		foreach ( $this->get_content_types_built_in() as $post_type ) {

			$object    = get_post_type_object( $post_type );
			$old_value = isset( $this->setting['post_type'][ $object->name ] ) ? $this->setting['post_type'][ $object->name ] : '';
			$label     = $object->label;

			if ( isset( $new_value['post_type'][ $post_type ] ) ) {
				$new_value['post_type'][ $post_type ] = $this->validate_image( $new_value['post_type'][ $post_type ], $old_value, $label, $size_to_check );
			}
		}

		return $new_value;
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
	protected function validate_image( $new_value, $old_value, $label, $size_to_check ) {

		// ok for field to be empty
		if ( ! $new_value ) {
			return '';
		}

		$new_value = displayfeaturedimagegenesis_check_image_id( $new_value );
		$old_value = displayfeaturedimagegenesis_check_image_id( $old_value );
		$source    = wp_get_attachment_image_src( $new_value, 'full' );
		$valid     = (bool) $this->is_valid_img_ext( $source[0] );
		$width     = $source[1];

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

		$message .= sprintf( __( ' The %s Featured Image has been reset to the last valid setting.', 'display-featured-image-genesis' ), $label );

		return $message;
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
}
