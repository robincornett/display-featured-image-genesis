<?php
/**
 * Dependent class to build a featured taxonomy widget
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 * @license   GPL-2.0+
 * @since 2.0.0
 */

/**
 * Genesis Featured Taxonomy widget class.
 *
 * @since 2.0.0
 *
 */
class Display_Featured_Image_Genesis_Widget_CPT extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->defaults = array(
			'title'                   => '',
			'post_type'               => 'post',
			'show_image'              => 0,
			'image_alignment'         => '',
			'image_size'              => 'medium',
			'show_title'              => 0,
			'show_content'            => 0,
		);

		$widget_ops = array(
			'classname'                   => 'featured-posttype',
			'description'                 => __( 'Displays a post type archive with its featured image', 'display-featured-image-genesis' ),
			'customize_selective_refresh' => true,
		);

		$control_ops = array(
			'id_base' => 'featured-posttype',
			'width'   => 505,
			'height'  => 350,
		);

		parent::__construct( 'featured-posttype', __( 'Display Featured Post Type Archive Image', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

	}

	/**
	 * Echo the widget content.
	 *
	 * @since 2.0.0
	 *
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$post_type = get_post_type_object( $instance['post_type'] );
		if ( ! $post_type ) {
			return;
		}
		$option    = displayfeaturedimagegenesis_get_setting();
		$image_id  = '';

		if ( 'post' === $instance['post_type'] ) {
			$frontpage       = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage       = get_option( 'page_for_posts' );
			$postspage_image = get_post_thumbnail_id( $postspage );
			$title           = get_post( $postspage )->post_title;
			$permalink       = esc_url( get_the_permalink( $postspage ) );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$postspage_image = display_featured_image_genesis_get_default_image_id();
				$title           = get_bloginfo( 'name' );
				$permalink       = home_url();
			}
			$image_id = $postspage_image;
		}
		else {
			$title     = $post_type->label;
			$permalink = esc_url( get_post_type_archive_link( $instance['post_type'] ) );
			if ( post_type_supports( $instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
				$headline = genesis_get_cpt_option( 'headline', $instance['post_type'] );
				if ( ! empty( $headline ) ) {
					$title = $headline;
				}
			}
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		$image = '';
		if ( isset( $option['post_type'][ $post_type->name ] )  ) {
			$image_id = displayfeaturedimagegenesis_check_image_id( $option['post_type'][ $post_type->name ] );
		}
		$image_src = wp_get_attachment_image_src( $image_id, $instance['image_size'] );
		if ( $image_src ) {
			$image = '<img src="' . $image_src[0] . '" alt="' . $title . '" />';
		}

		if ( $instance['show_image'] && $image ) {
			$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
			printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $instance['image_alignment'] ), $role, $image );
		}

		if ( $instance['show_title'] ) {

			if ( ! empty( $instance['show_title'] ) ) {

				$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
				if ( genesis_html5() ) {
					$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
				}
				echo wp_kses_post( $title_output );

			}
		}

		$intro_text = '';
		if ( post_type_supports( $instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$intro_text = genesis_get_cpt_option( 'intro_text', $instance['post_type'] );
		} elseif ( 'post' === $instance['post_type'] ) {
			$intro_text = get_post( $postspage )->post_excerpt;
			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$intro_text = get_bloginfo( 'description' );
			}
		}

		if ( $instance['show_content'] && $intro_text ) {

			echo genesis_html5() ? '<div class="archive-description">' : '';

			$intro_text = apply_filters( 'display_featured_image_genesis_cpt_description', $intro_text );

			echo wp_kses_post( wpautop( $intro_text ) );

			echo genesis_html5() ? '</div>' : '';

		}

		echo $args['after_widget'];

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
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		$new_instance['title']     = strip_tags( $new_instance['title'] );
		$new_instance['post_type'] = esc_attr( $new_instance['post_type'] );
		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $instance Current settings
	 */
	public function form( $instance ) {

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$form     = new DisplayFeaturedImageGenesisWidgets( $this, $instance );

		$form->do_text( $instance, array(
			'id'    => 'title',
			'label' => __( 'Title:', 'display-featured-image-genesis' ),
			'class' => 'widefat',
		) );

		echo '<div class="genesis-widget-column">';

		$form->do_boxes( array(
			'post_type' => array(
				array(
					'method' => 'select',
					'args'   => array(
						'id'      => 'post_type',
						'label'   => __( 'Post Type:', 'display-featured-image-genesis' ),
						'flex'    => true,
						'choices' => $this->get_post_types(),
					),
				),
			),
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'text' => array(
				array(
					'method' => 'checkbox',
					'args'   => array(
						'id'    => 'show_title',
						'label' => __( 'Show Archive Title', 'display-featured-image-genesis' ),
					),
				),
				array(
					'method' => 'checkbox',
					'args'   => array(
						'id'    => 'show_content',
						'label' => __( 'Show Archive Intro Text', 'display-featured-image-genesis' ),
					),
				),
			),
		) );

		echo '</div>';
		echo '<div class="genesis-widget-column genesis-widget-column-right">';

		$form->do_boxes( array(
			'image' => array(
				array(
					'method' => 'checkbox',
					'args'   => array(
						'id'    => 'show_image',
						'label' => __( 'Show Featured Image', 'display-featured-image-genesis' ),
					),
				),
				array(
					'method' => 'select',
					'args'   => array(
						'id'      => 'image_size',
						'label'   => __( 'Image Size:', 'display-featured-image-genesis' ),
						'flex'    => true,
						'choices' => $form->get_image_size(),
					),
				),
				array(
					'method' => 'select',
					'args'   => array(
						'id'      => 'image_alignment',
						'label'   => __( 'Image Alignment', 'display-featured-image-genesis' ),
						'flex'    => true,
						'choices' => $form->get_image_alignment(),
					),
				),
			),
		), 'genesis-widget-column-box-top' );

		echo '</div>';
	}

	/**
	 * @return mixed
	 */
	protected function get_post_types() {
		$args = array(
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
