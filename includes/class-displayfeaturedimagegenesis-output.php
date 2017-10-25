<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 * @copyright 2014-2017 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Output {

	/**
	 * @var Display_Featured_Image_Genesis_Common $common
	 */
	protected $common;

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

		$this->setting = displayfeaturedimagegenesis_get_setting();
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
		$css_file = apply_filters( 'display_featured_image_genesis_css_file', plugin_dir_url( __FILE__ ) . 'css/display-featured-image-genesis.css' );
		$common   = $this->get_common_class();
		wp_enqueue_style( 'displayfeaturedimage-style', esc_url( $css_file ), array(), $common->version );
		if ( $this->setting['max_height'] ) {
			$this->add_inline_style();
		}
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

		$large = $common->minimum_backstretch_width();
		$item  = $this->get_item();
		$width = absint( $item->backstretch[1] );
		/**
		 * Creates display_featured_image_genesis_force_backstretch filter to check
		 * whether get_post_type array should force the backstretch effect for this post type.
		 * @uses is_in_array()
		 */
		if ( $width > $large || Display_Featured_Image_Genesis_Common::is_in_array( 'force_backstretch' ) ) {
			$this->do_backstretch_image_things();
		} else {
			$this->do_large_image_things();
		}
	}

	/**
	 * Add max_height to output via inline style.
	 *
	 * @since 2.6.0
	 */
	public function add_inline_style() {
		$css = sprintf( '.big-leader { max-height: %spx; }', $this->setting['max_height'] );
		wp_add_inline_style( 'displayfeaturedimage-style', strip_tags( $css ) );
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
		$common = $this->get_common_class();
		$large  = $common->minimum_backstretch_width();
		$item   = $this->get_item();
		$width  = (int) $item->backstretch[1];
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
	 * All actions required to output the backstretch image
	 * @since 2.3.4
	 */
	protected function do_backstretch_image_things() {
		$common = $this->get_common_class();
		wp_register_script( 'backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), '2.1.15', true );
		wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array(
			'jquery',
			'backstretch',
		), $common->version, true );

		add_action( 'wp_print_scripts', array( $this, 'localize_scripts' ) );
		$location = $this->get_hooks();
		add_action( esc_attr( $location['backstretch']['hook'] ), array( $this, 'do_backstretch_image_title' ), $location['backstretch']['priority'] );
	}

	/**
	 * Pass variables through to our js
	 *
	 * @since 2.3.0
	 */
	public function localize_scripts() {
		// backstretch settings which can be filtered
		$backstretch_vars = apply_filters( 'display_featured_image_genesis_backstretch_variables', array(
			'centeredX' => $this->setting['centeredX'] ? 'center' : 'left',
			'centeredY' => $this->setting['centeredY'] ? 'center' : 'top',
			'fade'      => $this->setting['fade'],
		) );

		$image_id     = Display_Featured_Image_Genesis_Common::set_image_id();
		$large        = wp_get_attachment_image_src( $image_id, 'large' );
		$medium_large = wp_get_attachment_image_src( $image_id, 'medium_large' );
		$responsive   = apply_filters( 'displayfeaturedimagegenesis_responsive_backstretch', true );
		$item         = $this->get_item();
		$output       = array(
			'source'       => array(
				'backstretch'  => esc_url( $item->backstretch[0] ),
				'large'        => $large[3] && $responsive ? esc_url( $large[0] ) : '',
				'medium_large' => $medium_large[3] && $responsive ? esc_url( $medium_large[0] ) : '',
			),
			'width'        => array(
				'backstretch'  => $item->backstretch[1],
				'large'        => $large[3] ? $large[1] : '',
				'medium_large' => $medium_large[3] ? $medium_large[1] : '',
			),
			'image_height' => array(
				'backstretch'  => $item->backstretch[2],
				'large'        => $large[3] ? $large[2] : '',
				'medium_large' => $medium_large[3] ? $medium_large[2] : '',
			),
			'height'       => (int) $this->setting['less_header'],
			'alignX'       => $backstretch_vars['centeredX'],
			'alignY'       => $backstretch_vars['centeredY'],
			'fade'         => (int) $backstretch_vars['fade'],
			'title'        => esc_attr( $this->get_image_alt_text( $image_id ) ),
		);

		wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', $output );
	}

	/**
	 * backstretch image title ( for images which are larger than Media Settings > Large )
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image_title() {

		$this->description = new Display_Featured_Image_Genesis_Description();

		if ( $this->move_title() ) {
			$this->remove_title_descriptions();
		}

		echo '<div class="big-leader">';
		echo '<div class="wrap">';

		do_action( 'display_featured_image_genesis_before_title' );

		if ( $this->move_title() ) {
			if ( $this->move_excerpts() ) {
				$this->do_title_descriptions();
			} else {
				$item = $this->get_item();
				if ( ! empty( $item->title ) && $this->do_the_title() ) {
					echo $this->do_the_title();
				}
				add_action( 'genesis_before_loop', array( $this, 'add_descriptions' ) );
			}
		}

		do_action( 'display_featured_image_genesis_after_title' );

		// close wrap
		echo '</div>';

		// if javascript not enabled, do a fallback featured image
		$image_id = Display_Featured_Image_Genesis_Common::set_image_id();
		$image    = wp_get_attachment_image( $image_id, 'displayfeaturedimage_backstretch', false, array(
			'alt'         => $this->get_image_alt_text( $image_id ),
			'class'       => 'post-image',
			'aria-hidden' => 'true',
		) );
		printf( '<noscript><div class="backstretch no-js">%s</div></noscript>', $image );

		// close big-leader
		echo '</div>';

		add_filter( 'jetpack_photon_override_image_downsize', '__return_false' );
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
		$image_id      = Display_Featured_Image_Genesis_Common::set_image_id();
		$attr['class'] = 'aligncenter featured';
		$attr['alt']   = $this->get_image_alt_text( $image_id );
		$image_size    = apply_filters( 'display_featured_image_large_image_size', Display_Featured_Image_Genesis_Common::image_size() );
		$image         = wp_get_attachment_image( $image_id, $image_size, false, $attr );
		$image         = apply_filters( 'display_featured_image_genesis_large_image_output', $image );
		echo wp_kses_post( $image );
	}

	/**
	 * Return the title.
	 * @return string title with markup.
	 *
	 * @since 2.3.1
	 */
	protected function do_the_title() {
		if ( is_front_page() && ! $this->description->show_front_page_title() ) {
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
	 * Separate archive titles from descriptions. Titles show in leader image
	 * area; descriptions show before loop.
	 *
	 * @since  1.3.0
	 *
	 */
	public function add_descriptions() {

		$this->description->do_tax_description();
		$this->description->do_author_description();
		$this->description->do_cpt_archive_description();

	}

	/**
	 * Do title and description together (for excerpt output)
	 *
	 * @since 2.3.1
	 */
	protected function do_title_descriptions() {
		$this->description->do_front_blog_excerpt();
		$this->description->do_excerpt();
		genesis_do_taxonomy_title_description();
		genesis_do_author_title_description();
		genesis_do_cpt_archive_title_description();
	}

	/**
	 * Remove Genesis titles/descriptions
	 * @since 2.3.1
	 */
	protected function remove_title_descriptions() {
		if ( is_singular() && ! is_page_template( 'page_blog.php' ) ) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
			remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
			if ( ! post_type_supports( get_post_type( get_the_ID() ), 'genesis-entry-meta-before-content' ) && apply_filters( 'displayfeaturedimagegenesis_remove_entry_header', true ) ) {
				remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
				remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
			}
		}
		remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
		remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
		remove_action( 'genesis_before_loop', 'genesis_do_blog_template_heading' );
		remove_action( 'genesis_before_loop', 'genesis_do_posts_page_heading' );
	}

	/**
	 * Check plugin settings/filters to see if the featured image should output on this post/etc. at all.
	 * Returns true to quit now; false to carry on.
	 * @return bool
	 */
	protected function quit_now() {
		$disable       = false;
		$exclude_front = is_front_page() && $this->setting['exclude_front'];
		$post_type     = get_post_type();
		$skip_singular = is_singular() && isset( $this->setting['skip'][ $post_type ] ) && $this->setting['skip'][ $post_type ] ? true : false;

		if ( $this->get_skipped_posttypes() || $skip_singular || $exclude_front || $this->check_post_meta( '_displayfeaturedimagegenesis_disable' ) ) {
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

		return Display_Featured_Image_Genesis_Common::is_in_array( 'skipped_posttypes', $post_types );
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
		$is_paged = ! empty( $this->setting['is_paged'] ) ? $this->setting['is_paged'] : 0;
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
		$move_excerpts = $this->setting['move_excerpts'];
		/**
		 * Creates display_featured_image_genesis_omit_excerpt filter to check
		 * whether get_post_type array should not move excerpts for this post type.
		 * @uses is_in_array()
		 */
		if ( $move_excerpts && ! Display_Featured_Image_Genesis_Common::is_in_array( 'omit_excerpt' ) ) {
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
		$keep_titles = $this->setting['keep_titles'];
		/**
		 * Creates display_featured_image_genesis_do_not_move_titles filter to check
		 * whether get_post_type array should not move titles to overlay the featured image.
		 * @uses is_in_array()
		 */
		if ( $keep_titles || Display_Featured_Image_Genesis_Common::is_in_array( 'do_not_move_titles' ) || $this->check_post_meta( '_displayfeaturedimagegenesis_move' ) ) {
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
		if ( ! isset( $this->setting['thumbnails'] ) || ! $this->setting['thumbnails'] ) {
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
	 * Get the plugin's common class.
	 *
	 * @return \Display_Featured_Image_Genesis_Common
	 */
	protected function get_common_class() {
		if ( isset( $this->common ) ) {
			return $this->common;
		}
		$this->common = new Display_Featured_Image_Genesis_Common();

		return $this->common;
	}

	/**
	 * Get the current featured image and related variables.
	 * @return \stdClass
	 */
	protected function get_item() {
		if ( isset( $this->item ) ) {
			return $this->item;
		}
		$this->item = Display_Featured_Image_Genesis_Common::get_image_variables();

		return $this->item;
	}

	/**
	 * Define the hooks/priorities for image output.
	 *
	 * @return array
	 */
	protected function get_hooks() {
		$large_hook = apply_filters( 'display_featured_image_genesis_move_large_image', 'genesis_before_loop' );
		if ( ! is_singular() || is_page_template( 'page_blog.php' ) ) {
			$check = strpos( $large_hook, 'entry' ) || strpos( $large_hook, 'post' );
			if ( false !== $check ) {
				$large_hook = 'genesis_before_loop';
			}
		}

		return apply_filters( 'displayfeaturedimagegenesis_hooks', array(
			'backstretch' => array(
				'hook'     => apply_filters( 'display_featured_image_move_backstretch_image', 'genesis_after_header' ),
				'priority' => apply_filters( 'display_featured_image_move_backstretch_image_priority', 10 ),
			),
			'large'       => array(
				'hook'     => $large_hook,
				'priority' => apply_filters( 'display_featured_image_genesis_move_large_image_priority', 12 ),
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
}
