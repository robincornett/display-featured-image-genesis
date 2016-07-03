<?php

/**
 * Class DisplayFeaturedImageGenesisDoSetting
 * @package Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class DisplayFeaturedImageGenesisGetSetting {

	/**
	 * Define the default plugin settings.
	 * @return mixed|void
	 * @since 2.6.0
	 */
	public function defaults() {
		return apply_filters( 'displayfeaturedimagegenesis_defaults', array(
			'less_header'   => 0,
			'default'       => '',
			'exclude_front' => 0,
			'keep_titles'   => 0,
			'move_excerpts' => 0,
			'is_paged'      => 0,
			'feed_image'    => 0,
			'thumbnails'    => 0,
			'post_types'    => array(),
			'skip'          => array(),
			'fallback'      => array(),
			'max_height'    => '',
			'always_default' => 0,
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
