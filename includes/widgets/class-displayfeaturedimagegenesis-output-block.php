<?php
/**
 * Copyright (c) 2019 Robin Cornett
 */

/**
 * Class DisplayFeaturedImageGenesisOutputBlock
 */
class DisplayFeaturedImageGenesisOutputBlock {

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
		foreach ( $this->blocks() as $block => $data ) {
			if ( empty( $data['nickname'] ) || ! is_callable( array( $this, "render_{$data['nickname']}" ) ) ) {
				continue;
			}
			register_block_type(
				"{$this->name}{$block}",
				array(
					'editor_script'   => "{$this->block}block",
					'attributes'      => $this->fields( $block ),
					'render_callback' => array( $this, "render_{$data['nickname']}" ),
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
		return array(
			'term'      => array(
				'title'       => __( 'Display Featured Term Image', 'display-featured-image-genesis' ),
				'description' => __( 'Display a featured term', 'display-featured-image-genesis' ),
				'keywords'    => array(
					__( 'Term', 'display-featured-image-genesis' ),
					__( 'Featured Image', 'display-featured-image-genesis' ),
				),
			),
			'author'    => array(
				'title'       => __( 'Display Featured Author Profile', 'display-featured-image-genesis' ),
				'description' => __( 'Display a featured author', 'display-featured-image-genesis' ),
				'keywords'    => array(
					__( 'Author', 'display-featured-image-genesis' ),
					__( 'Featured Image', 'display-featured-image-genesis' ),
				),
			),
			'post-type' => array(
				'title'       => __( 'Display Featured Post Type Archive Image', 'display-featured-image-genesis' ),
				'description' => __( 'Display a featured content type', 'display-featured-image-genesis' ),
				'keywords'    => array(
					__( 'Post Type', 'display-featured-image-genesis' ),
					__( 'Featured Image', 'display-featured-image-genesis' ),
				),
				'nickname'    => 'cpt',
			),
		);
	}

	/**
	 * Render the widget in a container div.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_cpt( $atts ) {
		$atts      = wp_parse_args( $atts, include 'fields/cpt-defaults.php' );
		$post_type = get_post_type_object( $atts['post_type'] );
		if ( ! $post_type ) {
			return '';
		}

		$classes = $this->get_block_classes( $atts, 'post-type' );
		include plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-cpt.php';
		$output = '<div class="' . implode( ' ', $classes ) . '">';
		ob_start();
		new DisplayFeaturedImageGenesisOutputCPT( $atts, array(), $post_type );
		$output .= ob_get_contents();
		ob_clean();
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get the CSS classes for the block.
	 * @param $atts
	 *
	 * @return array
	 */
	private function get_block_classes( $atts, $block_id ) {
		$classes = array(
			"wp-block-{$this->block}{$block_id}",
		);
		if ( ! empty( $atts['className'] ) ) {
			$classes[] = $atts['className'];
		}
		if ( ! empty( $atts['blockAlignment'] ) ) {
			$classes[] = 'align' . $atts['blockAlignment'];
		}

		return $classes;
	}

	/**
	 * Register the block script and style.
	 */
	public function register_script_style() {
		$minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : ' . min';
		$version = displayfeaturedimagegenesis_get()->version;
		if ( ! $minify ) {
			$version .= current_time( 'gmt' );
		}
		wp_register_script(
			"{$this->block}block",
			plugin_dir_url( dirname( __FILE__ ) ) . "js/test-block{$minify}.js",
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			false
		);
	}

	/**
	 * Localize.
	 */
	public function localize() {
		wp_localize_script( "{$this->block}block", 'DisplayFeaturedImageTestBlock', $this->get_localization_data() );
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
			if ( empty( $data['nickname'] ) || ! is_callable( array( $this, "render_{$data['nickname']}" ) ) ) {
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
			$output[ $key ] = $this->get_individual_field_attributes( $value );
		}

		return $output;
	}

	private function get_all_fields( $block ) {
		if ( 'post-type' === $block ) {
			$block = 'cpt';
		}
		$fields     = "{$block}_fields";
		$attributes = array_merge(
			array(
				'blockAlignment' => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'title'          => array(
					'type'    => 'string',
					'default' => '',
					'args'    => array(
						'id'    => 'title',
						'label' => 'Title',
					),
				),
			),
			$this->$fields()
		);

		return $attributes;
	}

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
	 * Get an array of attributes for an individual field.
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function get_individual_field_attributes( $field ) {
		$method     = empty( $field['method'] ) ? 'text' : $field['method'];
		$field_type = $this->get_field_type( $method );
		if ( empty( $field['args']['label'] ) ) {
			return $field;
		}
		$defaults   = include 'fields/cpt-defaults.php';
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
