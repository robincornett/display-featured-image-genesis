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
		$classes = $this->get_block_classes( $atts, $block_id );
		$this->include_output_file( $block_id );

		ob_start();
		echo '<div class="' . esc_attr( $classes ) . '">';
		new DisplayFeaturedImageGenesisOutputCPT( $atts, array(), $post_type );
		echo '</div>';
		$output = ob_get_contents();
		ob_clean();

		return $output;
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_author( $atts ) {
		$block_id = 'author';
		$atts     = $this->update_attributes( $atts, $block_id );
		if ( empty( $atts['user'] ) ) {
			return '';
		}
		$classes = $this->get_block_classes( $atts, $block_id );
		$this->include_output_file( $block_id );

		ob_start();
		echo '<div class="' . esc_attr( $classes ) . '">';
		new DisplayFeaturedImageGenesisOutputAuthor( $atts, array() );
		$output = ob_get_contents();
		echo '</div>';
		ob_clean();

		return $output;
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render_term( $atts ) {
		$block_id = 'term';
		$atts     = $this->update_attributes( $atts, $block_id );
		$term     = get_term_by( 'id', $atts['term'], $atts['taxonomy'] );
		if ( ! $term ) {
			return '';
		}
		$classes = $this->get_block_classes( $atts, $block_id );
		$this->include_output_file( $block_id );

		ob_start();
		echo '<div class="' . esc_attr( $classes ) . '">';
		new DisplayFeaturedImageGenesisOutputTerm( $atts, array(), $term );
		echo '</div>';
		$output = ob_get_contents();
		ob_clean();

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

	/**
	 * Load the block output file.
	 *
	 * @param $block_id
	 */
	private function include_output_file( $block_id ) {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . "output/class-displayfeaturedimagegenesis-output-{$block_id}.php";
	}
}
