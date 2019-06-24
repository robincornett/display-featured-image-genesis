<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsBlocks
 */
class DisplayFeaturedImageGenesisWidgetsBlocks {

	/**
	 * The block name.
	 *
	 * @var string
	 */
	protected $name = 'displayfeaturedimagegenesis/';

	/**
	 * @var string
	 */
	protected $block = 'displayfeaturedimagegenesis-';

	/**
	 * The plugin setting.
	 * @var array
	 */
	protected $setting;

	/**
	 * Register our block type.
	 */
	public function init() {
		$this->register_script_style();
		include_once 'class-displayfeaturedimagegenesis-widgets-blocks-output.php';
		$output = new DisplayFeaturedImageGenesisWidgetsBlocksOutput();
		foreach ( $this->blocks() as $block => $data ) {
			if ( empty( $data['nickname'] ) || ! is_callable( array( $output, "render_{$data['nickname']}" ) ) ) {
				continue;
			}
			register_block_type(
				"{$this->name}{$block}",
				array(
					'editor_script'   => "{$this->block}block",
					'editor_style'    => "{$this->block}block",
					'attributes'      => $this->fields( $block ),
					'render_callback' => array( $output, "render_{$data['nickname']}" ),
				)
			);
		}
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
	}

	/**
	 * Get the list of blocks to create.
	 * @return array
	 */
	private function blocks() {
		return include 'fields/blocks.php';
	}

	/**
	 * Register the block script and style.
	 */
	public function register_script_style() {
		$minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : ' .min';
		$version = displayfeaturedimagegenesis_get()->version;
		wp_register_style( "{$this->block}block", plugin_dir_url( dirname( __FILE__ ) ) . 'css/blocks.css', array(), $version, 'screen' );
		if ( ! $minify ) {
			$version .= current_time( 'gmt' );
		}
		wp_register_script(
			"{$this->block}block",
			plugin_dir_url( dirname( __FILE__ ) ) . "js/block{$minify}.js",
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			false
		);
	}

	/**
	 * Localize.
	 */
	public function localize() {
		wp_localize_script( "{$this->block}block", 'DisplayFeaturedImageBlock', $this->get_localization_data() );
	}

	/**
	 * Get the data for localizing everything.
	 * @return array
	 */
	protected function get_localization_data() {
		$blocks = $this->blocks();
		$common = array(
			'icon'     => 'format-image',
			'category' => 'widgets',
		);
		$output = array();
		foreach ( $blocks as $block => $data ) {
			if ( empty( $data['nickname'] ) ) {
				continue;
			}
			$common['panels'] = array(
				'main' => array(
					'title'       => __( 'Block Settings', 'display-featured-image-genesis' ),
					'initialOpen' => true,
					'attributes'  => $this->fields( $block ),
				),
			);
			$common['block']  = "{$this->name}{$block}";
			if ( ! empty( $data['nickname'] ) ) {
				$block = $data['nickname'];
			}
			$output[ $block ] = array_merge(
				$data,
				$common
			);
		}

		return $output;
	}

	/**
	 * Get the fields for the block.
	 *
	 * @param $block
	 *
	 * @return array
	 */
	private function fields( $block ) {
		$output = array();
		foreach ( $this->get_all_fields( $block ) as $key => $value ) {
			if ( ! empty( $value['args']['id'] ) ) {
				$key = $value['args']['id'];
			}
			$output[ $key ] = $this->get_individual_field_attributes( $value, $block );
		}

		return $output;
	}

	/**
	 * @param $block
	 *
	 * @return array
	 */
	private function get_all_fields( $block ) {
		$fields     = "{$block}_fields";
		$attributes = array_merge(
			include 'fields/blocks-attributes.php',
			$this->$fields()
		);

		return $attributes;
	}

	/**
	 * @return array
	 */
	protected function cpt_fields() {
		$form = new DisplayFeaturedImageGenesisWidgetsForm( $this, array() );

		return array_merge(
			include 'fields/cpt-post_type.php',
			include 'fields/text.php',
			include 'fields/image.php',
			include 'fields/archive.php'
		);
	}

	/**
	 * @return array
	 */
	protected function author_fields() {
		$form = new DisplayFeaturedImageGenesisWidgetsForm( $this, array() );
		$user = array(
			array(
				'method' => 'select',
				'args'   => include 'fields/author-user.php',
			),
		);

		return array_merge(
			$user,
			include 'fields/author-image.php',
			include 'fields/author-gravatar.php',
			include 'fields/author-description.php',
			include 'fields/author-archive.php'
		);
	}

	/**
	 * Get an array of attributes for an individual field.
	 *
	 * @param $field
	 * @param $block
	 *
	 * @return array
	 */
	protected function get_individual_field_attributes( $field, $block ) {
		$method     = empty( $field['method'] ) ? 'text' : $field['method'];
		$field_type = $this->get_field_type( $method );
		if ( empty( $field['args']['label'] ) ) {
			return $field;
		}
		$defaults   = include "fields/{$block}-defaults.php";
		$attributes = array(
			'type'    => $field_type,
			'default' => $defaults[ $field['args']['id'] ],
			'label'   => $field['args']['label'],
			'method'  => $method,
		);
		if ( in_array( 'number', array( $field_type, $method ), true ) ) {
			$attributes['min'] = $field['args']['min'];
			$attributes['max'] = $field['args']['max'];
		} elseif ( 'select' === $method ) {
			$attributes['options'] = $this->convert_choices_for_select( $field['args']['choices'] );
		}

		return $attributes;
	}

	/**
	 * Define the type of field for our script.
	 *
	 * @param $method
	 *
	 * @return string
	 */
	private function get_field_type( $method ) {
		$type = 'string';
		if ( 'number' === $method ) {
			return $method;
		}
		if ( 'checkbox' === $method ) {
			return 'boolean';
		}

		return $type;
	}

	/**
	 * Convert a standard PHP array to what the block editor needs.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private function convert_choices_for_select( $options ) {
		$output = array();
		foreach ( $options as $value => $label ) {
			$output[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return $output;
	}
}
