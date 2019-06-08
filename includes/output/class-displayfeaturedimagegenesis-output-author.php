<?php

/**
 * Class DisplayFeaturedImageGenesisOutputAuthor
 * @since 3.1.0
 */
class DisplayFeaturedImageGenesisOutputAuthor {

	/**
	 * The instance of the widget.
	 *
	 * @var array
	 */
	private $instance;

	/**
	 * The sidebar args.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * The widget ID base.
	 *
	 * @var string
	 */
	private $id_base;

	/**
	 * DisplayFeaturedImageGenesisOutputAuthor constructor.
	 *
	 * @param        $instance
	 * @param        $args
	 * @param string $id_base
	 */
	public function __construct( $instance, $args, $id_base = '' ) {
		$this->instance = $instance;
		$this->args     = $this->update_args( $args );
		$this->id_base  = $id_base ? $id_base : 'display-featured-image-genesis-author';
		$this->init();
	}

	/**
	 * Output the featured author.
	 * @since 3.1.0
	 */
	private function init() {
		if ( ! empty( $this->instance['title'] ) ) {
			echo wp_kses_post( $this->args['before_title'] . apply_filters( 'widget_title', $this->instance['title'], $this->instance, $this->id_base ) . $this->args['after_title'] );
		}

		$this->do_featured_image();

		$text  = $this->get_gravatar();
		$text .= $this->get_author_description();
		echo wp_kses_post( wpautop( $text ) );

		$this->do_author_link();
	}

	/**
	 * Echo the author featured image.
	 */
	protected function do_featured_image() {
		if ( ! $this->instance['show_featured_image'] ) {
			return;
		}
		$image_id = get_the_author_meta( 'displayfeaturedimagegenesis', $this->instance['user'] );
		if ( ! $image_id ) {
			return;
		}
		echo wp_get_attachment_image(
			$image_id,
			$this->instance['featured_image_size'],
			false,
			array(
				'alt'   => get_the_author_meta( 'display_name', $this->instance['user'] ),
				'class' => $this->instance['featured_image_alignment'],
			)
		);
	}

	/**
	 * Return the author gravatar.
	 *
	 * @return string
	 */
	protected function get_gravatar() {
		if ( ! $this->instance['show_gravatar'] ) {
			return '';
		}

		$gravatar = get_avatar( $this->instance['user'], $this->instance['size'] );
		if ( empty( $this->instance['gravatar_alignment'] ) ) {
			return $gravatar;
		}

		return '<span class="align' . esc_attr( $this->instance['gravatar_alignment'] ) . '">' . $gravatar . '</span>';
	}

	/**
	 * Return the author bio/info.
	 *
	 * @param $this ->instance
	 *
	 * @return string
	 */
	public function get_author_description() {
		if ( ! $this->instance['author_info'] ) {
			return '';
		}

		return 'text' === $this->instance['author_info'] ? $this->instance['bio_text'] : get_the_author_meta( 'description', $this->instance['user'] );
	}

	/**
	 * Return the author link.
	 *
	 * @return string
	 */
	protected function get_author_link() {
		return $this->instance['page'] ? sprintf( ' <a class="pagelink" href="%s">%s</a>', get_page_link( $this->instance['page'] ), $this->instance['page_link_text'] ) : '';
	}

	/**
	 * Output the author link.
	 */
	protected function do_author_link() {
		if ( ! $this->instance['posts_link'] || ! $this->instance['link_text'] ) {
			return;
		}
		// If posts link option checked, add posts link to output
		$display_name = get_the_author_meta( 'display_name', $this->instance['user'] );
		$user_name    = ! empty( $display_name ) && function_exists( 'genesis_a11y' ) && genesis_a11y() ? '<span class="screen-reader-text">' . $display_name . ': </span>' : '';

		printf(
			'<div class="posts_link posts-link"><a href="%s">%s%s</a></div>',
			esc_url( get_author_posts_url( $this->instance['user'] ) ),
			wp_kses_post( $user_name ),
			esc_attr( $this->instance['link_text'] )
		);
	}

	/**
	 * Update the "sidebar" args with some defaults (really only needed for the title).
	 *
	 * @param array $args
	 *
	 * @return array
	 * @since 1.7.0
	 *
	 */
	private function update_args( $args ) {
		$defaults = array(
			'before_title' => '<h3 class="display-featured-image-genesis__title">',
			'after_title'  => '</h3>',
		);

		return wp_parse_args( $args, $defaults );
	}
}
