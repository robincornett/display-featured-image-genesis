<?php

/**
 * Class DisplayFeaturedImageGenesisDoSetting
 * @package   Display_Featured_Image_Genesis
 * @copyright 2016 Robin Cornett
 */
class DisplayFeaturedImageGenesisGetSetting {

	/**
	 * The plugin setting.
	 * @var $setting
	 */
	protected $setting;

	/**
	 * Define the default plugin settings.
	 * @return array
	 * @since 2.6.0
	 */
	public function defaults() {
		return apply_filters(
			'displayfeaturedimagegenesis_defaults',
			array(
				'less_header'          => 0,
				'default'              => '',
				'exclude_front'        => 0,
				'keep_titles'          => 0,
				'move_excerpts'        => 0,
				'is_paged'             => 0,
				'feed_image'           => 0,
				'thumbnails'           => 0,
				'post_type'            => array(),
				'skip'                 => array(),
				'fallback'             => array(),
				'max_height'           => '',
				'always_default'       => 0,
				'centeredX'            => 1,
				'centeredY'            => 1,
				'fade'                 => 750,
				'shortcodes'           => 0,
				'backstretch_hook'     => 'genesis_after_header',
				'backstretch_priority' => 10,
				'large_hook'           => 'genesis_before_loop',
				'large_priority'       => 12,
				'large'                => array(),
				'image_size'           => '2048x2048',
				'scriptless'           => 0,
			)
		);
	}

	/**
	 * Retrieve plugin setting.
	 *
	 * @param string $key optional setting key
	 * @return array All plugin settings.
	 * @since 2.3.0
	 */
	public function get_display_setting( $key = '' ) {
		if ( isset( $this->setting ) ) {
			return $key ? $this->setting[ $key ] : $this->setting;
		}
		$defaults = $this->defaults();
		$setting  = get_option( 'displayfeaturedimagegenesis', $defaults );

		$this->setting = wp_parse_args( $setting, $defaults );

		return $key ? $this->setting[ $key ] : $this->setting;
	}
}
