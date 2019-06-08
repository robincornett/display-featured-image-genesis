<?php

/**
 * Class Display_Featured_Image_Genesis_Author_Widget
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Author_Widget extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'author-profile',
			'description'                 => __( 'Displays user profile block with Gravatar', 'display-featured-image-genesis' ),
			'customize_selective_refresh' => true,
		);

		$control_ops = array(
			'id_base' => 'featured-author',
			'width'   => 200,
			'height'  => 250,
		);

		parent::__construct( 'featured-author', __( 'Display Featured Author Profile', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

	}

	/**
	 * Get the widget defaults.
	 *
	 * @return array
	 */
	public function defaults() {
		return array(
			'title'                    => '',
			'show_featured_image'      => 0,
			'featured_image_alignment' => 'alignnone',
			'featured_image_size'      => 'medium',
			'gravatar_alignment'       => 'left',
			'user'                     => '',
			'show_gravatar'            => 0,
			'size'                     => 45,
			'author_info'              => '',
			'bio_text'                 => '',
			'page'                     => '',
			'page_link_text'           => __( 'Read More', 'display-featured-image-genesis' ) . '&#x02026;',
			'posts_link'               => 0,
			'link_text'                => __( 'View My Blog Posts', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		echo $args['before_widget'];

		include plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-author.php';
		new DisplayFeaturedImageGenesisOutputAuthor( $instance, $args, $this->id_base );

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {

		$new_instance['user'] = (int) $new_instance['user'];
		$updater              = new DisplayFeaturedImageGenesisWidgetsUpdate();

		return $updater->update( $new_instance, $old_instance, $this->get_fields( $new_instance ) );
	}

	/**
	 * Get all widget fields.
	 *
	 * @param $new_instance
	 *
	 * @return array
	 */
	public function get_fields( $new_instance ) {
		$form = new DisplayFeaturedImageGenesisWidgetsForm( $this, $new_instance );

		return array_merge(
			include 'fields/author-image.php',
			include 'fields/author-gravatar.php',
			include 'fields/author-description.php',
			include 'fields/author-archive.php'
		);
	}

	/**
	 * Echo the settings update form.
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
		$form->do_select( $instance, array(
			'id'      => 'user',
			'label'   => __( 'Select a user. The email address for this account will be used to pull the Gravatar image.', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $this->get_users(),
		) );

		$form->do_boxes( array(
			'author' => include 'fields/author-image.php',
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'gravatar' => include 'fields/author-gravatar.php',
		) );

		$form->do_boxes( array(
			'description' => include 'fields/author-description.php',
		) );

		$form->do_boxes( array(
			'archive' => include 'fields/author-archive.php',
		) );
	}

	/**
	 * Get the authors on the site.
	 *
	 * @return array
	 */
	protected function get_users() {
		$users   = get_users( array(
			'who' => 'authors',
		) );
		$options = array();
		foreach ( $users as $user ) {
			$options[ $user->ID ] = $user->data->display_name;
		}

		return $options;
	}

	/**
	 * Get gravatar sizes.
	 *
	 * @return array
	 */
	protected function get_gravatar_sizes() {
		$sizes   = apply_filters( 'genesis_gravatar_sizes', array(
			__( 'Small', 'display-featured-image-genesis' )       => 45,
			__( 'Medium', 'display-featured-image-genesis' )      => 65,
			__( 'Large', 'display-featured-image-genesis' )       => 85,
			__( 'Extra Large', 'display-featured-image-genesis' ) => 125,
		) );
		$options = array();
		foreach ( (array) $sizes as $label => $size ) {
			$options[ $size ] = $label;
		}

		return $options;
	}

	/**
	 * Get the pages on the site.
	 *
	 * @return array
	 */
	protected function get_pages() {
		$page_ids = get_pages( array(
			'post_type' => 'page',
		) );
		$pages    = array();
		if ( $page_ids ) {
			$pages[] = __( 'None', 'display-featured-image-genesis' );
			foreach ( $page_ids as $id ) {
				$title            = empty( $id->post_title ) ? '#' . $id->ID . __( ' (no title)', 'sixtenpress-featured-content' ) : $id->post_title;
				$pages[ $id->ID ] = $title;
			}
		}

		return $pages;
	}
}
