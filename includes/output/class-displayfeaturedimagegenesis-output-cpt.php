<?php

/**
 * Class DisplayFeaturedImageGenesisOutputCPT
 * @since 3.1.0
 */
class DisplayFeaturedImageGenesisOutputCPT {

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
	 * The post type object.
	 * @var object
	 */
	private $post_type;

	/**
	 * The widget ID base.
	 *
	 * @var string
	 */
	private $id_base;

	/**
	 * DisplayFeaturedImageGenesisOutputCPT constructor.
	 *
	 * @param        $instance
	 * @param        $args
	 * @param        $post_type
	 * @param string $id_base
	 */
	public function __construct( $instance, $args, $post_type, $id_base = 'display-featured-image-genesis-cpt' ) {
		$this->instance  = $instance;
		$this->args      = $this->update_args( $args );
		$this->post_type = $post_type;
		$this->id_base   = $id_base;
		$this->init();
	}

	/**
	 * Output the featured post type.
	 * @since 3.1.0
	 */
	private function init() {
		if ( ! empty( $this->instance['title'] ) ) {
			echo wp_kses_post( $this->args['before_title'] . apply_filters( 'widget_title', $this->instance['title'], $this->instance, $this->id_base ) . $this->args['after_title'] );
		}

		$permalink = $this->get_permalink();
		$title     = $this->get_title();

		$this->do_image( $title, $permalink );
		$this->do_title( $permalink, $title );
		$this->do_intro_text();
		$this->do_archive_link( $permalink );
	}

	/**
	 *
	 * @return string
	 */
	private function get_title() {
		$title = $this->post_type->label;
		if ( 'post' === $this->instance['post_type'] ) {
			$frontpage = get_option( 'show_on_front' );
			$postspage = get_option( 'page_for_posts' );
			$title     = get_post( $postspage )->post_title;

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$title = get_bloginfo( 'name' );
			}
		} elseif ( post_type_supports( $this->instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$headline = genesis_get_cpt_option( 'headline', $this->instance['post_type'] );
			if ( ! empty( $headline ) ) {
				$title = $headline;
			}
		}

		return $title;
	}

	/**
	 * Get the link for the post type archive.
	 *
	 * @return mixed|string
	 */
	private function get_permalink() {
		if ( 'post' === $this->instance['post_type'] ) {
			$frontpage = get_option( 'show_on_front' );
			$postspage = get_option( 'page_for_posts' );
			$permalink = get_the_permalink( $postspage );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$permalink = home_url();
			}
		} else {
			$permalink = get_post_type_archive_link( $this->instance['post_type'] );
		}

		return esc_url( $permalink );
	}

	/**
	 * Print out the image for the widget.
	 *
	 * @param $title
	 * @param $permalink
	 */
	private function do_image( $title, $permalink ) {
		if ( ! $this->instance['show_image'] ) {
			return;
		}
		$image = $this->get_image( $title );
		if ( $image ) {
			$role = empty( $this->instance['show_title'] ) ? '' : 'aria-hidden="true" tabindex="-1"';
			printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $this->instance['image_alignment'] ), $role, $image );
		}
	}

	/**
	 * Get the image.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	private function get_image( $title ) {
		$image_id = $this->get_image_id();

		return wp_get_attachment_image(
			$image_id,
			$this->instance['image_size'],
			false,
			array(
				'alt' => $title,
			)
		);
	}

	/**
	 * Get the image ID.
	 *
	 * @return int|string
	 */
	private function get_image_id() {
		$image_id = '';
		$option   = displayfeaturedimagegenesis_get_setting();
		if ( 'post' === $this->instance['post_type'] ) {
			$frontpage       = get_option( 'show_on_front' );
			$postspage       = get_option( 'page_for_posts' );
			$postspage_image = get_post_thumbnail_id( $postspage );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$postspage_image = display_featured_image_genesis_get_default_image_id();
			}
			$image_id = $postspage_image;
		} elseif ( isset( $option['post_type'][ $this->post_type->name ] ) && $option['post_type'][ $this->post_type->name ] ) {
			$image_id = $option['post_type'][ $this->post_type->name ];
		}

		return $image_id;
	}

	/**
	 * Print the title with markup.
	 *
	 * @param $permalink
	 * @param $title
	 */
	private function do_title( $permalink, $title ) {
		if ( ! $this->instance['show_title'] ) {
			return;
		}

		$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
		if ( ! genesis_html5() ) {
			$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
		}
		echo wp_kses_post( $title_output );
	}

	/**
	 * Print the intro text.
	 */
	private function do_intro_text() {
		$intro_text = $this->get_intro_text();
		if ( ! $this->instance['show_content'] || ! $intro_text ) {
			return;
		}
		echo genesis_html5() ? '<div class="archive-description">' : '';
		$intro_text = apply_filters( 'display_featured_image_genesis_cpt_description', $intro_text );
		echo wp_kses_post( wpautop( $intro_text ) );
		echo genesis_html5() ? '</div>' : '';
	}

	/**
	 * Get the intro text.
	 *
	 * @return string
	 */
	private function get_intro_text() {
		$intro_text = '';
		if ( post_type_supports( $this->instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$intro_text = genesis_get_cpt_option( 'intro_text', $this->instance['post_type'] );
		} elseif ( 'post' === $this->instance['post_type'] ) {
			$frontpage  = get_option( 'show_on_front' );
			$postspage  = get_option( 'page_for_posts' );
			$intro_text = get_post( $postspage )->post_excerpt;
			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$intro_text = get_bloginfo( 'description' );
			}
		}

		if ( 'custom' === $this->instance['show_content'] ) {
			$intro_text = $this->instance['custom_content'];
		}

		return $intro_text;
	}

	/**
	 * Echo the CPT archive link.
	 *
	 * @param $permalink
	 */
	private function do_archive_link( $permalink ) {
		if ( ! $this->instance['archive_link'] || ! $this->instance['archive_link_text'] ) {
			return;
		}
		printf(
			'<p class="more-from-category"><a href="%s">%s</a></p>',
			esc_url( $permalink ),
			esc_html( $this->instance['archive_link_text'] )
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
