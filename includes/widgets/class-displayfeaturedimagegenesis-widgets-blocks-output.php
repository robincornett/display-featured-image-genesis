<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsBlocksOutput
 */
class DisplayFeaturedImageGenesisWidgetsBlocksOutput {

	/**
	 * @var string
	 */
	private $block = 'displayfeaturedimagegenesis';

	/**
	 * Render the widget in a container div.
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_cpt( $atts ) {
		$block_id  = 'cpt';
		$atts      = $this->update_attributes( $atts, $block_id );
		$post_type = get_post_type_object( $atts['post_type'] );
		if ( ! $post_type ) {
			return '';
		}

		$classes = $this->get_block_classes( $atts, 'cpt' );
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-cpt.php';
		$output = '<div class="' . esc_attr( $classes ) . '">';
		ob_start();
		new DisplayFeaturedImageGenesisOutputCPT( $atts, array(), $post_type );
		$output .= ob_get_contents();
		ob_clean();
		$output .= '</div>';

		return $output;
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_author( $atts ) {
		$classes = $this->get_block_classes( $atts, 'author' );
		$block_id = 'author';
		$atts     = $this->update_attributes( $atts, $block_id );
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-author.php';

		$output = '<div class="' . esc_attr( $classes ) . '">';
		ob_start();
		new DisplayFeaturedImageGenesisOutputAuthor( $atts, array() );
		$output .= ob_get_contents();
		ob_clean();
		$output .= '</div>';

		return $output;
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_term( $atts ) {
		$term = get_term_by( 'id', $atts['term'], $atts['taxonomy'] );
		$block_id = 'term';
		$atts     = $this->update_attributes( $atts, $block_id );
		if ( ! $term ) {
			return '';
		}
		$classes = $this->get_block_classes( $atts, 'term' );
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'output/class-displayfeaturedimagegenesis-output-term.php';

		$output = '<div class="' . esc_attr( $classes ) . '">';
		ob_start();
		new DisplayFeaturedImageGenesisOutputTerm( $atts, array(), $term );
		$output .= ob_get_contents();
		ob_clean();
		$output .= '</div>';

		return $output;
	}

	/**
	 * Update the block attributes by merging with defaults.
	 *
	 * @param $atts
	 * @param $block_id
	 *
	 * @return array
	 */
	private function update_attributes( $atts, $block_id ) {
		return wp_parse_args( $atts, include "fields/{$block_id}-defaults.php" );
	}

	/**
	 * Get the CSS classes for the block.
	 *
	 * @param $atts
	 * @param $block_id
	 *
	 * @return string
	 */
	private function get_block_classes( $atts, $block_id ) {
		$classes = array(
			"wp-block-{$this->block}-{$block_id}",
		);
		if ( ! empty( $atts['className'] ) ) {
			$classes[] = $atts['className'];
		}
		if ( ! empty( $atts['blockAlignment'] ) ) {
			$classes[] = 'align' . $atts['blockAlignment'];
		}

		return implode( ' ', $classes );
	}
}
