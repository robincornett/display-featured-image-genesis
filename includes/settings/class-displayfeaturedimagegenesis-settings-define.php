<?php

/**
 * Class Display_Featured_Image_Genesis_Settings_Define
 * @package DisplayFeaturedImageGenesis
 * @copyright 2017 Robin Cornett
 */
class Display_Featured_Image_Genesis_Settings_Define extends Display_Featured_Image_Genesis_Helper {

	/**
	 * Register plugin settings page sections
	 *
	 * @since 2.3.0
	 */
	public function register_sections() {
		return array(
			'main'  => array(
				'id'    => 'main',
				'title' => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
			),
			'style' => array(
				'id'    => 'style',
				'title' => __( 'Display Settings', 'display-featured-image-genesis' ),
			),
			'cpt'   => array(
				'id'    => 'cpt',
				'title' => __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Register plugin settings fields
	 * @return array           all settings fields
	 *
	 * @since 2.3.0
	 */
	public function register_fields() {

		return array_merge( $this->define_main_fields(), $this->define_style_fields(), $this->define_cpt_fields() );
	}

	/**
	 * Define the fields for the main/first tab.
	 * @return array
	 */
	protected function define_main_fields() {
		$common = new Display_Featured_Image_Genesis_Common();
		$large  = $common->minimum_backstretch_width();

		return array(
			array(
				'id'       => 'default',
				'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'callback' => 'set_default_image',
				'section'  => 'main',
			),
			array(
				'id'          => 'always_default',
				'title'       => __( 'Always Use Default', 'display-featured-image-genesis' ),
				'callback'    => 'do_checkbox',
				'section'     => 'main',
				'label'       => __( 'Always use the default image, even if a featured image is set.', 'display-featured-image-genesis' ),
				'description' => sprintf(
					/* translators: placeholder is a number equivalent to the width of the site's Large image (Settings > Media) */
					esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
					absint( $large + 1 )
				),
			),
			array(
				'id'       => 'exclude_front',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'keep_titles',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'move_excerpts',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'is_paged',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'feed_image',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'thumbnails',
				'title'    => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'shortcode',
				'title'    => __( 'Add Shortcode Buttons', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'main',
				'options'  => array(
					'displayfeaturedimagegenesis_term'      => __( 'Featured Term Widget', 'display-featured-image-genesis' ),
					'displayfeaturedimagegenesis_author'    => __( 'Featured Author Widget', 'display-featured-image-genesis' ),
					'displayfeaturedimagegenesis_post_type' => __( 'Featured Post Type Widget', 'display-featured-image-genesis' ),
				),
			),
		);
	}

	/**
	 * Define the fields for the style tab.
	 * @return array
	 */
	protected function define_style_fields() {
		return array(
			array(
				'id'          => 'less_header',
				'title'       => __( 'Height', 'display-featured-image-genesis' ),
				'callback'    => 'do_number',
				'section'     => 'style',
				'label'       => __( 'pixels to remove', 'display-featured-image-genesis' ),
				'min'         => 0,
				'max'         => 400,
				'description' => __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' ),
			),
			array(
				'id'          => 'max_height',
				'title'       => __( 'Maximum Height', 'display-featured-image-genesis' ),
				'callback'    => 'do_number',
				'section'     => 'style',
				'label'       => __( 'pixels', 'display-featured-image-genesis' ),
				'min'         => 100,
				'max'         => 1000,
				'description' => __( 'Optionally, set a max-height value for the header image; it will be added to your CSS.', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'centeredX',
				'title'    => __( 'Center Horizontally', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'buttons'  => $this->pick_center(),
				'legend'   => __( 'Center the backstretch image on the horizontal axis?', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'centeredY',
				'title'    => __( 'Center Vertically', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'buttons'  => $this->pick_center(),
				'legend'   => __( 'Center the backstretch image on the vertical axis?', 'display-featured-image-genesis' ),
			),
			array(
				'id'       => 'fade',
				'title'    => __( 'Fade', 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'style',
				'label'    => __( 'milliseconds', 'display-featured-image-genesis' ),
				'min'      => 0,
				'max'      => 20000,
			),
		);
	}

	/**
	 * Define the fields for the content types tab.
	 * @return array
	 */
	protected function define_cpt_fields() {
		$fields = array(
			array(
				'id'        => 'post_types][search',
				'title'     => __( 'Search Results', 'display-featured-image-genesis' ),
				'callback'  => 'set_cpt_image',
				'section'   => 'cpt',
				'post_type' => 'search',
			),
			array(
				'id'        => 'post_types][fourohfour',
				'title'     => __( '404 Page', 'display-featured-image-genesis' ),
				'callback'  => 'set_cpt_image',
				'section'   => 'cpt',
				'post_type' => 'fourohfour',
			),
			array(
				'id'       => 'skip',
				'title'    => __( 'Skip Content Types', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'cpt',
				'options'  => $this->get_post_types(),
			),
		);

		$post_types = $this->get_post_types();
		if ( $post_types ) {
			$show_on_front = get_option( 'show_on_front' );
			$posts_page    = get_option( 'page_for_posts' );
			if ( 'page' === $show_on_front && $posts_page ) {
				unset( $post_types['post'] );
			}
			foreach ( $post_types as $post_type => $label ) {
				$fields[] = array(
					'id'        => 'post_types][' . esc_attr( $post_type ),
					'title'     => esc_attr( $label ),
					'callback'  => 'set_cpt_image',
					'section'   => 'cpt',
					'post_type' => $post_type,
				);
			}
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	public function pick_center() {
		return array(
			1 => __( 'Center', 'display-featured-image-genesis' ),
			0 => __( 'Do Not Center', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Get the post types as options.
	 * @return array
	 */
	protected function get_post_types() {
		$post_types = $this->get_content_types_built_in();
		$options    = array();
		foreach ( $post_types as $post_type ) {
			$object                = get_post_type_object( $post_type );
			$options[ $post_type ] = $object->label;
		}

		return $options;
	}
}
