<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Output {

	/**
	 * set parameters for scripts, etc. to run.
	 *
	 * @since 1.1.3
	 */
	public function manage_output() {

		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$skip           = $displaysetting['exclude_front'];

		$post_types = array();
		$post_types[] = 'attachment';
		$post_types[] = 'revision';
		$post_types[] = 'nav_menu_item';

		$skipped_types = apply_filters( 'display_featured_image_genesis_skipped_posttypes', $post_types );

		if ( is_admin() || ( in_array( get_post_type(), $skipped_types ) ) || ( $skip && is_front_page() ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

	}


	/**
	 * enqueue plugin styles and scripts.
	 * @return enqueue
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {

		$version = Display_Featured_Image_Genesis_Common::$version;
		$item    = Display_Featured_Image_Genesis_Common::get_image_variables();
		$large   = Display_Featured_Image_Genesis_Common::minimum_backstretch_width();
		$medium  = absint( get_option( 'medium_size_w' ) );
		$width   = absint( $item->backstretch[1] );

		// check if they have enabled display on subsequent pages
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$is_paged       = ! empty( $displaysetting['is_paged'] ) ? $displaysetting['is_paged'] : 0;

		// if there is no backstretch image set, or it is too small, or it's page 2+ and they didn't change the setting, die
		if ( empty( $item->backstretch ) || $width <= $medium || ( is_paged() && ! $is_paged ) ) {
			return;
		}
		// if the featured image is not part of the content, or we're not on a singular page, carry on
		if ( false === $item->content || ! is_singular() ) {

			$css_file = apply_filters( 'display_featured_image_genesis_css_file', plugin_dir_url( __FILE__ ) . 'css/display-featured-image-genesis.css' );
			wp_enqueue_style( 'displayfeaturedimage-style', esc_url( $css_file ), array(), $version );

			$post_types = array();
			$force_backstretch = apply_filters( 'display_featured_image_genesis_force_backstretch', $post_types );
			// check if the image is large enough for backstretch
			if ( $width > $large || in_array( get_post_type(), $force_backstretch ) ) {

				wp_enqueue_script( 'displayfeaturedimage-backstretch', plugins_url( '/includes/js/backstretch.js', dirname( __FILE__ ) ), array( 'jquery' ), $version, true );
				wp_enqueue_script( 'displayfeaturedimage-backstretch-set', plugins_url( '/includes/js/backstretch-set.js', dirname( __FILE__ ) ), array( 'jquery', 'displayfeaturedimage-backstretch' ), $version, true );

				$hook = apply_filters( 'display_featured_image_move_backstretch_image', 'genesis_after_header' );
				add_action( $hook, array( $this, 'do_backstretch_image_title' ) );

			}

			// otherwise it's a large image.
			elseif ( $width <= $large ) {

				remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
				add_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description', 15 );

				$hook = 'genesis_before_loop';
				if ( is_singular() && ! is_page_template( 'page_blog.php' ) ) {
					$hook = apply_filters( 'display_featured_image_genesis_move_large_image', $hook );
				}
				add_action( $hook, array( $this, 'do_large_image' ), 12 ); // works for both HTML5 and XHTML
			}
		}
	}

	/**
	 * set body class if featured images are displayed using the plugin
	 * @param filter $classes body_class
	 *
	 * @since  1.0.0
	 */
	public function add_body_class( $classes ) {

		$item   = Display_Featured_Image_Genesis_Common::get_image_variables();
		$large  = Display_Featured_Image_Genesis_Common::minimum_backstretch_width();
		$medium = absint( get_option( 'medium_size_w' ) );
		$width  = absint( $item->backstretch[1] );

		// check if they have enabled display on subsequent pages
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$is_paged       = ! empty( $displaysetting['is_paged'] ) ? $displaysetting['is_paged'] : 0;

		// if there is no backstretch image set, or it is too small, or it's page 2+ and they didn't change the setting, die
		if ( empty( $item->backstretch ) || $width <= $medium || ( is_paged() && ! $is_paged ) ) {
			return $classes;
		}

		if ( false === $item->content || ! is_singular() ) {
			if ( $width > $large ) {
				$classes[] = 'has-leader';
			}
			elseif ( $width <= $large ) {
				$classes[] = 'large-featured';
			}
		}
		return apply_filters( 'display_featured_image_genesis_classes', $classes );
	}

	/**
	 * backstretch image title ( for images which are larger than Media Settings > Large )
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_backstretch_image_title() {

		$item           = Display_Featured_Image_Genesis_Common::get_image_variables();
		$displaysetting = get_option( 'displayfeaturedimagegenesis' );
		$keep_titles    = $displaysetting['keep_titles'];

		// backstretch settings from plugin/featured image settings
		$backstretch_settings = array(
			'src'    => esc_url( $item->backstretch[0] ),
			'height' => absint( $displaysetting['less_header'] ),
		);
		// backstretch settings which can be filtered
		$backstretch_variables = array(
			'centeredX' => true,
			'centeredY' => true,
			'fade'      => 750,
		);

		$backstretch_variables = apply_filters( 'display_featured_image_genesis_backstretch_variables', $backstretch_variables );
		$output = array_merge( $backstretch_settings, $backstretch_variables );

		wp_localize_script( 'displayfeaturedimage-backstretch-set', 'BackStretchVars', $output );

		/**
		 * filter to maybe move titles, or not
		 * @var filter
		 * @since 2.2.0
		 */
		$post_types        = array();
		$do_not_move_title = apply_filters( 'display_featured_image_genesis_do_not_move_titles', $post_types );

		// if titles will be moved to overlay backstretch image
		if ( ! $keep_titles && ! in_array( get_post_type(), $do_not_move_title ) ) {
			if ( is_singular() && ! is_front_page() && ! is_page_template( 'page_blog.php' ) ) {
				remove_action( 'genesis_entry_header', 'genesis_do_post_title' ); // HTML5
				remove_action( 'genesis_post_title', 'genesis_do_post_title' ); // XHTML
			}

			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
		}

		echo '<div class="big-leader">';
		echo '<div class="wrap">';

		$move_excerpts = $displaysetting['move_excerpts'];
		$post_types    = array();
		/**
		 * create a filter to not move excerpts if move excerpts is enabled
		 * @var filter
		 * @since  2.0.0 (deprecated old function from 1.3.3)
		 */
		$omit_excerpt = apply_filters( 'display_featured_image_genesis_omit_excerpt', $post_types );

		//* if move excerpts is enabled
		if ( $move_excerpts && ! in_array( get_post_type(), $omit_excerpt ) ) {

			Display_Featured_Image_Genesis_Description::do_front_blog_excerpt();
			Display_Featured_Image_Genesis_Description::do_excerpt();
			genesis_do_taxonomy_title_description();
			genesis_do_author_title_description();
			genesis_do_cpt_archive_title_description();

		}

		// if titles are not being moved to overlay the image
		elseif ( ! $keep_titles && ! in_array( get_post_type(), $do_not_move_title ) ) {

			if ( ! empty( $item->title ) && ! is_front_page() ) {

				$class = 'archive-title';
				if ( is_singular() ) {
					$class = 'entry-title';
				}

				$itemprop = '';
				if ( genesis_html5() ) {
					$itemprop = 'itemprop="headline"';
				}
				$title = $item->title;
				$title_output = sprintf( '<h1 class="%s featured-image-overlay" %s>%s</h1>', $class, $itemprop, $title );

				echo apply_filters( 'display_featured_image_genesis_modify_title_overlay', $title_output, $class, $itemprop, $title );

			}

			remove_action( 'genesis_before_loop', 'genesis_do_cpt_archive_title_description' );
			remove_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
			remove_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );

			add_action( 'genesis_before_loop', array( $this, 'move_titles' ) );

		}

		//* close wrap
		echo '</div>';

		//* if javascript not enabled, do a fallback background image
		$no_js  = '<noscript><div class="backstretch no-js" style="background-image: url(' . esc_url( $item->backstretch[0] ) . '); }"></div></noscript>';
		echo $no_js;

		//* close big-leader
		echo '</div>';
	}

	/**
	 * Large image, centered above content
	 * @return image
	 *
	 * @since  1.0.0
	 */
	public function do_large_image() {
		$item  = Display_Featured_Image_Genesis_Common::get_image_variables();
		$image = sprintf( '<img src="%1$s" class="aligncenter featured" alt="%2$s" />',
			esc_url( $item->backstretch[0] ),
			esc_attr( $item->title )
		);

		echo apply_filters( 'display_featured_image_genesis_large_image_output', $image );
	}

	/**
	 * Separate archive titles from descriptions. Titles show in leader image
	 * area; descriptions show before loop.
	 *
	 * @return descriptions
	 *
	 * @since  1.3.0
	 *
	 */
	public function move_titles() {

		Display_Featured_Image_Genesis_Description::do_tax_description();
		Display_Featured_Image_Genesis_Description::do_author_description();
		Display_Featured_Image_Genesis_Description::do_cpt_archive_description();

	}

}
