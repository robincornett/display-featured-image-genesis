<?php

/**
 * Class Display_Featured_Image_Genesis_Post_Meta
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2020 Robin Cornett Creative, LLC
 */
class Display_Featured_Image_Genesis_Post_Meta {

	/**
	 * ID for our new metabox.
	 * @var string
	 */
	protected $metabox = 'displayfeaturedimagegenesis';

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
	 * For Gutenberg, add a new metabox, since the thumbnail hooks are no longer present.
	 * Should eventually be replaced with a block.
	 *
	 * @since 3.0.0
	 *
	 * @param $post_type
	 * @param $post
	 */
	public function add_metabox( $post_type, $post ) {
		if ( ! $this->is_block_editor() ) {
			return;
		}
		if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
			return;
		}
		add_meta_box(
			$this->metabox,
			__( 'Display Featured Image', 'display-featured-image-genesis' ),
			array( $this, 'do_metabox' ),
			$post_type,
			'side',
			'low'
		);
	}

	/**
	 * Output the metabox.
	 *
	 * @param $post
	 * @param $args
	 */
	public function do_metabox( $post, $args ) {
		echo $this->get_metabox_content( $post->ID );
	}

	/**
	 * Build the metabox with the checkbox setting.
	 * @since 2.5.0
	 *
	 * @param $content
	 * @param $post_id
	 *
	 * @return string
	 */
	public function meta_box( $content, $post_id ) {
		return $this->get_metabox_content( $post_id ) . $content;
	}

	/**
	 * Get the metabox content/fields.
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function get_metabox_content( $post_id ) {
		$output = wp_nonce_field( "{$this->metabox}_post_save", "{$this->metabox}_post_nonce", true, false );
		$select = $this->get_select();
		if ( $select ) {
			foreach ( $select as $s ) {
				$output .= $this->do_select( $s, $post_id );
			}
		}
		$checkboxes = $this->get_checkboxes();
		if ( $checkboxes ) {
			foreach ( $checkboxes as $checkbox ) {
				$output .= $this->do_checkbox( $checkbox, $post_id );
			}
		}

		return $output;
	}

	/**
	 * Define array of checkboxes to add to post editor featured image.
	 * @return array
	 * @since 2.5.2
	 */
	protected function get_checkboxes() {
		$checkboxes = array();
		$setting    = displayfeaturedimagegenesis_get_setting( 'keep_titles' );
		if ( ! $setting ) {
			$checkboxes[] = array(
				'setting' => $this->move,
				'label'   => __( 'Don\'t move the title to overlay the backstretch featured image on this post', 'display-featured-image-genesis' ),
			);
		}

		return $checkboxes;
	}

	/**
	 * @return array
	 */
	protected function get_select() {
		$options     = array(
			0 => __( 'Content type default', 'display-featured-image-genesis' ),
			1 => __( 'Don\'t display the featured image', 'display-featured-image-genesis' ),
		);
		$image_sizes = apply_filters(
			'displayfeaturedimagegenesis_image_size_choices',
			array(
				'2048x2048' => __( 'Use a banner image if it exists', 'display-featured-image-genesis' ),
				'large'     => __( 'Use a large (not banner) image', 'display-featured-image-genesis' ),
			)
		);

		return array(
			array(
				'setting' => $this->disable,
				'label'   => __( 'Featured Image Size:', 'display-featured-image-genesis' ),
				'options' => array_merge( $options, $image_sizes ),
			),
		);
	}

	/**
	 * Generic function to add a post_meta checkbox
	 *
	 * @param $args array includes setting and label
	 *
	 * @param $post_id
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
	 * @param $args
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function do_select( $args, $post_id ) {
		$value = get_post_meta( $post_id, $args['setting'], true );
		if ( 'displayfeaturedimage_backstretch' === $value ) {
			$value = '2048x2048';
		}
		$output = sprintf(
			'<p><label for="%2$s">%1$s</label><select id="%2$s" name="%2$s">',
			esc_attr( $args['label'] ),
			esc_attr( $args['setting'] )
		);
		foreach ( (array) $args['options'] as $option => $field_label ) {
			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option ),
				selected( $option, $value, false ),
				esc_attr( $field_label )
			);
		}
		$output .= '</select></p>';

		return $output;
	}

	/**
	 * Check whether we are on a block editor/Gutenberg screen.
	 *
	 * @return bool
	 */
	private function is_block_editor() {
		if ( ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) ) {
			return true;
		}
		$screen = get_current_screen();
		if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
			return true;
		}

		return false;
	}

	/**
	 * Update the post meta.
	 *
	 * @param $post_id
	 */
	public function save_meta( $post_id ) {

		// Bail if we're doing an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// if our nonce isn't there, or we can't verify it, bail
		if ( ! $this->user_can_save( "{$this->metabox}_post_save", "{$this->metabox}_post_nonce" ) ) {
			return;
		}

		// if our current user can't edit this post, bail
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$checkboxes = $this->get_checkboxes();

		foreach ( $checkboxes as $checkbox ) {
			if ( filter_input( INPUT_POST, $checkbox['setting'], FILTER_DEFAULT ) ) {
				update_post_meta( $post_id, $checkbox['setting'], 1 );
			} else {
				delete_post_meta( $post_id, $checkbox['setting'] );
			}
		}

		$select = $this->get_select();
		foreach ( $select as $s ) {
			$value = filter_input( INPUT_POST, $s['setting'], FILTER_DEFAULT );
			$value = is_numeric( $value ) ? (int) $value : esc_attr( $value );
			update_post_meta( $post_id, $s['setting'], $value );
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
