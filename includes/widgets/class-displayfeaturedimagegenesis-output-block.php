<?php
/**
 * Copyright (c) 2019 Robin Cornett
 */

/**
 * Class ScriptlessSocialSharingOutputBlock
 */
class DisplayFeaturedImageGenesisOutputBlock {

	/**
	 * The block name.
	 *
	 * @var string
	 */
	protected $name = 'displayfeaturedimagegenesis/post-type';

	/**
	 * @var string
	 */
	protected $block = 'displayfeaturedimagegenesis-post-type';

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
		register_block_type(
			$this->name,
			array(
				'editor_script'   => $this->block . '-block',
				'attributes'      => $this->fields(),
				'render_callback' => array( $this, 'render' ),
			)
		);
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
	}

	/**
	 * Render the widget in a container div.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function render( $atts ) {
		$atts      = wp_parse_args( $atts, include 'fields/cpt-defaults.php' );
		$post_type = get_post_type_object( $atts['post_type'] );
		if ( ! $post_type ) {
			return '';
		}

		$classes = $this->get_block_classes( $atts );
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
	private function get_block_classes( $atts ) {
		array(
			'wp-block-' . $this->block,
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
		$version = '3.2.0';
		if ( ! $minify ) {
			$version .= current_time( 'gmt' );
		}
		wp_register_script(
			$this->block . '-block',
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
		wp_localize_script( $this->block . '-block', 'DisplayFeaturedImageTestBlock', $this->get_localization_data() );
	}

	/**
	 * Get the data for localizing everything.
	 * @return array
	 */
	protected function get_localization_data() {
		return array(
			'block'       => $this->name,
			'title'       => __( 'Display Featured Image Genesis Post Type', 'scriptless-social-sharing' ),
			'description' => __( 'featured image.', 'scriptless-social-sharing' ),
			'keywords'    => array(
				__( 'image', 'scriptless-social-sharing' ),
				__( 'featured', 'scriptless-social-sharing' ),
			),
			'panels'      => array(
				'main' => array(
					'title'       => __( 'Block Settings', 'scriptless-social-sharing' ),
					'initialOpen' => true,
					'attributes'  => $this->fields(),
				),
			),
			'icon'        => 'format-image',
			'category'    => 'widgets',
		);
	}

	/**
	 * Get the fields for the block.
	 * @return array
	 */
	private function fields() {
		$output = array();
		foreach ( $this->get_all_fields() as $key => $value ) {
			if ( ! empty( $value['args']['id'] ) ) {
				$key = $value['args']['id'];
			}
			$output[ $key ] = $this->get_individual_field_attributes( $value );
		}

		return $output;
	}

	private function get_all_fields() {
		$form       = new DisplayFeaturedImageGenesisWidgetsForm( $this, array() );
		$fields     = array_merge(
			include 'fields/cpt-post_type.php',
			include 'fields/text.php',
			include 'fields/image.php',
			include 'fields/archive.php'
		);
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
			$fields
		);

		return $attributes;
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
