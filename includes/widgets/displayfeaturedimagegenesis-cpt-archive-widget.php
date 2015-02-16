<?php
/**
 * Dependent class to build a featured taxonomy widget
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
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
	function __construct() {

		$this->defaults = array(
			'title'                   => '',
			'post_type'               => 'post',
			'show_image'              => 0,
			'image_alignment'         => '',
			'image_size'              => 'medium',
			'show_title'              => 0,
			'show_content'            => 0
		);

		$widget_ops = array(
			'classname'   => 'featured-posttype',
			'description' => __( 'Displays a post type archive with its featured image', 'display-featured-image-genesis' ),
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
	function widget( $args, $instance ) {

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$post_type = get_post_type_object( $instance['post_type'] );
		$option    = get_option( 'displayfeaturedimagegenesis' );

		if ( 'post' === $instance['post_type'] ) {
			$frontpage       = get_option( 'show_on_front' ); // either 'posts' or 'page'
			$postspage       = get_option( 'page_for_posts' );
			$postspage_image = get_post_thumbnail_id( $postspage );
			$title           = get_post( $postspage )->post_title;
			$permalink       = esc_url( get_the_permalink( $postspage ) );

			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$item = Display_Featured_Image_Genesis_Common::get_image_variables();
				$postspage_image = $item->fallback_id;
				$title           = get_bloginfo( 'name');
				$permalink       = home_url();
			}
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
		if ( 'post' === $instance['post_type'] ) {
			$image_id = $postspage_image;
		}
		else {
			$image_id = Display_Featured_Image_Genesis_Common::get_image_id( $option['post_type'][$post_type->name] );
		}
		$image_src = wp_get_attachment_image_src( $image_id, $instance['image_size'] );
		if ( $image_src ) {
			$image = '<img src="' . $image_src[0] . '" title="' . $title . '" />';
		}

		if ( $instance['show_image'] && $image ) {
			printf( '<a href="%s" title="%s" class="%s">%s</a>', $permalink, esc_html( $title ), esc_attr( $instance['image_alignment'] ), $image );
		}

		if ( $instance['show_title'] ) {

			if ( ! empty( $instance['show_title'] ) ) {

				if ( genesis_html5() )
					printf( '<h2 class="archive-title"><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );
				else
					printf( '<h2><a href="%s">%s</a></h2>', $permalink, esc_html( $title ) );

			}

		}

		$intro_text = '';
		if ( post_type_supports( $instance['post_type'], 'genesis-cpt-archives-settings' ) ) {
			$intro_text = genesis_get_cpt_option( 'intro_text', $instance['post_type'] );
		}
		elseif ( 'post' === $instance['post_type'] ) {
			$intro_text = get_post( $postspage )->post_excerpt;
			if ( 'posts' === $frontpage || ( 'page' === $frontpage && ! $postspage ) ) {
				$intro_text = get_bloginfo( 'description' );
			}
		}

		if ( $instance['show_content'] && $intro_text ) {

			echo genesis_html5() ? '<div class="archive-description">' : '';

			$intro_text = apply_filters( 'display_featured_image_genesis_cpt_description', $intro_text );

			echo $intro_text;

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
	function form( $instance ) {

		//* Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'display-featured-image-genesis' ); ?> </label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<div class="genesis-widget-column">

			<div class="genesis-widget-column-box genesis-widget-column-box-top">

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php _e( 'Post Type:', 'display-featured-image-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>" >
					<?php
					//* Fetch a list of possible post types
					$args = array(
						'public'      => true,
						'_builtin'    => false,
						'has_archive' => true
					);
					$output     = 'objects';
					$post_types = get_post_types( $args, $output );

					echo '<option value="post"' . selected( 'post', $instance['post_type'], false ) . '>' . __( 'Posts', 'display-featured-image-genesis' ) . '</option>';
					foreach ( $post_types as $post_type ) {
						echo '<option value="' . esc_attr( $post_type->name ) . '"' . selected( esc_attr( $post_type->name ), $instance['post_type'], false ) . '>' . esc_attr( $post_type->label ) . '</option>';
					} ?>
					</select>
				</p>

			</div>

			<div class="genesis-widget-column-box">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>" value="1" <?php checked( $instance['show_title'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>"><?php _e( 'Show Archive Title', 'display-featured-image-genesis' ); ?></label>
				</p>

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="1" <?php checked( $instance['show_content'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"><?php _e( 'Show Archive Intro Text', 'display-featured-image-genesis' ); ?></label>
				</p>

			</div>

		</div>

		<div class="genesis-widget-column genesis-widget-column-right">

			<div class="genesis-widget-column-box genesis-widget-column-box-top">

				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" value="1" <?php checked( $instance['show_image'] ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>"><?php _e( 'Show Featured Image', 'display-featured-image-genesis' ); ?></label>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>"><?php _e( 'Image Size:', 'display-featured-image-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_size' ) ); ?>" class="genesis-image-size-selector" name="<?php echo esc_attr( $this->get_field_name( 'image_size' ) ); ?>">
						<?php
						$sizes = genesis_get_image_sizes();
						foreach( (array) $sizes as $name => $size ) {
							echo '<option value="' . esc_attr( $name ) . '"' . selected( $name, $instance['image_size'], FALSE ) . '>' . esc_html( $name ) . ' ( ' . absint( $size['width'] ) . 'x' . absint( $size['height'] ) . ' )</option>';
						} ?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>"><?php _e( 'Image Alignment:', 'display-featured-image-genesis' ); ?> </label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'image_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_alignment' ) ); ?>">
						<option value="alignnone">- <?php _e( 'None', 'display-featured-image-genesis' ); ?> -</option>
						<option value="alignleft" <?php selected( 'alignleft', $instance['image_alignment'] ); ?>><?php _e( 'Left', 'display-featured-image-genesis' ); ?></option>
						<option value="alignright" <?php selected( 'alignright', $instance['image_alignment'] ); ?>><?php _e( 'Right', 'display-featured-image-genesis' ); ?></option>
						<option value="aligncenter" <?php selected( 'aligncenter', $instance['image_alignment'] ); ?>><?php _e( 'Center', 'display-featured-image-genesis' ); ?></option>
					</select>
				</p>

			</div>

		</div>
		<?php

	}

}
