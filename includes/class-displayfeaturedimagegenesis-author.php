<?php

class Display_Featured_Image_Genesis_Author {

	protected $settings;
	protected $name;

	/**
	 * Set new profile field for authors
	 *
	 * @since 2.3.0
	 */
	public function set_author_meta() {

		$this->settings = new Display_Featured_Image_Genesis_Settings();
		$this->name     = 'displayfeaturedimagegenesis';
		// current user
		add_action( 'profile_personal_options', array( $this, 'do_author_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
		// not current user
		add_action( 'edit_user_profile', array( $this, 'do_author_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
	}

	function do_author_fields( $user ) {

		$id = get_the_author_meta( $this->name, $user->ID );

		echo '<table class="form-table">';

			echo '<tr class="user-featured-image-wrap">';
				echo '<th scope="row"><label for="' . esc_attr( $this->name ) . '">Featured Image</label></th>';

				echo '<td>';
				if ( $id ) {
					echo wp_kses_post( $this->settings->render_image_preview( $id ) );
				}

				$this->settings->render_buttons( $id, $this->name );
				echo '<p class="description">Upload an image to use as your author page featured image.</p>';
				echo '</td>';
			echo '</tr>';

		echo '</table>';
	}

	function save_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( empty( $_POST['_wpnonce'] ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		$new_value = $_POST[ $this->name ];
		$old_value = get_the_author_meta( $this->name, $user_id );
		if ( $old_value !== $new_value ) {
			$new_value = $this->settings->validate_author_image( $new_value, $old_value );

			update_user_meta( $user_id, $this->name, $new_value );
		}
	}

}
