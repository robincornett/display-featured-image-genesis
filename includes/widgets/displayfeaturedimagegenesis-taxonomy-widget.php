<?php
/**
 * Dependent class to build a featured taxonomy widget
 *
 * @package DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 * @since 2.0.0
 */

/**
 * Genesis Featured Taxonomy widget class.
 *
 * @since 2.0.0
 *
 */
class Display_Featured_Image_Genesis_Widget_Taxonomy extends WP_Widget {

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
	function __construct() {

		$this->defaults = array(
			'title'           => '',
			'taxonomy'        => 'category',
			'term'            => 'none',
			'show_image'      => 0,
			'image_alignment' => '',
			'image_size'      => 'medium',
			'show_title'      => 0,
			'show_content'    => 0,
		);

		$widget_ops = array(
			'classname'                   => 'featured-term',
			'description'                 => __( 'Displays a term with its featured image', 'display-featured-image-genesis' ),
			'customize_selective_refresh' => true,
		);

		$control_ops = array(
			'id_base' => 'featured-taxonomy',
			'width'   => 505,
			'height'  => 350,
		);

		parent::__construct( 'featured-taxonomy', __( 'Display Featured Term Image', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

		add_action( 'wp_ajax_widget_selector', array( $this, 'term_action_callback' ) );

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

		$term_id  = $instance['term'];
		$term     = get_term_by( 'id', $term_id, $instance['taxonomy'] );
		if ( ! $term ) {
			return;
		}

		$title = displayfeaturedimagegenesis_get_term_meta( $term, 'headline' );
		if ( ! $title ) {
			$title = $term->name;
		}
		$permalink = get_term_link( $term );

		$args['before_widget'] = str_replace( 'class="widget ', 'class="widget ' . $term->slug . ' ', $args['before_widget'] );
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		$image      = '';
		$term_image = displayfeaturedimagegenesis_get_term_image( $term_id );
		if ( $term_image ) {
			$image_src = wp_get_attachment_image_src( $term_image, $instance['image_size'] );
			if ( $image_src ) {
				$image = '<img src="' . esc_url( $image_src[0] ) . '" alt="' . esc_html( $title ) . '" />';
			}

			if ( $instance['show_image'] && $image ) {
				$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
				printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $instance['image_alignment'] ), $role, wp_kses_post( $image ) );
			}
		}

		if ( $instance['show_title'] ) {

			if ( ! empty( $instance['show_title'] ) ) {

				$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
				if ( genesis_html5() ) {
					$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
				}
				echo wp_kses_post( $title_output );

			}
		}

		if ( $instance['show_content'] ) {

			echo genesis_html5() ? '<div class="term-description">' : '';

			$intro_text = displayfeaturedimagegenesis_get_term_meta( $term, 'intro_text' );
			$intro_text = apply_filters( 'display_featured_image_genesis_term_description', $intro_text );
			if ( ! $intro_text ) {
				$intro_text = $term->description;
			}

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

		$new_instance['title'] = strip_tags( $new_instance['title'] );
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

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$form     = new DisplayFeaturedImageGenesisWidgets( $this, $instance );

		$form->do_text( $instance, array(
			'id'    => 'title',
			'label' => __( 'Title:', 'display-featured-image-genesis' ),
			'class' => 'widefat',
		) );

		echo '<div class="genesis-widget-column">';

			$form->do_boxes( array(
				'taxonomy' => array(
					array(
						'method' => 'select',
						'args'   => array(
							'id'       => 'taxonomy',
							'label'    => __( 'Taxonomy:', 'display-featured-image-genesis' ),
							'flex'     => true,
							'onchange' => sprintf( 'term_postback(\'%s\', this.value );', esc_attr( $this->get_field_id( 'term' ) ) ),
						    'choices'  => $this->get_taxonomies(),
						),
					),
					array(
						'method' => 'select',
						'args'   => array(
							'id'    => 'term',
							'label'   => __( 'Term:', 'display-featured-image-genesis' ),
							'flex'    => true,
							'choices' => $this->get_term_lists( $instance, false ),
						),
					),
				),
			), 'genesis-widget-column-box-top' );

			$form->do_boxes( array(
				'words' => array(
					array(
						'method' => 'checkbox',
						'args'   => array(
							'id'    => 'show_title',
							'label' => __( 'Show Term Title', 'display-featured-image-genesis' ),
						),
					),
					array(
						'method' => 'checkbox',
						'args'   => array(
							'id'    => 'show_content',
							'label' => __( 'Show Term Intro Text', 'display-featured-image-genesis' ),
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
						'id'    => 'image_size',
						'label' => __( 'Image Size:', 'display-featured-image-genesis' ),
						'flex'  => true,
					    'choices' => $form->get_image_size(),
					),
				),
				array(
					'method' => 'select',
					'args'   => array(
						'id'      => 'image_alignment',
						'label'   => __( 'Image Alignment', 'display-featured-image-genesis' ),
						'flex'    => true,
						'choices' => array(
							'alignnone'   => __( 'None', 'display-featured-image-genesis' ),
							'alignleft'   => __( 'Left', 'display-featured-image-genesis' ),
							'alignright'  => __( 'Right', 'display-featured-image-genesis' ),
							'aligncenter' => __( 'Center', 'display-featured-image-genesis' ),
						),
					),
				),
			),
		), 'genesis-widget-column-box-top' );

		echo '</div>';
	}

	/**
	 * @return array
	 */
	protected function get_taxonomies() {
		$args       = array(
			'public'  => true,
			'show_ui' => true,
		);
		$taxonomies = get_taxonomies( $args, 'objects' );
		$options    = array();
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	/**
	 * @param $instance
	 * @param bool $ajax
	 *
	 * @return mixed
	 */
	protected function get_term_lists( $instance, $ajax = false ) {
		$args            = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		$taxonomy        = $ajax ? sanitize_text_field( $_POST['taxonomy'] ) : $instance['taxonomy'];
		$terms           = get_terms( $taxonomy, $args );
		$options['none'] = '--';
		foreach ( $terms as $term ) {
			if ( is_object( $term ) ) {
				$options[ $term->term_id ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Handles the callback to populate the custom term dropdown. The
	 * selected post type is provided in $_POST['taxonomy'], and the
	 * calling script expects a JSON array of term objects.
	 */
	function term_action_callback() {

		$list = $this->get_term_lists( array(), true );

		// And emit it
		echo wp_json_encode( $list );
		die();
	}
}
