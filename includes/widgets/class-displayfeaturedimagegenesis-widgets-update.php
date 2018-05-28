<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsUpdate
 */
class DisplayFeaturedImageGenesisWidgetsUpdate {
	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 3.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param $old_instance
	 * @param $fields
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance, $fields ) {
		foreach ( $fields as $field ) {
			$value = $field['args']['id'];
			if ( ! isset( $new_instance[ $value ] ) ) {
				continue;
			}
			switch ( $field['method'] ) {
				// Sanitize numbers
				case 'number':
					$new_instance[ $value ] = $new_instance[ $value ] ? absint( $new_instance[ $value ] ) : '';
					break;

				// Sanitize checkboxes
				case 'checkbox':
					$new_instance[ $value ] = (int) (bool) $new_instance[ $value ];
					break;

				// Sanitize text fields
				case 'text':
					$new_instance[ $value ] = strip_tags( $new_instance[ $value ] );
					break;

				// Escape select options
				case 'select':
					$new_instance[ $value ] = is_numeric( $new_instance[ $value ] ) ? (int) $new_instance[ $value ] : esc_attr( $new_instance[ $value ] );
					break;

				case 'textarea':
					if ( function_exists( 'sanitize_textarea_field' ) ) {
						$new_instance[ $value ] = sanitize_textarea_field( $new_instance[ $value ] );
					} else {
						$new_instance[ $value ] = esc_textarea( $new_instance[ $value ] );
					}
					break;

				// Default
				default:
					$new_instance[ $value ] = esc_attr( $new_instance[ $value ] );
					break;
			}
		} // End foreach().

		// Title is never included in the fields.
		$new_instance['title'] = strip_tags( $new_instance['title'] );

		return $new_instance;

	}
}
