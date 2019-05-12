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
		return include 'sections.php';
	}

	/**
	 * Register plugin settings fields
	 * @return array           all settings fields
	 *
	 * @since 2.3.0
	 */
	public function register_fields() {

		$main     = include 'fields-main.php';
		$style    = include 'fields-style.php';
		$cpt      = include 'fields-cpt.php';
		$advanced = include 'fields-advanced.php';

		return array_merge( $main, $style, $cpt, $advanced );
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
