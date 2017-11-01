<?php

/**
 * Class DisplayFeaturedImageGenesisDoSetting
 * @package   Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class DisplayFeaturedImageGenesisGetSetting {

	/**
	 * Define the default plugin settings.
	 * @return array
	 * @since 2.6.0
	 */
	public function defaults() {
		return apply_filters( 'displayfeaturedimagegenesis_defaults', array(
			'less_header'          => 0,
			'default'              => '',
			'exclude_front'        => 0,
			'keep_titles'          => 0,
			'move_excerpts'        => 0,
			'is_paged'             => 0,
			'feed_image'           => 0,
			'thumbnails'           => 0,
			'post_types'           => array(),
			'skip'                 => array(),
			'fallback'             => array(),
			'max_height'           => '',
			'always_default'       => 0,
			'centeredX'            => 1,
			'centeredY'            => 1,
			'fade'                 => 750,
			'shortcode'            => array(
				'displayfeaturedimagegenesis_term'      => 0,
				'displayfeaturedimagegenesis_author'    => 0,
				'displayfeaturedimagegenesis_post_type' => 0,
			),
			'backstretch_hook'     => 'genesis_after_header',
			'backstretch_priority' => 10,
			'large_hook'           => 'genesis_before_loop',
			'large_priority'       => 12,
			'large'                => array(),
		) );
	}

	/**
	 * Retrieve plugin setting.
	 * @return array All plugin settings.
	 *
	 * @since 2.3.0
	 */
	public function get_display_setting() {
		$defaults = $this->defaults();
		$setting  = get_option( 'displayfeaturedimagegenesis', $defaults );

		return wp_parse_args( $setting, $defaults );

	}
}
