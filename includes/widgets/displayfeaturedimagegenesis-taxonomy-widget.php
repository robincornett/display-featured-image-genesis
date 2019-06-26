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
			'width'   => 200,
			'height'  => 350,
		);

		parent::__construct( 'featured-taxonomy', __( 'Display Featured Term Image', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

		$form = new DisplayFeaturedImageGenesisWidgetsForm( $this, array() );
		add_action( 'wp_ajax_widget_selector', array( $form, 'term_action_callback' ) );

	}

	/**
	 * @return array
	 */
	public function defaults() {
		return include 'fields/term-defaults.php';
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
		$term     = get_term_by( 'id', $instance['term'], $instance['taxonomy'] );
		if ( ! $term ) {
			return;
		}
		$args['before_widget'] = str_replace( 'class="widget ', 'class="widget ' . $term->slug . ' ', $args['before_widget'] );
		echo $args['before_widget'];

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-term.php';
		new DisplayFeaturedImageGenesisOutputTerm( $instance, $args, $term, $this->id_base );

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
			include 'fields/text.php',
			include 'fields/term-taxonomy.php',
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
	 *
	 * @return string
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
			'taxonomy' => include 'fields/term-taxonomy.php',
		), 'genesis-widget-column-box-top' );

		$label = __( 'Term', 'display-featured-image-genesis' );
		$form->do_boxes( array(
			'words' => include 'fields/text.php',
		) );

		$form->do_boxes( array(
			'image' => include 'fields/image.php',
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'archive' => include 'fields/archive.php',
		) );
	}
}
