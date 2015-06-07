<?php

class Display_Featured_Image_Genesis_Author_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function __construct() {

		$this->defaults = array(
			'title'                    => '',
			'show_featured_image'      => 0,
			'featured_image_alignment' => '',
			'featured_image_size'      => 'medium',
			'gravatar_alignment'       => 'left',
			'user'                     => '',
			'show_gravatar'            => 0,
			'size'                     => '45',
			'author_info'              => '',
			'bio_text'                 => '',
			'page'                     => '',
			'page_link_text'           => __( 'Read More', 'display-featured-image-genesis' ) . '&#x02026;',
			'posts_link'               => '',
		);

		$widget_ops = array(
			'classname'   => 'author-profile',
			'description' => __( 'Displays user profile block with Gravatar', 'display-featured-image-genesis' ),
		);

		$control_ops = array(
			'id_base' => 'featured-author',
			'width'   => 200,
			'height'  => 250,
		);

		parent::__construct( 'featured-author', __( 'Display Featured Author Profile', 'display-featured-image-genesis' ), $widget_ops, $control_ops );

	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $args['before_widget'];

			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
			}

			if ( $instance['show_featured_image'] ) {
				$image_id  = get_the_author_meta( 'displayfeaturedimagegenesis', $instance['user'] );
				$image_src = wp_get_attachment_image_src( $image_id, $instance['featured_image_size'] );
				if ( $image_src ) {
					echo '<img src="' . esc_url( $image_src[0] ) . '" alt="' . get_the_author_meta( 'display_name', $instance['user'] ) . '" class="' . $instance['featured_image_alignment'] . '" />';
				}
			}

			$text = '';

			if ( $instance['show_gravatar'] ) {
				if ( ! empty( $instance['gravatar_alignment'] ) ) {
					$text .= '<span class="align' . esc_attr( $instance['gravatar_alignment'] ) . '">';
				}

				$text .= get_avatar( $instance['user'], $instance['size'] );

				if( ! empty( $instance['gravatar_alignment'] ) ) {
					$text .= '</span>';
				}
			}

			$text .= 'text' === $instance['author_info'] ? $instance['bio_text'] : get_the_author_meta( 'description', $instance['user'] );

			$text .= $instance['page'] ? sprintf( ' <a class="pagelink" href="%s">%s</a>', get_page_link( $instance['page'] ), $instance['page_link_text'] ) : '';

			// Echo $text
			echo wpautop( $text );

			// If posts link option checked, add posts link to output
			$display_name = get_the_author_meta( 'display_name', $instance['user'] );
			$user_name = ( ! empty ( $display_name ) && genesis_a11y() ) ? '<span class="screen-reader-text">' . $display_name. ': </span>' : '';

			if ( $instance['posts_link'] ) {
				printf( '<div class="posts_link posts-link"><a href="%s">%s%s</a></div>', get_author_posts_url( $instance['user'] ), $user_name, __( 'View My Blog Posts', 'display-featured-image-genesis' ) );
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
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		$new_instance['title']          = strip_tags( $new_instance['title'] );
		$new_instance['bio_text']       = current_user_can( 'unfiltered_html' ) ? $new_instance['bio_text'] : genesis_formatting_kses( $new_instance['bio_text'] );
		$new_instance['page_link_text'] = strip_tags( $new_instance['page_link_text'] );

		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {

		// Merge with defaults
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'display-featured-image-genesis' ); ?>:</label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'user' ) ); ?>"><?php _e( 'Select a user. The email address for this account will be used to pull the Gravatar image.', 'display-featured-image-genesis' ); ?></label><br />
			<?php wp_dropdown_users( array( 'who' => 'authors', 'name' => $this->get_field_name( 'user' ), 'selected' => $instance['user'] ) ); ?>
		</p>

		<div class="genesis-widget-column-box genesis-widget-column-box-top">
			<p>
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_featured_image' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_featured_image' ) ); ?>" value="1" <?php checked( $instance['show_featured_image'] ); ?>/>
				<label for="<?php echo esc_attr( $this->get_field_name( 'show_featured_image' ) ); ?>"><?php _e( 'Show the user\'s featured image.', 'display-featured-image-genesis' ); ?></label><br />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'featured_image_size' ) ); ?>"><?php _e( 'Image Size:', 'display-featured-image-genesis' ); ?> </label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'featured_image_size' ) ); ?>" class="genesis-image-size-selector" name="<?php echo esc_attr( $this->get_field_name( 'featured_image_size' ) ); ?>">
					<?php
					$sizes = genesis_get_image_sizes();
					foreach ( (array) $sizes as $name => $size ) {
						echo '<option value="' . esc_attr( $name ) . '"' . selected( $name, $instance['featured_image_size'], false ) . '>' . esc_html( $name ) . ' ( ' . absint( $size['width'] ) . 'x' . absint( $size['height'] ) . ' )</option>';
					} ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'featured_image_alignment' ) ); ?>"><?php _e( 'Image Alignment:', 'display-featured-image-genesis' ); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'featured_image_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'featured_image_alignment' ) ); ?>">
					<option value="alignnone">- <?php _e( 'None', 'display-featured-image-genesis' ); ?> -</option>
					<option value="alignleft" <?php selected( 'alignleft', $instance['featured_image_alignment'] ); ?>><?php _e( 'Left', 'display-featured-image-genesis' ); ?></option>
					<option value="alignright" <?php selected( 'alignright', $instance['featured_image_alignment'] ); ?>><?php _e( 'Right', 'display-featured-image-genesis' ); ?></option>
					<option value="aligncenter" <?php selected( 'aligncenter', $instance['featured_image_alignment'] ); ?>><?php _e( 'Center', 'display-featured-image-genesis' ); ?></option>
				</select>
			</p>
		</div>

		<div class="genesis-widget-column-box">
			<p>
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_gravatar' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_gravatar' ) ); ?>" value="1" <?php checked( $instance['show_gravatar'] ); ?>/>
				<label for="<?php echo esc_attr( $this->get_field_name( 'show_gravatar' ) ); ?>"><?php _e( 'Show the user\'s gravatar.', 'display-featured-image-genesis' ); ?></label><br />

				<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>"><?php _e( 'Gravatar Size', 'display-featured-image-genesis' ); ?>:</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>">
					<?php
					$sizes = array( __( 'Small', 'display-featured-image-genesis' ) => 45, __( 'Medium', 'display-featured-image-genesis' ) => 65, __( 'Large', 'display-featured-image-genesis' ) => 85, __( 'Extra Large', 'display-featured-image-genesis' ) => 125 );
					$sizes = apply_filters( 'genesis_gravatar_sizes', $sizes );
					foreach ( (array) $sizes as $label => $size ) { ?>
						<option value="<?php echo absint( $size ); ?>" <?php selected( $size, $instance['size'] ); ?>><?php printf( '%s (%spx)', $label, $size ); ?></option>
					<?php } ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'gravatar_alignment' ) ); ?>"><?php _e( 'Gravatar Alignment', 'display-featured-image-genesis' ); ?>:</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'gravatar_alignment' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'gravatar_alignment' ) ); ?>">
					<option value="">- <?php _e( 'None', 'display-featured-image-genesis' ); ?> -</option>
					<option value="left" <?php selected( 'left', $instance['gravatar_alignment'] ); ?>><?php _e( 'Left', 'display-featured-image-genesis' ); ?></option>
					<option value="right" <?php selected( 'right', $instance['gravatar_alignment'] ); ?>><?php _e( 'Right', 'display-featured-image-genesis' ); ?></option>
				</select>
			</p>
		</div>

		<div class="genesis-widget-column-box">
			<fieldset>
				<legend><?php _e( 'Select which text you would like to use as the author description', 'display-featured-image-genesis' ); ?></legend>
				<p>
					<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'author_info' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'author_info' ) ); ?>_val1" value="bio" <?php checked( $instance['author_info'], 'bio' ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'author_info' ) ); ?>_val1"><?php _e( 'Author Bio', 'display-featured-image-genesis' ); ?></label><br />
					<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'author_info' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'author_info' ) ); ?>_val2" value="text" <?php checked( $instance['author_info'], 'text' ); ?>/>
					<label for="<?php echo esc_attr( $this->get_field_id( 'author_info' ) ); ?>_val2"><?php _e( 'Custom Text (below)', 'display-featured-image-genesis' ); ?></label><br />
					<label for="<?php echo esc_attr( $this->get_field_id( 'bio_text' ) ); ?>" class="screen-reader-text"><?php _e( 'Custom Text Content', 'display-featured-image-genesis' ); ?></label>
					<textarea id="<?php echo esc_attr( $this->get_field_id( 'bio_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bio_text' ) ); ?>" class="widefat" rows="6" cols="4"><?php echo htmlspecialchars( $instance['bio_text'] ); ?></textarea>
				</p>
			</fieldset>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_name( 'page' ) ); ?>"><?php _e( 'Choose your extended "About Me" page from the list below. This will be the page linked to at the end of the about me section.', 'display-featured-image-genesis' ); ?></label><br />
				<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page' ), 'show_option_none' => __( 'None', 'display-featured-image-genesis' ), 'selected' => $instance['page'] ) ); ?>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'page_link_text' ) ); ?>"><?php _e( 'Extended page link text', 'display-featured-image-genesis' ); ?>:</label>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'page_link_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'page_link_text' ) ); ?>" value="<?php echo esc_attr( $instance['page_link_text'] ); ?>" class="widefat" />
			</p>

			<p>
				<input id="<?php echo esc_attr( $this->get_field_id( 'posts_link' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'posts_link' ) ); ?>" value="1" <?php checked( $instance['posts_link'] ); ?>/>
				<label for="<?php echo esc_attr( $this->get_field_id( 'posts_link' ) ); ?>"><?php _e( 'Show Author Archive Link?', 'display-featured-image-genesis' ); ?></label>
			</p>
		</div>
	<?php

	}

}
