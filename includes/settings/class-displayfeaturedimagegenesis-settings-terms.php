<?php
/**
 * Copyright (c) 2017 Robin Cornett
 */

class Display_Featured_Image_Genesis_Settings_Terms {

	/**
	 * The db query for old term options.
	 * @var $term_option_query
	 */
	protected $term_option_query = array();

	/**
	 * Check if term images need to be updated because they were added before WP 4.4 and this plugin 2.4.
	 * @since 2.6.1
	 */
	public function maybe_update_terms() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			return;
		}
		if ( $this->terms_have_been_updated() ) {
			return;
		}
		$previous_user = get_option( 'displayfeaturedimagegenesis', false );
		if ( ! $previous_user ) {
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );

			return;
		}
		$this->term_option_query = $this->check_term_images();
		if ( $this->term_option_query ) {
			$this->update_delete_term_meta();
			$this->term_meta_notice();
		} else {
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );
		}
	}

	/**
	 * For 4.4, output a notice explaining that old term options can be updated to term_meta.
	 * Options are to update all terms or to ignore, and do by hand.
	 * @since 2.4.0
	 */
	protected function term_meta_notice() {
		$screen = get_current_screen();
		if ( 'appearance_page_displayfeaturedimagegenesis' !== $screen->id ) {
			return;
		}
		$updated_terms = get_option( 'displayfeaturedimagegenesis_updatedterms', false );
		if ( $updated_terms ) {
			return;
		}
		$rows = $this->term_option_query;
		if ( ! $rows ) {
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );

			return;
		}
		$message  = sprintf( '<p>%s</p>', __( 'WordPress 4.4 introduces term metadata for categories, tags, and other taxonomies. This is your opportunity to optionally update all impacted terms on your site to use the new metadata.', 'display-featured-image-genesis' ) );
		$message .= sprintf( '<p>%s</p>', __( 'This <strong>will modify</strong> your database (potentially many entries at once), so if you\'d rather do it yourself, you can. Here\'s a list of the affected terms:', 'display-featured-image-genesis' ) );
		$message .= '<ul style="margin-left:24px;list-style-type:disc;">';
		foreach ( $rows as $row ) {
			$term_id = str_replace( 'displayfeaturedimagegenesis_', '', $row );
			$term    = get_term( (int) $term_id );
			if ( ! is_wp_error( $term ) && ! is_null( $term ) ) {
				$message .= edit_term_link( $term->name, '<li>', '</li>', $term, false );
			}
		}
		$message .= '</ul>';
		$message .= sprintf( '<p>%s</p>', __( 'To get rid of this notice, you can 1) update your terms by hand; 2) click the update button (please check your terms afterward); or 3) click the dismiss button.', 'display-featured-image-genesis' ) );
		$faq      = sprintf( __( 'For more information, please visit the plugin\'s <a href="%s" target="_blank">Frequently Asked Questions</a> on WordPress.org.', 'display-featured-image-genesis' ), esc_url( 'https://wordpress.org/plugins/display-featured-image-genesis/faq/' ) );
		$message .= sprintf( '<p>%s</p>', $faq );
		echo '<div class="updated">' . wp_kses_post( $message );
		echo '<form action="" method="post">';
		wp_nonce_field( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce', false );
		$buttons = array(
			array(
				'value' => __( 'Update My Terms', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmeta',
				'class' => 'button-primary',
			),
			array(
				'value' => __( 'Dismiss (Not Recommended)', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmetadismiss',
				'class' => 'button-secondary',
			),
		);
		echo '<p>';
		foreach ( $buttons as $button ) {
			printf( '<input type="submit" class="%s" name="%s" value="%s" style="margin-right:12px;" />',
				esc_attr( $button['class'] ),
				esc_attr( $button['name'] ),
				esc_attr( $button['value'] )
			);
		}
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Update and/or delete term_meta and wp_options
	 * @since 2.4.0
	 */
	protected function update_delete_term_meta() {

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			foreach ( $this->term_option_query as $option_key ) {
				$term_id = (int) str_replace( 'displayfeaturedimagegenesis_', '', $option_key );
				$option  = get_option( esc_attr( $option_key ), false );
				$term    = get_term( (int) $term_id );
				if ( ! is_wp_error( $term ) && ! is_null( $term ) ) {
					$image_id = (int) $option['term_image'];
					update_term_meta( $term_id, 'displayfeaturedimagegenesis', $image_id );
				}
				if ( false !== $option ) {
					delete_option( esc_attr( $option_key ) );
				}
			}
		}

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) || isset( $_POST['displayfeaturedimagegenesis_termmetadismiss'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );
		}
	}

	/**
	 * Get IDs of terms with featured images
	 *
	 * @param  array $term_ids empty array
	 *
	 * @return array           all terms with featured images
	 * @since      2.4.0
	 * @deprecated 2.6.1 by check_term_images() due to heavy load on sites with many terms
	 */
	protected function get_affected_terms( $affected_terms = array() ) {
		_deprecated_function( __FUNCTION__, '2.6.1', array( $this, 'check_term_images' ) );
		$args       = apply_filters( 'displayfeaturedimagegenesis_get_taxonomies', array(
			'public'  => true,
			'show_ui' => true,
		) );
		$taxonomies = get_taxonomies( $args );

		foreach ( $taxonomies as $tax ) {
			$args  = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
			$terms = get_terms( $tax, $args );
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$option  = get_option( "displayfeaturedimagegenesis_{$term_id}", false );
				if ( false !== $option ) {
					$affected_terms[] = $term;
				}
			}
		}

		return $affected_terms;
	}

	/**
	 * Check whether terms need to be updated
	 * @return boolean true if on 4.4 and wp_options for terms exist; false otherwise
	 *
	 * @since 2.4.0
	 */
	protected function terms_have_been_updated() {
		$updated = get_option( 'displayfeaturedimagegenesis_updatedterms', false );

		return (bool) $updated;
	}

	/**
	 * Check for term images stored as options.
	 * @return array|bool
	 * @since 2.6.1
	 */
	protected function check_term_images() {
		$all_options = wp_load_alloptions();
		$options     = false;

		foreach ( $all_options as $name => $value ) {
			if ( stristr( $name, 'displayfeaturedimagegenesis_' ) && 'displayfeaturedimagegenesis_updatedterms' !== $name ) {
				$options[] = $name;
			}
		}

		return $options;
	}
}
