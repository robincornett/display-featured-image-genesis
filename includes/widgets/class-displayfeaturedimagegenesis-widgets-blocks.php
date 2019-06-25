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
	 * @var \DisplayFeaturedImageGenesisWidgetsBlocksFields
	 */
	private $fields;

	/**
	 * Register our block type.
	 */
	public function init() {
		$this->register_script_style();
		include_once 'class-displayfeaturedimagegenesis-widgets-blocks-output.php';
		$output = new DisplayFeaturedImageGenesisWidgetsBlocksOutput();
		$fields = $this->get_fields_class();
		foreach ( $this->blocks() as $block => $data ) {
			if ( ! is_callable( array( $output, "render_{$block}" ) ) ) {
				continue;
			}
			register_block_type(
				"{$this->name}{$block}",
				array(
					'editor_script'   => "{$this->block}block",
					'editor_style'    => "{$this->block}block",
					'attributes'      => $fields->fields( $block ),
					'render_callback' => array( $output, "render_{$block}" ),
				)
			);
		}
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
		add_action( 'wp_ajax_displayfeaturedimagegenesis_block', array( $fields, 'term_action_callback' ) );
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
		$fields = $this->get_fields_class();
		wp_localize_script( "{$this->block}block", 'DisplayFeaturedImageBlock', $fields->get_localization_data() );
	}

	/**
	 * Get the fields class.
	 * @return \DisplayFeaturedImageGenesisWidgetsBlocksFields
	 */
	private function get_fields_class() {
		if ( isset( $this->fields ) ) {
			return $this->fields;
		}
		include_once 'class-displayfeaturedimagegenesis-widgets-blocks-fields.php';
		$this->fields = new DisplayFeaturedImageGenesisWidgetsBlocksFields();

		return $this->fields;
	}
}
