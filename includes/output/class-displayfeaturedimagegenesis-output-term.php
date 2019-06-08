<?php

/**
 * Class DisplayFeaturedImageGenesisOutputTerm
 * @since 3.1.0
 */
class DisplayFeaturedImageGenesisOutputTerm {

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
	 * The term object.
	 * @var object
	 */
	private $term;

	/**
	 * The widget ID base.
	 *
	 * @var string
	 */
	private $id_base;

	/**
	 * DisplayFeaturedImageGenesisOutputTerm constructor.
	 *
	 * @param        $instance
	 * @param        $args
	 * @param        $term
	 * @param string $id_base
	 */
	public function __construct( $instance, $args, $term, $id_base = '' ) {
		$this->instance = $instance;
		$this->args     = $this->update_args( $args );
		$this->term     = $term;
		$this->id_base  = $id_base ? $id_base : 'display-featured-image-genesis-term';
		$this->init();
	}

	/**
	 * Output the featured term.
	 * @since 3.1.0
	 */
	private function init() {
		if ( ! empty( $this->instance['title'] ) ) {
			echo wp_kses_post( $this->args['before_title'] . apply_filters( 'widget_title', $this->instance['title'], $this->instance, $this->id_base ) . $this->args['after_title'] );
		}

		$title = displayfeaturedimagegenesis_get_term_meta( $this->term, 'headline' );
		if ( ! $title ) {
			$title = $this->term->name;
		}
		$permalink = get_term_link( $this->term );

		$this->do_image( $title, $permalink );

		$this->do_title( $title, $permalink );

		$this->do_content();

		$this->do_archive_link();
	}

	/**
	 * Echo the term image with markup.
	 *
	 * @param $term_id
	 * @param $title
	 * @param $permalink
	 */
	private function do_image( $title, $permalink ) {
		$term_image = displayfeaturedimagegenesis_get_term_image( $this->instance['term'] );
		if ( ! $term_image ) {
			return;
		}
		$image = wp_get_attachment_image(
			$term_image,
			$this->instance['image_size'],
			false,
			array(
				'alt' => $title,
			)
		);

		if ( $this->instance['show_image'] && $image ) {
			$role = empty( $this->instance['show_title'] ) ? '' : 'aria-hidden="true"';
			printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $this->instance['image_alignment'] ), $role, wp_kses_post( $image ) );
		}
	}

	/**
	 * Echo the term title with markup.
	 *
	 * @param $title
	 * @param $permalink
	 */
	private function do_title( $title, $permalink ) {
		if ( ! $this->instance['show_title'] ) {
			return;
		}

		if ( empty( $this->instance['show_title'] ) ) {
			return;
		}
		$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
		if ( ! genesis_html5() ) {
			$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
		}
		echo wp_kses_post( $title_output );
	}

	/**
	 * Echo the term intro text or description.
	 */
	private function do_content() {
		if ( ! $this->instance['show_content'] ) {
			return;
		}

		echo genesis_html5() ? '<div class="term-description">' : '';

		$intro_text = displayfeaturedimagegenesis_get_term_meta( $this->term, 'intro_text' );
		$intro_text = apply_filters( 'display_featured_image_genesis_term_description', $intro_text );
		if ( ! $intro_text ) {
			$intro_text = $this->term->description;
		}

		if ( 'custom' === $this->instance['show_content'] ) {
			$intro_text = $this->instance['custom_content'];
		}

		echo wp_kses_post( wpautop( $intro_text ) );

		echo genesis_html5() ? '</div>' : '';
	}

	/**
	 * Echo the term archive link.
	 */
	private function do_archive_link() {
		if ( ! $this->instance['archive_link'] || ! $this->instance['archive_link_text'] ) {
			return;
		}
		printf(
			'<p class="more-from-category"><a href="%s">%s</a></p>',
			esc_url( get_term_link( $this->term ) ),
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
