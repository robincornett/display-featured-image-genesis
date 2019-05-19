<?php

/**
 * Class DisplayFeaturedImageGenesisSettingsImages
 */
class DisplayFeaturedImageGenesisSettingsImages {

	/**
	 * @var string
	 */
	private $page = 'displayfeaturedimagegenesis';

	/**
	 * The plugin setting.
	 * @var array $setting
	 */
	private $setting;

	/**
	 * DisplayFeaturedImageGenesisSettingsImages constructor.
	 *
	 * @param $setting
	 */
	public function __construct( $setting ) {
		$this->setting = $setting;
	}

	/**
	 * Do the image settings field.
	 *
	 * @param $args
	 *
	 * @since 3.1.0
	 */
	public function do_image( $args ) {
		$posts_page = $this->has_page_for_posts( $args['id'] );
		if ( $posts_page ) {
			/* translators: the link is to edit the posts page. */
			printf( wp_kses_post( __( 'You may set a fallback image for Posts on your <a href="%s">posts page</a>.', 'display-featured-image-genesis' ) ), esc_url( $posts_page ) );

			return;
		}
		$value = $this->get_image_value( $args['id'] );
		$name  = $this->get_image_name( $args['id'] );
		if ( ! empty( $value ) ) {
			echo wp_kses_post( $this->render_image_preview( $value, $args['id'] ) );
		}
		$this->render_buttons( $value, $name );

		if ( empty( $value ) || in_array( $args['id'], array( 'default', 'search', 'fourohfour', 'post' ), true ) ) {
			return;
		}
		$this->do_cpt_description( $args );
	}

	/**
	 * Print the CPT description.
	 *
	 * @param $args
	 */
	private function do_cpt_description( $args ) {
		$archive_link = get_post_type_archive_link( $args['id'] );
		if ( ! $archive_link ) {
			return;
		}
		$description = sprintf(
			/* translators: placeholder is the post type name. */
			__( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( $archive_link ),
			esc_attr( $args['title'] )
		);
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Check if a posts page has been set: if so, return the edit link for it, otherwise return false.
	 *
	 * @param $id
	 *
	 * @return bool|string|null
	 * @since 3.1.0
	 */
	private function has_page_for_posts( $id ) {
		if ( 'post' !== $id ) {
			return false;
		}
		$show_on_front = get_option( 'show_on_front' );
		$posts_page    = get_option( 'page_for_posts' );

		return ( 'page' === $show_on_front && $posts_page ) ? get_edit_post_link( $posts_page ) : false;
	}

	/**
	 * Get the image setting value.
	 * @since 3.1.0
	 * @param $id
	 *
	 * @return string
	 */
	private function get_image_value( $id ) {
		$value = '';
		if ( 'default' === $id && $this->setting[ $id ] ) {
			$value = $this->setting[ $id ];
		} elseif ( ! empty( $this->setting['post_type'][ $id ] ) ) {
			$value = $this->setting['post_type'][ $id ];
		}

		return $value;
	}

	/**
	 * Get the image setting name.
	 * @since 3.1.0
	 * @param $id
	 *
	 * @return string
	 */
	private function get_image_name( $id ) {
		if ( 'default' === $id ) {
			$name = "{$this->page}[{$id}]";
		} else {
			$name = "{$this->page}[post_type][{$id}]";
		}

		return $name;
	}

	/**
	 * display image preview
	 *
	 * @param int $id      featured image ID
	 * @param     $alt     string description for alt text
	 *
	 * @return string
	 *
	 * @since 2.3.0
	 */
	public function render_image_preview( $id, $alt = '' ) {
		if ( empty( $id ) ) {
			return '';
		}

		/* translators: the placeholder refers to which featured image */
		$alt_text = sprintf( __( '%s featured image', 'display-featured-image-genesis' ), esc_attr( $alt ) );
		$preview  = wp_get_attachment_image_src( (int) $id, 'medium' );

		return sprintf( '<div class="upload-image-preview"><img src="%s" alt="%s" /></div>', esc_url( $preview[0] ), esc_attr( $alt_text ) );
	}

	/**
	 * show image select/delete buttons
	 *
	 * @param int    $id   image ID
	 * @param string $name name for value/ID/class
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $id, $name ) {
		$id = $id ? (int) $id : '';
		printf( '<input type="hidden" class="upload-image-id" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( $id ) );
		printf(
			'<button id="%s" class="upload-image button-secondary">%s</button>',
			esc_attr( $name ),
			esc_attr__( 'Select Image', 'display-featured-image-genesis' )
		);
		printf(
			' <button class="delete-image button-secondary"%s>%s</button>',
			empty( $id ) ? 'style="display:none;"' : '',
			esc_attr__( 'Delete Image', 'display-featured-image-genesis' )
		);
	}
}
