<?php

/**
 * Class Display_Featured_Image_Genesis_Post_Meta
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014-2016 Robin Cornett Creative, LLC
 */
class Display_Featured_Image_Genesis_Post_Meta {

	/**
	 * Post meta key to disable buttons
	 * @var string
	 */
	protected $disable = '_displayfeaturedimagegenesis_disable';

	/**
	 * Post meta key to not move titles
	 * @var string
	 */
	protected $move = '_displayfeaturedimagegenesis_move';

	/**
	 * Build the metabox with the checkbox setting.
	 * @since 2.5.0
	 */
	public function meta_box( $content, $post_id ) {

		$output     = wp_nonce_field( 'displayfeaturedimagegenesis_post_save', 'displayfeaturedimagegenesis_post_nonce', true, false );
		$checkboxes = $this->get_checkboxes();
		foreach ( $checkboxes as $checkbox ) {
			$output .= $this->do_checkbox( $checkbox, $post_id );
		}

		return $output . $content;
	}

	/**
	 * Define array of checkboxes to add to post editor featured image.
	 * @return array
	 * @since 2.5.2
	 */
	protected function get_checkboxes() {
		return array(
			array(
				'setting' => $this->disable,
				'label'   => __( 'Don\'t show the featured image on this post', 'display-featured-image-genesis' ),
			),
			array(
				'setting' => $this->move,
				'label'   => __( 'Don\'t move the title to overlay the featured image on this post', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Generic function to add a post_meta checkbox
	 * @param $args array includes setting and label
	 *
	 * @return string checkbox label/input
	 * @since 2.5.2
	 */
	protected function do_checkbox( $args, $post_id ) {
		$check   = get_post_meta( $post_id, $args['setting'], true ) ? 1 : '';
		$output  = '<p>';
		$output .= sprintf( '<label for="%s">', $args['setting'] );
		$output .= sprintf( '<input type="checkbox" id="%1$s" name="%1$s" %2$s />%3$s', $args['setting'], checked( $check, 1, false ), $args['label'] );
		$output .= '</label>';
		$output .= '</p>';

		return $output;
	}

	/**
	 * Update the post meta.
	 * @param $post_id
	 */
	public function save_meta( $post_id ) {

		// Bail if we're doing an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// if our nonce isn't there, or we can't verify it, bail
		if ( ! $this->user_can_save( 'displayfeaturedimagegenesis_post_save', 'displayfeaturedimagegenesis_post_nonce' ) ) {
			return;
		}

		// if our current user can't edit this post, bail
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$checkboxes = $this->get_checkboxes();

		foreach ( $checkboxes as $checkbox ) {
			if ( isset( $_POST[ $checkbox['setting'] ] ) ) {
				update_post_meta( $post_id, $checkbox['setting'], 1 );
			} else {
				delete_post_meta( $post_id, $checkbox['setting'] );
			}
		}
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    1.2.0
	 * @access   protected
	 *
	 * @param    string $action The name of the action specified on the submenu page
	 * @param    string $nonce  The nonce specified on the submenu page
	 *
	 * @return   bool                True if the user has permission to save; false, otherwise.
	 * @author   Tom McFarlin (https://tommcfarlin.com/save-wordpress-submenu-page-options/)
	 */
	protected function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}

		return ( $is_nonce_set && $is_valid_nonce );
	}
}
