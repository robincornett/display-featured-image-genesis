<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2019 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Output {

	/**
	 * @var Display_Featured_Image_Genesis_Description $description
	 */
	protected $description;

	/**
	 * @var
	 */
	protected $setting;

	/**
	 * @var
	 */
	protected $item;

	/**
	 * set parameters for scripts, etc. to run.
	 *
	 * @since 1.1.3
	 */
	public function manage_output() {

		if ( $this->quit_now() ) {
			return;
		}
		add_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	/**
	 * enqueue plugin styles and scripts.
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {

		if ( ! $this->can_do_things() ) {
			return;
		}

		include_once 'class-displayfeaturedimagegenesis-enqueue.php';
		$enqueue = new DisplayFeaturedImageGenesisEnqueue( $this->get_setting(), $this->get_item() );
		$enqueue->enqueue_style();
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

		$large = $this->get_minimum_backstretch_width();
		$item  = $this->get_item();
		$width = absint( $item->backstretch[1] );
		/**
		 * Creates display_featured_image_genesis_force_backstretch filter to check
		 * whether get_post_type array should force the backstretch effect for this post type.
		 * @uses is_in_array()
		 */
		if ( $width > $large || displayfeaturedimagegenesis_get()->is_in_array( 'force_backstretch' ) ) {
			$scriptless = displayfeaturedimagegenesis_get_setting( 'scriptless' );
			if ( ! $scriptless ) {
				$enqueue->enqueue_scripts();
			}
			$this->launch_backstretch_image();
		} else {
			$this->do_large_image_things();
		}
	}

	/**
	 * set body class if featured images are displayed using the plugin
	 *
	 * @param $classes array
	 *
	 * @return array
	 *
	 * @since  1.0.0
	 */
	public function add_body_class( $classes ) {
		if ( ! $this->can_do_things() ) {
			return $classes;
		}
		$large = $this->get_minimum_backstretch_width();
		$item  = $this->get_item();
		$width = (int) $item->backstretch[1];
		if ( false === $item->content || ! is_singular() ) {
			if ( $width > $large ) {
				$classes[] = 'has-leader';
			} elseif ( $width <= $large ) {
				$classes[] = 'large-featured';
			}
		}

		return apply_filters( 'display_featured_image_genesis_classes', $classes );
	}

	/**
	 * Actually output the backstretch/banner image and title markup at the designated hook.
	 * @since 3.1.0
	 */
	protected function launch_backstretch_image() {
		$location = $this->get_hooks();
		add_action( esc_attr( $location['backstretch']['hook'] ), array( $this, 'do_backstretch_image_title' ), $location['backstretch']['priority'] );
	}

	/**
	 * backstretch image title ( for images which are larger than Media Settings > Large )
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image_title() {
		$description = $this->get_description_class();
		if ( $this->move_title() ) {
			$description->remove_title_descriptions();
		}

		$class      = 'big-leader';
		$scriptless = displayfeaturedimagegenesis_get_setting( 'scriptless' );
		if ( $scriptless ) {
			$class .= ' big-leader--scriptless';
		}
		echo '<div class="' . esc_attr( $class ) . '">';
		$image = $this->get_banner_image();
		if ( $scriptless ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $image;
		}
		echo '<div class="wrap">';

		do_action( 'display_featured_image_genesis_before_title' );

		if ( $this->move_title() ) {
			if ( $this->move_excerpts() ) {
				$description->do_title_descriptions();
			} else {
				$item = $this->get_item();
				if ( ! empty( $item->title ) && $this->do_the_title() ) {
					echo $this->do_the_title();
				}
				add_action( 'genesis_before_loop', array( $description, 'add_descriptions' ) );
			}
		}

		do_action( 'display_featured_image_genesis_after_title' );

		// close wrap
		echo '</div>';

		if ( ! $scriptless ) {
			printf( '<noscript><div class="backstretch no-js">%s</div></noscript>', $image );
		}

		// close big-leader
		echo '</div>';

		add_filter( 'jetpack_photon_override_image_downsize', '__return_false' );
	}

	/**
	 * Get the banner/noscript image.
	 * @since 3.1.0
	 *
	 * @return string
	 */
	protected function get_banner_image() {
		$image_id = displayfeaturedimagegenesis_get()->set_image_id();
		return wp_get_attachment_image(
			$image_id,
			displayfeaturedimagegenesis_get()->banner_image_size(),
			false,
			array(
				'alt'         => $this->get_image_alt_text( $image_id ),
				'class'       => 'big-leader__image post-image',
				'aria-hidden' => 'true',
			)
		);
	}

	/**
	 * Get the alt text for the featured image. Use the image alt text if filter is true.
	 *
	 * @param string $image_id
	 *
	 * @return mixed
	 */
	protected function get_image_alt_text( $image_id = '' ) {
		$item     = $this->get_item();
		$alt_text = $item->title;
		if ( $image_id && apply_filters( 'displayfeaturedimagegenesis_prefer_image_alt', false ) ) {
			$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( $image_alt ) {
				$alt_text = $image_alt;
			}
		}

		return $alt_text;
	}

	/**
	 * All actions required to output the large image
	 * @since 2.3.4
	 */
	protected function do_large_image_things() {
		remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
		add_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description', 15 );

		$location = $this->get_hooks();
		add_action( esc_attr( $location['large']['hook'] ), array( $this, 'do_large_image' ), $location['large']['priority'] ); // works for both HTML5 and XHTML
	}

	/**
	 * Large image, centered above content
	 *
	 * @since  1.0.0
	 */
	public function do_large_image() {
		$image_id      = displayfeaturedimagegenesis_get()->set_image_id();
		$attr['class'] = 'aligncenter featured';
		$attr['alt']   = $this->get_image_alt_text( $image_id );
		$image_size    = apply_filters( 'display_featured_image_large_image_size', displayfeaturedimagegenesis_get()->image_size() );
		$image         = wp_get_attachment_image( $image_id, $image_size, false, $attr );
		$image         = apply_filters( 'display_featured_image_genesis_large_image_output', $image, $image_id );
		echo wp_kses_post( $image );
	}

	/**
	 * Return the title.
	 * @return string title with markup.
	 *
	 * @since 2.3.1
	 */
	protected function do_the_title() {
		$description = $this->get_description_class();
		if ( is_front_page() && ! $description->show_front_page_title() ) {
			return '';
		}
		$class        = is_singular() ? 'entry-title' : 'archive-title';
		$itemprop     = genesis_html5() ? 'itemprop="headline"' : '';
		$item         = $this->get_item();
		$title        = $item->title;
		$title_output = sprintf( '<h1 class="%s featured-image-overlay" %s>%s</h1>', $class, $itemprop, $title );

		return apply_filters( 'display_featured_image_genesis_modify_title_overlay', $title_output, esc_attr( $class ), esc_attr( $itemprop ), $title );
	}

	/**
	 * Check plugin settings/filters to see if the featured image should output on this post/etc. at all.
	 * Returns true to quit now; false to carry on.
	 * @return bool
	 */
	protected function quit_now() {
		$setting       = $this->get_setting();
		$disable       = false;
		$exclude_front = is_front_page() && $setting['exclude_front'];
		$post_type     = get_post_type();
		$skip_singular = is_singular() && isset( $setting['skip'][ $post_type ] ) && $setting['skip'][ $post_type ] ? true : false;
		$post_id       = displayfeaturedimagegenesis_get()->get_post_id();
		$post_meta     = 1 === (int) get_post_meta( $post_id, '_displayfeaturedimagegenesis_disable', true );

		if ( $this->get_skipped_posttypes() || $skip_singular || $exclude_front || $post_meta ) {
			$disable = true;
		}

		/**
		 * Allow users to decide to quite early conditionally, outside of specific post types.
		 * @since 2.6.1
		 */
		return apply_filters( 'displayfeaturedimagegenesis_disable', $disable );
	}

	/**
	 * Creates display_featured_image_genesis_skipped_posttypes filter to check
	 * whether get_post_type array should not run plugin on this post type.
	 * @uses is_in_array()
	 */
	protected function get_skipped_posttypes() {
		$post_types = array( 'attachment', 'revision', 'nav_menu_item' );

		return displayfeaturedimagegenesis_get()->is_in_array( 'skipped_posttypes', $post_types );
	}

	/**
	 * Check whether plugin can output backstretch or large image
	 * @return boolean checks featured image size. returns true if can proceed; false if cannot
	 *
	 * @since 2.3.4
	 */
	public function can_do_things() {
		$can_do = true;
		$item   = $this->get_item();
		$medium = (int) apply_filters( 'displayfeaturedimagegenesis_set_medium_width', get_option( 'medium_size_w' ) );
		$width  = (int) $item->backstretch[1];

		// check if they have enabled display on subsequent pages
		$is_paged = $this->get_setting( 'is_paged' );
		// if there is no backstretch image set, or it is too small, or the image is in the content, or it's page 2+ and they didn't change the setting, die
		if ( empty( $item->backstretch ) || $width <= $medium || ( is_paged() && ! $is_paged ) || ( is_singular() && false !== $item->content ) ) {
			$can_do = false;
		}

		return apply_filters( 'displayfeaturedimagegenesis_can_do', $can_do );
	}

	/**
	 * Create a filter to not move excerpts if move excerpts is enabled.
	 * @return bool
	 * @since  2.0.0 (deprecated old function from 1.3.3)
	 */
	protected function move_excerpts() {
		$move_excerpts = $this->get_setting( 'move_excerpts' );
		/**
		 * Creates display_featured_image_genesis_omit_excerpt filter to check
		 * whether get_post_type array should not move excerpts for this post type.
		 * @uses is_in_array()
		 */
		if ( $move_excerpts && ! displayfeaturedimagegenesis_get()->is_in_array( 'omit_excerpt' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * filter to maybe move titles, or not
	 * @return bool
	 * @since 2.2.0
	 */
	protected function move_title() {
		$keep_titles = $this->get_setting( 'keep_titles' );
		/**
		 * Creates display_featured_image_genesis_do_not_move_titles filter to check
		 * whether get_post_type array should not move titles to overlay the featured image.
		 * @uses is_in_array()
		 */
		if ( $keep_titles || displayfeaturedimagegenesis_get()->is_in_array( 'do_not_move_titles' ) || $this->check_post_meta( '_displayfeaturedimagegenesis_move' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * If there is no image to use for the post thumbnail in archives,
	 * optionally use the term or post type image as a fallback instead.
	 *
	 * @param $defaults
	 *
	 * @return mixed
	 * @since 2.5.0
	 */
	public function change_thumbnail_fallback( $defaults ) {
		if ( ! $this->get_setting( 'thumbnails' ) ) {
			return $defaults;
		}
		remove_action( 'genesis_entry_content', 'display_featured_image_genesis_add_archive_thumbnails', 5 );
		$args            = array(
			'post_mime_type' => 'image',
			'post_parent'    => get_the_ID(),
			'post_type'      => 'attachment',
		);
		$attached_images = get_children( $args );
		if ( $attached_images ) {
			return $defaults;
		}
		$image_id = display_featured_image_genesis_get_term_image_id();
		if ( empty( $image_id ) ) {
			$image_id = display_featured_image_genesis_get_cpt_image_id();
		}
		if ( $image_id ) {
			$defaults['fallback'] = $image_id;
		}

		return $defaults;
	}

	/**
	 * Instantiate the description class as needed.
	 * @return \Display_Featured_Image_Genesis_Description
	 * @since 3.1.0
	 */
	protected function get_description_class() {
		if ( isset( $this->description ) ) {
			return $this->description;
		}
		include_once 'class-displayfeaturedimagegenesis-description.php';
		$this->description = new Display_Featured_Image_Genesis_Description();

		return $this->description;
	}

	/**
	 * Get the current featured image and related variables.
	 * @return \stdClass
	 */
	protected function get_item() {
		if ( isset( $this->item ) ) {
			return $this->item;
		}
		$this->item = displayfeaturedimagegenesis_get()->get_image_variables();

		return $this->item;
	}

	/**
	 * Get the minimum acceptable backstretch width.
	 *
	 * @return int
	 */
	protected function get_minimum_backstretch_width() {
		return displayfeaturedimagegenesis_get()->minimum_backstretch_width();
	}

	/**
	 * Define the hooks/priorities for image output.
	 *
	 * @return array
	 */
	protected function get_hooks() {
		$setting    = displayfeaturedimagegenesis_get_setting();
		$large_hook = apply_filters( 'display_featured_image_genesis_move_large_image', $setting['large_hook'] );
		if ( ! is_singular() || is_page_template( 'page_blog.php' ) ) {
			$check = strpos( $large_hook, 'entry' ) || strpos( $large_hook, 'post' );
			if ( false !== $check ) {
				$large_hook = 'genesis_before_loop';
			}
		}

		return apply_filters( 'displayfeaturedimagegenesis_hooks', array(
			'backstretch' => array(
				'hook'     => apply_filters( 'display_featured_image_move_backstretch_image', $setting['backstretch_hook'] ),
				'priority' => apply_filters( 'display_featured_image_move_backstretch_image_priority', $setting['backstretch_priority'] ),
			),
			'large'       => array(
				'hook'     => $large_hook,
				'priority' => apply_filters( 'display_featured_image_genesis_move_large_image_priority', $setting['large_priority'] ),
			),
		) );
	}

	/**
	 * Check the post_meta for singular posts/pages/posts page.
	 *
	 * @param $meta_key string the post_meta key to check
	 *
	 * @return bool
	 * @since 2.5.2
	 */
	protected function check_post_meta( $meta_key ) {
		$post_id   = get_option( 'page_for_posts' ) && is_home() ? get_option( 'page_for_posts' ) : get_the_ID();
		$post_meta = (bool) get_post_meta( $post_id, $meta_key, true );

		return (bool) ( is_home() || is_singular() ) && $post_meta;
	}

	/**
	 * Get the plugin setting.
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function get_setting( $key = '' ) {
		if ( isset( $this->setting ) ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}

		$this->setting = displayfeaturedimagegenesis_get_setting();

		return $key ? $this->setting[ $key ] : $this->setting;
	}

	/**
	 * Remove Genesis titles/descriptions
	 * Deprecated in 2.4.0 as function was moved to the descriptions class instead.
	 *
	 * @since 2.3.1
	 */
	public function remove_title_descriptions() {
		$description = $this->get_description_class();
		_deprecated_function( __FUNCTION__, '3.1.0', '$description->remove_title_descriptions' );
		$description->remove_title_descriptions();
	}

	/**
	 * Do title and description together (for excerpt output)
	 * Deprecated in 2.4.0 as function was moved to the descriptions class instead.
	 *
	 * @since 2.3.1
	 */
	public function do_title_descriptions() {
		$description = $this->get_description_class();
		_deprecated_function( __FUNCTION__, '3.1.0', '$description->do_title_descriptions' );
		$description->do_title_descriptions();
	}

	/**
	 * Separate archive titles from descriptions. Titles show in leader image
	 * area; descriptions show before loop.
	 * Deprecated in 2.4.0 as function was moved to the descriptions class instead.
	 *
	 * @since  1.3.0
	 */
	public function add_descriptions() {
		$description = $this->get_description_class();
		_deprecated_function( __FUNCTION__, '3.1.0', '$description->add_descriptions' );
		$description->add_descriptions();
	}
}
