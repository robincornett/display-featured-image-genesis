<?php
/**
 * Dependent class to build a featured taxonomy widget
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 * @since     2.0.0
 */

/**
 * Genesis Featured Taxonomy widget class.
 *
 * @since 2.0.0
 *
 */
class Display_Featured_Image_Genesis_Widget_Taxonomy extends WP_Widget {

	/**
	 * @var $form_class \DisplayFeaturedImageGenesisWidgetsForm
	 */
	protected $form_class;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

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
	 * @return array
	 */
	public function defaults() {
		return array(
			'title'             => '',
			'taxonomy'          => 'category',
			'term'              => '',
			'show_image'        => 0,
			'image_alignment'   => '',
			'image_size'        => 'medium',
			'show_title'        => 0,
			'show_content'      => 0,
			'archive_link'      => 0,
			'archive_link_text' => __( 'View Term Archive', 'display-featured-image-genesis' ),
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

		$term_id = $instance['term'];
		$term    = get_term_by( 'id', $term_id, $instance['taxonomy'] );
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

		$this->do_image( $term_id, $title, $permalink, $instance );

		$this->do_title( $title, $permalink, $instance );

		$this->do_content( $term, $instance );

		$this->do_archive_link( $term, $instance );

		echo $args['after_widget'];

	}

	/**
	 * Echo the term image with markup.
	 *
	 * @param $term_id
	 * @param $title
	 * @param $permalink
	 * @param $instance
	 */
	protected function do_image( $term_id, $title, $permalink, $instance ) {
		$term_image = displayfeaturedimagegenesis_get_term_image( $term_id );
		if ( ! $term_image ) {
			return;
		}
		$image = wp_get_attachment_image( $term_image, $instance['image_size'], false, array(
			'alt' => $title,
		) );

		if ( $instance['show_image'] && $image ) {
			$role = empty( $instance['show_title'] ) ? '' : 'aria-hidden="true"';
			printf( '<a href="%s" title="%s" class="%s" %s>%s</a>', esc_url( $permalink ), esc_html( $title ), esc_attr( $instance['image_alignment'] ), $role, wp_kses_post( $image ) );
		}
	}

	/**
	 * Echo the term title with markup.
	 *
	 * @param $title
	 * @param $permalink
	 * @param $instance
	 */
	protected function do_title( $title, $permalink, $instance ) {
		if ( ! $instance['show_title'] ) {
			return;
		}

		if ( ! empty( $instance['show_title'] ) ) {

			$title_output = sprintf( '<h2><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
			if ( genesis_html5() ) {
				$title_output = sprintf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', esc_url( $permalink ), esc_html( $title ) );
			}
			echo wp_kses_post( $title_output );
		}
	}

	/**
	 * Echo the term intro text or description.
	 *
	 * @param $term
	 * @param $instance
	 */
	protected function do_content( $term, $instance ) {
		if ( ! $instance['show_content'] ) {
			return;
		}

		echo genesis_html5() ? '<div class="term-description">' : '';

		$intro_text = displayfeaturedimagegenesis_get_term_meta( $term, 'intro_text' );
		$intro_text = apply_filters( 'display_featured_image_genesis_term_description', $intro_text );
		if ( ! $intro_text ) {
			$intro_text = $term->description;
		}

		echo wp_kses_post( wpautop( $intro_text ) );

		echo genesis_html5() ? '</div>' : '';
	}

	/**
	 * Echo the term archive link.
	 *
	 * @param $term
	 * @param $instance
	 */
	protected function do_archive_link( $term, $instance ) {
		if ( ! $instance['archive_link'] || ! $instance['archive_link_text'] ) {
			return;
		}
		printf( '<p class="more-from-category"><a href="%s">%s</a></p>',
			esc_url( get_term_link( $term ) ),
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
	 * @return array
	 */
	public function get_fields( $instance = array() ) {
		$form = $this->get_form_class( $instance );

		return array_merge(
			$form->get_text_fields(),
			$this->get_taxonomy_fields( $instance ),
			$form->get_image_fields(),
			$form->get_archive_fields()
		);
	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $instance Current settings
	 *
	 * @return string
	 */
	public function form( $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$form     = $this->get_form_class( $instance );

		$form->do_text( $instance, array(
			'id'    => 'title',
			'label' => __( 'Title:', 'display-featured-image-genesis' ),
			'class' => 'widefat',
		) );

		echo '<div class="genesis-widget-column">';

		$form->do_boxes( array(
			'taxonomy' => $this->get_taxonomy_fields( $instance ),
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'words' => $form->get_text_fields(),
		) );

		echo '</div>';
		echo '<div class="genesis-widget-column genesis-widget-column-right">';

		$form->do_boxes( array(
			'image' => $form->get_image_fields(),
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'archive' => $form->get_archive_fields(),
		) );

		echo '</div>';
	}

	/**
	 * Get the plugin widget forms class.
	 *
	 * @param array $instance
	 *
	 * @return \DisplayFeaturedImageGenesisWidgetsForm
	 */
	protected function get_form_class( $instance = array() ) {
		if ( isset( $this->form_class ) ) {
			return $this->form_class;
		}
		$this->form_class = new DisplayFeaturedImageGenesisWidgetsForm( $this, $instance );

		return $this->form_class;
	}

	/**
	 * @param $instance
	 *
	 * @return array
	 */
	protected function get_taxonomy_fields( $instance ) {
		return array(
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
					'id'      => 'term',
					'label'   => __( 'Term:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_term_lists( $instance, false ),
				),
			),
		);
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
		$args        = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		$taxonomy    = $ajax ? sanitize_text_field( $_POST['taxonomy'] ) : $instance['taxonomy'];
		$terms       = get_terms( $taxonomy, $args );
		$options[''] = '--';
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
	public function term_action_callback() {

		$list = $this->get_term_lists( array(), true );

		// And emit it
		echo wp_json_encode( $list );
		die();
	}
}
