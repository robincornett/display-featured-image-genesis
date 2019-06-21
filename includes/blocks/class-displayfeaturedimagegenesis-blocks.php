<?php

/**
 * Class DisplayFeaturedImageGenesisBlocks
 */
class DisplayFeaturedImageGenesisBlocks {

	/**
	 *
	 */
	public function init() {
		$this->register_script_style();
		foreach ( $this->blocks() as $block => $data ) {
			if ( 'term' === $block ) {
				continue;
			}
			register_block_type(
				"display-featured-image-genesis/{$block}",
				array(
					'editor_script'   => 'displayfeaturedimagegenesis-block',
					'attributes'      => $this->get_attributes( $block ),
					'render_callback' => array( $this, 'render' ),
				)
			);
		}
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize' ) );
	}

	/**
	 * Register the block script and style.
	 */
	public function register_script_style() {
		$version = displayfeaturedimagegenesis_get()->version;
		$minify  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : ' . min';
		if ( ! $minify ) {
			$version .= current_time( 'gmt' );
		}
		wp_register_script(
			'displayfeaturedimagegenesis-block',
			plugin_dir_url( dirname( __FILE__ ) ) . "js/block{$minify}.js",
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			false
		);
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
			),
		);
	}

	/**
	 * Render the widget in a container div.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		rgc_error_log( $attributes );
//		$classes = $this->get_block_classes( $attributes );
//		$output  = '<div class="' . implode( ' ', $classes ) . '">';
////		$output .= $this->register->shortcode( $attributes );
//		$output .= '</div>';

		return 'this is the output';
	}

	/**
	 * Get the block classes.
	 *
	 * @param $attributes
	 *
	 * @return array
	 * @since 0.4.0
	 */
	private function get_block_classes( $attributes ) {
		$classes = array(
			'wp-block-displayfeaturedimagegenesis' . 'term',
		);
		if ( ! empty( $attributes['className'] ) ) {
			$classes[] = $attributes['className'];
		}
		if ( ! empty( $attributes['blockAlignment'] ) ) {
			$classes[] = 'align' . $attributes['blockAlignment'];
		}

		return $classes;
	}

	/**
	 * @param $block
	 *
	 * @return array
	 */
	protected function get_attributes( $block ) {
		$attributes = array_merge(
			$this->fields(),
			$this->get_block_fields( $block )
		);
		foreach ( $attributes as $key => $value ) {
			$attributes[ $key ] = $this->get_individual_field_attributes( $value, $key );
		}

		return $attributes;
	}

	/**
	 *
	 */
	public function localize() {
		$args = array();
		foreach ( $this->blocks() as $block => $data ) {
			$args[ $block ] = $this->get_localization_data( $block, $data );
		}
		wp_localize_script( 'displayfeaturedimagegenesis-block', 'DisplayFeaturedImageGenesisBlock', $args );
	}

	/**
	 * Get the data for localizing everything.
	 *
	 * @param $block
	 * @param $data
	 *
	 * @return array
	 */
	protected function get_localization_data( $block, $data ) {
		return array(
			'block'       => "displayfeaturedimagegenesis/{$block}",
			'title'       => $data['title'],
			'description' => $data['description'],
			'keywords'    => $data['keywords'],
			'panels'      => array(
				'main' => array(
					'title'       => __( 'Block Settings', 'display-featured-image-genesis' ),
					'initialOpen' => true,
					'attributes'  => $this->get_attributes( $block ),
				),
			),
			'icon'        => 'format-image',
			'category'    => 'widgets',
		);
	}

	/**
	 * @return array
	 */
	private function fields() {
		return array(
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
				'label'   => __( 'Title', 'display-featured-image-genesis' ),
			),
			'show_image'     => array(
				'type'    => 'boolean',
				'default' => 1,
				'label'   => __( 'Show Featured Image', 'display-featured-image-genesis' ),
				'method'  => 'checkbox',
			),
			'image_size'     => array(
				'type'    => 'string',
				'default' => 'medium',
				'label'   => __( 'Image Size:', 'display-featured-image-genesis' ),
				'method'  => 'select',
				'choices' => $this->get_image_size(),
			),
		);
	}

	/**
	 * Get an array of attributes for an individual field.
	 *
	 * @param $field
	 * @param $id
	 *
	 * @return array
	 */
	protected function get_individual_field_attributes( $field, $id ) {
		$field_type = $field['type'];
		if ( empty( $field['label'] ) ) {
			return $field;
		}
		$attributes = array(
			'type'    => $field['type'],
			'default' => $field['default'],
			'label'   => $field['label'],
			'method'  => empty( $field['method'] ) ? 'text' : $field['method'],
		);
		if ( in_array( 'number', array( $field_type, $attributes['method'] ), true ) ) {
			$attributes['min'] = $field['args']['min'];
			$attributes['max'] = $field['args']['max'];
		} elseif ( 'select' === $attributes['method'] ) {
			foreach ( $field['choices'] as $value => $label ) {
				$attributes['options'][] = array(
					'value' => $value,
					'label' => $label,
				);
			}
		} elseif ( 'boolean' === $field_type ) {
			$attributes['default'] = 0;
		}

		return $attributes;
	}

	private function get_image_size() {
		$sizes   = genesis_get_image_sizes();
		$options = array();
		foreach ( (array) $sizes as $name => $size ) {
			$options[ $name ] = sprintf( '%s ( %s x %s )', esc_html( $name ), (int) $size['width'], (int) $size['height'] );
		}

		return $options;
	}

	/**
	 * @param $block
	 *
	 * @return array
	 */
	private function get_block_fields( $block ) {
		return array();
	}
}
