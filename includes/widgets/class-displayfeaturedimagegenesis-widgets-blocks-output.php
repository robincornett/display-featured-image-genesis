<?php

class DisplayFeaturedImageGenesisWidgetsBlocksOutput {

	private $block = 'displayfeaturedimagegenesis-';

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
		$atts    = wp_parse_args( $atts, include 'fields/author-defaults.php' );
		$classes = $this->get_block_classes( $atts, 'author' );
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
	 * Get the CSS classes for the block.
	 *
	 * @param $atts
	 * @param $block_id
	 *
	 * @return string
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

		return implode( ' ', $classes );
	}
}
