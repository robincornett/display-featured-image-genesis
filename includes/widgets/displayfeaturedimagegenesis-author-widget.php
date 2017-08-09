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
	 * @var $form_class \DisplayFeaturedImageGenesisWidgets
	 */
	protected $form_class;

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

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		if ( $instance['show_featured_image'] ) {
			$image_id  = get_the_author_meta( 'displayfeaturedimagegenesis', $instance['user'] );
			$image_src = wp_get_attachment_image_src( $image_id, $instance['featured_image_size'] );
			if ( $image_src ) {
				echo '<img src="' . esc_url( $image_src[0] ) . '" alt="' . esc_html( get_the_author_meta( 'display_name', $instance['user'] ) ) . '" class="' . esc_attr( $instance['featured_image_alignment'] ) . '" />';
			}
		}

		$text = '';

		if ( $instance['show_gravatar'] ) {
			if ( ! empty( $instance['gravatar_alignment'] ) ) {
				$text .= '<span class="align' . esc_attr( $instance['gravatar_alignment'] ) . '">';
			}

			$text .= get_avatar( $instance['user'], $instance['size'] );

			if ( ! empty( $instance['gravatar_alignment'] ) ) {
				$text .= '</span>';
			}
		}

		if ( $instance['author_info'] ) {
			$text .= 'text' === $instance['author_info'] ? $instance['bio_text'] : get_the_author_meta( 'description', $instance['user'] );
		}

		$text .= $instance['page'] ? sprintf( ' <a class="pagelink" href="%s">%s</a>', get_page_link( $instance['page'] ), $instance['page_link_text'] ) : '';

		// Echo $text
		echo wp_kses_post( wpautop( $text ) );

		// If posts link option checked, add posts link to output
		$display_name = get_the_author_meta( 'display_name', $instance['user'] );
		$user_name    = ! empty( $display_name ) && function_exists( 'genesis_a11y' ) && genesis_a11y() ? '<span class="screen-reader-text">' . $display_name . ': </span>' : '';

		if ( $instance['posts_link'] && $instance['link_text'] ) {
			printf( '<div class="posts_link posts-link"><a href="%s">%s%s</a></div>', esc_url( get_author_posts_url( $instance['user'] ) ), wp_kses_post( $user_name ), esc_attr( $instance['link_text'] ) );
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
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {

		$fields  = array_merge(
			$this->get_image_fields(),
			$this->get_gravatar_fields(),
			$this->get_description_fields(),
			$this->get_archive_fields()
		);

		$new_instance['user'] = (int) $new_instance['user'];
		$updater              = new DisplayFeaturedImageGenesisWidgetsUpdate();

		return $updater->update( $new_instance, $old_instance, $fields );

	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
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
		$form->do_select( $instance, array(
			'id'      => 'user',
			'label'   => __( 'Select a user. The email address for this account will be used to pull the Gravatar image.', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $this->get_users(),
		) );

		$form->do_boxes( array(
			'author' => $this->get_image_fields(),
		), 'genesis-widget-column-box-top' );

		$form->do_boxes( array(
			'gravatar' => $this->get_gravatar_fields(),
		) );

		$form->do_boxes( array(
			'description' => $this->get_description_fields(),
		) );

		$form->do_boxes( array(
			'archive' => $this->get_archive_fields(),
		) );
	}

	/**
	 * Get the plugin widget forms class.
	 *
	 * @param array $instance
	 *
	 * @return \DisplayFeaturedImageGenesisWidgets
	 */
	protected function get_form_class( $instance = array() ) {
		if ( isset( $this->form_class ) ) {
			return $this->form_class;
		}
		$this->form_class = new DisplayFeaturedImageGenesisWidgets( $this, $instance );

		return $this->form_class;
	}

	/**
	 * Get the image fields (unique to this widget).
	 *
	 * @return array
	 */
	protected function get_image_fields() {
		$form = $this->get_form_class();
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_featured_image',
					'label' => __( 'Show the user\'s featured image.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'featured_image_size',
					'label'   => __( 'Image Size:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $form->get_image_size(),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'featured_image_alignment',
					'label'   => __( 'Image Alignment:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $form->get_image_alignment(),
				),
			),
		);
	}

	/**
	 * Get the gravatar fields.
	 *
	 * @return array
	 */
	protected function get_gravatar_fields() {
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_gravatar',
					'label' => __( 'Show the user\'s gravatar.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'size',
					'label'   => __( 'Gravatar Size:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_gravatar_sizes(),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'gravatar_alignment',
					'label'   => __( 'Gravatar Alignment:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => array(
						''      => __( 'None', 'display-featured-image-genesis' ),
						'left'  => __( 'Left', 'display-featured-image-genesis' ),
						'right' => __( 'Right', 'display-featured-image-genesis' ),
					),
				),
			),
		);
	}

	/**
	 * Get the author description fields.
	 *
	 * @return array
	 */
	protected function get_description_fields() {
		return array(
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'author_info',
					'label'   => __( 'Text to use as the author description:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => array(
						''     => __( 'None', 'display-featured-image-genesis' ),
						'bio'  => __( 'Author Bio (from profile)', 'display-featured-image-genesis' ),
						'text' => __( 'Custom Text (below)', 'display-featured-image-genesis' ),
					),
				),
			),
			array(
				'method' => 'textarea',
				'args'   => array(
					'id'    => 'bio_text',
					'label' => __( 'Custom Text Content', 'display-featured-image-genesis' ),
					'flex'  => true,
					'rows'  => 6,
				),
			),
		);
	}

	/**
	 * Get the archive fields.
	 *
	 * @return array
	 */
	protected function get_archive_fields() {
		return array(
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'page',
					'label'   => __( 'Choose your extended "About Me" page from the list below. This will be the page linked to at the end of the author description.', 'display-featured-image-genesis' ),
					'choices' => $this->get_pages(),
				),
			),
			array(
				'method' => 'text',
				'args'   => array(
					'id'    => 'page_link_text',
					'label' => __( 'Extended page link text', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'posts_link',
					'label' => __( 'Show Author Archive Link?', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'text',
				'args'   => array(
					'id'    => 'link_text',
					'label' => __( 'Link Text:', 'display-featured-image-genesis' ),
				),
			),
		);
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
