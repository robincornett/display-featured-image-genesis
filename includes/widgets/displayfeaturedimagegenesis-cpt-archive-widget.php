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
		return include 'fields/cpt-defaults.php';
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

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-cpt.php';
		new DisplayFeaturedImageGenesisOutputCPT( $instance, $args, $post_type, $this->id_base );

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

		$form->do_text(
			$instance,
			array(
				'id'    => 'title',
				'label' => __( 'Title:', 'display-featured-image-genesis' ),
				'class' => 'widefat',
			)
		);

		$form->do_boxes(
			array(
				'post_type' => include 'fields/cpt-post_type.php',
			),
			'genesis-widget-column-box-top'
		);

		$label = __( 'Archive', 'display-featured-image-genesis' );
		$form->do_boxes(
			array(
				'words' => include 'fields/text.php',
			)
		);

		$form->do_boxes(
			array(
				'image' => include 'fields/image.php',
			)
		);

		$form->do_boxes(
			array(
				'archive' => include 'fields/archive.php',
			)
		);
	}
}
