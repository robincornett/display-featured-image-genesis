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
	 * @var \DisplayFeaturedImageGenesisSettingsValidateImage
	 */
	private $validator;

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
		$validator = $this->get_image_validator();

		return $validator->validate_image( $new_value, $this->setting[ $field['id'] ], $field['title'], displayfeaturedimagegenesis_get()->minimum_backstretch_width() );
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
		$validator     = $this->get_image_validator();

		return $validator->validate_image( $new_value, $old_value, $field['title'], $size_to_check );
	}

	/**
	 * @return \DisplayFeaturedImageGenesisSettingsValidateImage
	 */
	private function get_image_validator() {
		if ( isset( $this->validator ) ) {
			return $this->validator;
		}
		include_once 'class-displayfeaturedimagegenesis-settings-validate-image.php';
		$this->validator = new DisplayFeaturedImageGenesisSettingsValidateImage();

		return $this->validator;
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
