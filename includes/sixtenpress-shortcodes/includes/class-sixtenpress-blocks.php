<?php

/**
 * Class SixTenPressBlocks
 */
abstract class SixTenPressBlocks {

	/**
	 * The block name, namespaced: eg sixtenpress/featured-content.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The block name with dashes: eg sixtenpress-featured-content.
	 * @var string
	 */
	protected $block;

	/**
	 * The plugin version.
	 * @var string
	 */
	protected $version;

	/**
	 * Default block registration.
	 * Does not need to be overridden unless something unusual is going on with the block.
	 */
	public function init() {
		$this->register_script_style();
		register_block_type(
			$this->name,
			array(
				'editor_script'   => $this->block,
				'editor_style'    => $this->block,
				'attributes'      => $this->get_attributes(),
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Build the rendering function.
	 *
	 * @param $atts
	 */
	abstract public function render( $atts );

	/**
	 * Register the admin editor assets. They do not need to be enqueued
	 * as that's handled by the block registration.
	 * Example usage:
	 * public function register_script_style() {
	 *     wp_register_style( $this->block', plugin_dir_url( __FILE__ ) . 'css/block.css', array(), $this->version, 'all' );
	 *     $minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	 *     $version = $minify ? $this->version : $this->version . current_time( 'gmt' );
	 *     wp_register_script(
	 *         $this->block',
	 *         plugin_dir_url( __FILE__ ) . "js/index{$minify}.js",
	 *         array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
	 *         $version,
	 *         false
	 *     );
	 * }
	 */
	abstract public function register_script_style();

	/**
	 * Define the panels for the block.
	 *
	 * @uses get_individual_field_attributes
	 *
	 * @return array
	 */
	abstract protected function get_panels();

	/**
	 * Define the attributes for the block.
	 *
	 * @return array
	 */
	abstract protected function get_attributes();

	/**
	 * Define the array of individual field attributes.
	 *
	 * @param $field
	 * @param $id
	 *
	 * @return array
	 */
	abstract protected function get_individual_field_attributes( $field, $id );

	/**
	 * Define the type of field for our script.
	 *
	 * @param $method
	 *
	 * @return string
	 */
	protected function get_field_type( $method ) {
		$type = 'string';
		if ( 'number' === $method ) {
			return $method;
		}
		if ( 'checkbox' === $method ) {
			return 'boolean';
		}

		return $type;
	}
}
