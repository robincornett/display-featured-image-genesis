<?php
/**
 * Dependent class to build a featured taxonomy widget
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 * @license   GPL-2.0+
 * @since     2.0.0
 */

/**
 * Genesis Featured Taxonomy widget class.
 *
 * @since 2.0.0
 *
 */
class Display_Featured_Image_Genesis_Widget_CPT extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'featured-posttype',
			'description'                 => __( 'Displays a post type archive with its featured image', 'display-featured-image-genesis' ),
			'customize_selective_refresh' => true,
		);

		$control_ops = array(
			'id_base' => 'featured-posttype',
			'width'   => 200,
			'height'  => 350,
		);

		parent::__construct( 'featured-posttype', __( 'Display Featured Post Type Archive Image', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

	}

	/**
	 * Define the widget defaults.
	 * @return array
	 */
	public function defaults() {
		return array(
			'title'             => '',
			'post_type'         => 'post',
			'show_image'        => 0,
			'image_alignment'   => 'alignnone',
			'image_size'        => 'medium',
			'show_title'        => 0,
			'show_content'      => 0,
			'custom_content'    => '',
			'archive_link'      => 0,
			'archive_link_text' => __( 'View Content Type Archive', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Echo the widget content.
	 *
	 * @since 2.0.0
	 *
	 *
	 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$post_type = get_post_type_object( $instance['post_type'] );
		if ( ! $post_type ) {
			return;
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		$permalink = $this->get_permalink( $instance );
		$title     = $this->get_title( $instance, $post_type );

		$this->do_image( $instance, $post_type, $title, $permalink );
		$this->do_title( $instance, $permalink, $title );
		$this->do_intro_text( $instance );
		$this->do_archive_link( $permalink, $instance );

		echo $args['after_widget'];
	}

	/**
	 * @param $instance
	 * @param $post_type
	 *
	 * @return string
	 */
	protected function get_title( $instance, $post_type ) {
		$title = $post_type->label;
		if ( 'post' === $instance['post_type'] ) {
			$frontpage = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage = get_option( 'page_for_posts' );
			$title     = get_post( $postspage )->post_title;

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$title = get_bloginfo( 'name' );
			}
		} elseif ( post_type_supports( $instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$headline = genesis_get_cpt_option( 'headline', $instance['post_type'] );
			if ( ! empty( $headline ) ) {
				$title = $headline;
			}
		}

		return $title;
	}

	/**
	 * Get the link for the post type archive.
	 *
	 * @param $instance
	 *
	 * @return mixed|string
	 */
	protected function get_permalink( $instance ) {
		if ( 'post' === $instance['post_type'] ) {
			$frontpage = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage = get_option( 'page_for_posts' );
			$permalink = get_the_permalink( $postspage );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$permalink = home_url();
			}
		} else {
			$permalink = get_post_type_archive_link( $instance['post_type'] );
		}

		return esc_url( $permalink );
	}

	/**
	 * Print out the image for the widget.
	 *
	 * @param $instance
	 * @param $post_type
	 * @param $title
	 * @param $permalink
	 */
	protected function do_image( $instance, $post_type, $title, $permalink ) {
		if ( ! $instance['show_image'] ) {
			return;
		}
		$image = $this->get_image( $instance, $post_type, $title );
		if ( $image ) {
			$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
			printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $instance['image_alignment'] ), $role, $image );
		}
	}

	/**
	 * Get the image.
	 *
	 * @param $instance
	 * @param $post_type
	 * @param $title
	 *
	 * @return string
	 */
	protected function get_image( $instance, $post_type, $title ) {
		$image_id = $this->get_image_id( $instance, $post_type );

		return wp_get_attachment_image( $image_id, $instance['image_size'], false, array(
			'alt' => $title,
		) );
	}

	/**
	 * Get the image ID.
	 *
	 * @param $instance
	 * @param $post_type
	 *
	 * @return int|string
	 */
	protected function get_image_id( $instance, $post_type ) {
		$image_id = '';
		$option   = displayfeaturedimagegenesis_get_setting();
		if ( 'post' === $instance['post_type'] ) {
			$frontpage       = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage       = get_option( 'page_for_posts' );
			$postspage_image = get_post_thumbnail_id( $postspage );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$postspage_image = display_featured_image_genesis_get_default_image_id();
			}
			$image_id = $postspage_image;
		} elseif ( isset( $option['post_type'][ $post_type->name ] ) && $option['post_type'][ $post_type->name ] ) {
			$image_id = displayfeaturedimagegenesis_check_image_id( $option['post_type'][ $post_type->name ] );
		}

		return $image_id;
	}

	/**
	 * Print the title with markup.
	 *
	 * @param $instance
	 * @param $permalink
	 * @param $title
	 */
	protected function do_title( $instance, $permalink, $title ) {
		if ( ! $instance['show_title'] ) {
			return;
		}

		$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
		if ( genesis_html5() ) {
			$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
		}
		echo wp_kses_post( $title_output );
	}

	/**
	 * Print the intro text.
	 *
	 * @param $instance
	 */
	protected function do_intro_text( $instance ) {
		$intro_text = $this->get_intro_text( $instance );
		if ( ! $instance['show_content'] || ! $intro_text ) {
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
	 * @param $instance
	 *
	 * @return string
	 */
	protected function get_intro_text( $instance ) {
		$intro_text = '';
		if ( post_type_supports( $instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$intro_text = genesis_get_cpt_option( 'intro_text', $instance['post_type'] );
		} elseif ( 'post' === $instance['post_type'] ) {
			$frontpage  = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage  = get_option( 'page_for_posts' );
			$intro_text = get_post( $postspage )->post_excerpt;
			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$intro_text = get_bloginfo( 'description' );
			}
		}

		if ( 'custom' === $instance['show_content'] ) {
			$intro_text = $instance['custom_content'];
		}

		return $intro_text;
	}

	/**
	 * Echo the CPT archive link.
	 *
	 * @param $permalink
	 * @param $instance
	 */
	protected function do_archive_link( $permalink, $instance ) {
		if ( ! $instance['archive_link'] || ! $instance['archive_link_text'] ) {
			return;
		}
		printf( '<p class="more-from-category"><a href="%s">%s</a></p>',
			esc_url( $permalink ),
			esc_html( $instance['archive_link_text'] )
		);
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 2.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {

		$updater = new DisplayFeaturedImageGenesisWidgetsUpdate();

		return $updater->update( $new_instance, $old_instance, $this->get_fields( $new_instance ) );

	}

	/**
	 * Get all widget fields.
	 *
	 * @param array $instance
	 *
	 * @return array
	 */
	public function get_fields( $instance = array() ) {
		$form = new DisplayFeaturedImageGenesisWidgetsForm( $this, $instance );

		return array_merge(
			include 'fields/cpt-post_type.php',
			include 'fields/text.php',
			include 'fields/image.php',
			include 'fields/archive.php'
		);
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$form     = new DisplayFeaturedImageGenesisWidgetsForm( $this, $instance );

		$form->do_text( $instance, array(
			'id'    => 'title',
			'label' => __( 'Title:', 'display-featured-image-genesis' ),
			'class' => 'widefat',
		) );

		$form->do_boxes( array(
			'post_type' => include 'fields/cpt-post_type.php',
		), 'genesis-widget-column-box-top' );

		$label = __( 'Archive', 'display-featured-image-genesis' );
		$form->do_boxes( array(
			'words' => include 'fields/text.php',
		) );

		$form->do_boxes( array(
			'image' => include 'fields/image.php',
		) );

		$form->do_boxes( array(
			'archive' => include 'fields/archive.php',
		) );
	}

	/**
	 * Get the public registered post types on the site.
	 *
	 * @return mixed
	 */
	protected function get_post_types() {
		$args       = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output     = 'objects';
		$post_types = get_post_types( $args, $output );

		$options['post'] = __( 'Posts', 'display-featured-image-genesis' );
		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->label;
		}

		return $options;
	}
}
