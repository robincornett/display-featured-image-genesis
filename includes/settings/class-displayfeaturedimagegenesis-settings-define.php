<?php

/**
 * Class Display_Featured_Image_Genesis_Settings_Define
 * @package   DisplayFeaturedImageGenesis
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
			'main'         => array(
				'id'    => 'main',
				'title' => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
				'tab'   => 'main',
			),
			'style'        => array(
				'id'    => 'style',
				'title' => __( 'Display Settings', 'display-featured-image-genesis' ),
				'tab'   => 'style',
			),
			'cpt_sitewide' => array(
				'id'    => 'cpt_sitewide',
				'title' => __( 'Sitewide Settings', 'display-featured-image-genesis' ),
				'tab'   => 'cpt',
			),
			'cpt'          => array(
				'id'    => 'cpt',
				'title' => __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ),
				'tab'   => 'cpt',
			),
			'advanced'     => array(
				'id'    => 'advanced',
				'title' => __( 'Advanced Plugin Settings', 'display-featured-image-genesis' ),
				'tab'   => 'advanced',
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

		return array_merge( $this->define_main_fields(), $this->define_style_fields(), $this->define_cpt_fields(), $this->define_advanced_fields() );
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
				'type'     => 'image',
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
				'type'        => 'checkbox',
			),
			array(
				'id'       => 'image_size',
				'title'    => __( 'Preferred Image Size', 'display-featured-image-genesis' ),
				'callback' => 'do_select',
				'section'  => 'main',
				'choices'  => apply_filters( 'displayfeaturedimagegenesis_image_size_choices', array(
					'displayfeaturedimage_backstretch' => __( 'Backstretch (default)', 'display-featured-image-genesis' ),
					'large'                            => __( 'Large', 'display-featured-image-genesis' ),
				) ),
				'type'     => 'select',
			),
			array(
				'id'       => 'exclude_front',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'keep_titles',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'move_excerpts',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'is_paged',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'feed_image',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'thumbnails',
				'title'    => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ),
				'type'     => 'checkbox',
			),
			array(
				'id'       => 'shortcodes',
				'title'    => __( 'Add Shortcode Buttons', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'label'    => __( 'Add optional shortcode buttons to the post editor', 'display-featured-image-genesis' ),
				'skip'     => true,
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
				'type'        => 'number',
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
				'type'        => 'number',
			),
			array(
				'id'       => 'centeredX',
				'title'    => __( 'Center Horizontally', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'choices'  => $this->pick_center(),
				'legend'   => __( 'Center the backstretch image on the horizontal axis?', 'display-featured-image-genesis' ),
				'type'     => 'radio',
			),
			array(
				'id'       => 'centeredY',
				'title'    => __( 'Center Vertically', 'display-featured-image-genesis' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'style',
				'choices'  => $this->pick_center(),
				'legend'   => __( 'Center the backstretch image on the vertical axis?', 'display-featured-image-genesis' ),
				'type'     => 'radio',
			),
			array(
				'id'       => 'fade',
				'title'    => __( 'Fade', 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'style',
				'label'    => __( 'milliseconds', 'display-featured-image-genesis' ),
				'min'      => 0,
				'max'      => 20000,
				'type'     => 'number',
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
				'id'       => 'skip',
				'title'    => __( 'Skip Content Types', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'cpt_sitewide',
				'options'  => $this->get_post_types(),
				'skip'     => true,
			),
			array(
				'id'          => 'fallback',
				'title'       => __( 'Prefer Fallback Images', 'display-featured-image-genesis' ),
				'callback'    => 'do_checkbox_array',
				'section'     => 'cpt_sitewide',
				'options'     => $this->get_post_types(),
				'description' => __( 'Select content types which should always use a fallback image, even if a featured image has been set.', 'display-featured-image-genesis' ),
				'skip'        => true,
			),
			array(
				'id'          => 'large',
				'title'       => __( 'Force Large Images', 'display-featured-image-genesis' ),
				'callback'    => 'do_checkbox_array',
				'section'     => 'cpt_sitewide',
				'options'     => $this->get_post_types(),
				'description' => __( 'Select content types which should always prefer to use the large image size instead of the backstretch, even if a backstretch size image is available (singular posts/pages, not archives).', 'display-featured-image-genesis' ),
				'skip'        => true,
			),
		);

		$custom_pages = array(
			'search'     => __( 'Search Results', 'display-featured-image-genesis' ),
			'fourohfour' => __( '404 Page', 'display-featured-image-genesis' ),
		);
		$post_types   = array_merge( $custom_pages, $this->get_post_types() );
		foreach ( $post_types as $post_type => $label ) {
			$fields[] = array(
				'id'       => esc_attr( $post_type ),
				'title'    => esc_attr( $label ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'type'     => 'image',
				'skip'     => true,
			);
		}

		return $fields;
	}

	/**
	 * Define the fields for the advanced tab.
	 *
	 * @return array
	 */
	protected function define_advanced_fields() {
		return array(
			array(
				'id'       => 'backstretch_hook',
				'title'    => __( 'Backstretch Image Hook', 'display-featured-image-genesis' ),
				'callback' => 'do_select',
				'section'  => 'advanced',
				'choices'  => array(
					'genesis_before_header'               => 'genesis_before_header',
					'genesis_header'                      => 'genesis_header',
					'genesis_after_header'                => 'genesis_after_header ' . __( '(default)', 'display-featured-image-genesis' ),
					'genesis_before_content_sidebar_wrap' => 'genesis_before_content_sidebar_wrap',
					'genesis_before_content'              => 'genesis_before_content',
				),
				'type'     => 'select',
			),
			array(
				'id'          => 'backstretch_priority',
				'title'       => __( 'Backstretch Image Priority', 'display-featured-image-genesis' ),
				'callback'    => 'do_number',
				'section'     => 'advanced',
				'label'       => '',
				'min'         => 1,
				'max'         => 100,
				'description' => __( 'Default: 10', 'display-featured-image-genesis' ),
				'type'        => 'number',
			),
			array(
				'id'          => 'large_hook',
				'title'       => __( 'Large Image Hook', 'display-featured-image-genesis' ),
				'callback'    => 'do_select',
				'section'     => 'advanced',
				'choices'     => $this->large_hook_options(),
				'description' => __( 'Changing this hook only affects single post/page output, due to overlap/conflict with archive page output.', 'display-featured-image-genesis' ),
				'type'        => 'select',
			),
			array(
				'id'          => 'large_priority',
				'title'       => __( 'Large Image Priority', 'display-featured-image-genesis' ),
				'callback'    => 'do_number',
				'section'     => 'advanced',
				'label'       => '',
				'min'         => 1,
				'max'         => 100,
				'description' => __( 'Default: 12', 'display-featured-image-genesis' ),
				'type'        => 'number',
			),
		);
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

	/**
	 * Get the hooks for the large image.
	 *
	 * @return array
	 */
	protected function large_hook_options() {
		$hooks = array(
			'genesis_before_loop'                 => 'genesis_before_loop ' . __( '(default)', 'display-featured-image-genesis' ),
			'genesis_after_header'                => 'genesis_after_header',
			'genesis_before_content_sidebar_wrap' => 'genesis_before_content_sidebar_wrap',
		);
		$html5 = genesis_html5() ? array(
			'genesis_before_entry'  => 'genesis_before_entry ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
			'genesis_entry_header'  => 'genesis_entry_header ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
			'genesis_entry_content' => 'genesis_entry_content ' . __( '(HTML5 themes)', 'display-featured-image-genesis' ),
		) : array();

		return array_merge( $hooks, $html5 );
	}
}
