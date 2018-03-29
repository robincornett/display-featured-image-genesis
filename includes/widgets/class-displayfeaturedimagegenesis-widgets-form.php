<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsForm
 */
class DisplayFeaturedImageGenesisWidgetsForm {

	/**
	 * @var
	 */
	protected $parent;

	/**
	 * @var
	 */
	public $instance;

	/**
	 * DisplayFeaturedImageGenesisWidgets constructor.
	 *
	 * @param $parent
	 * @param $instance
	 */
	public function __construct( $parent, $instance ) {
		$this->parent   = $parent;
		$this->instance = $instance;
	}

	/**
	 * @return array
	 */
	public function get_image_size() {
		$sizes   = genesis_get_image_sizes();
		$options = array();
		foreach ( (array) $sizes as $name => $size ) {
			$options[ $name ] = sprintf( '%s ( %s x %s )', esc_html( $name ), (int) $size['width'], (int) $size['height'] );
		}

		return $options;
	}

	/**
	 * @return array
	 */
	public function get_image_alignment() {
		return array(
			'alignnone'   => __( 'None', 'display-featured-image-genesis' ),
			'alignleft'   => __( 'Left', 'display-featured-image-genesis' ),
			'alignright'  => __( 'Right', 'display-featured-image-genesis' ),
			'aligncenter' => __( 'Center', 'display-featured-image-genesis' ),
		);
	}

	/**
	 * Build boxes with fields.
	 *
	 * @param $boxes
	 * @param string $class
	 */
	public function do_boxes( $boxes, $class = '' ) {
		foreach ( $boxes as $box => $value ) {
			if ( ! $value ) {
				continue;
			}
			$box_class = ! $class ? 'genesis-widget-column-box' : 'genesis-widget-column-box ' . $class;
			printf( '<div class="%s">', esc_attr( $box_class ) );
			echo wp_kses_post( wpautop( $this->box_description( $box ) ) );
			$this->do_fields( $this->instance, $value );
			echo '</div>';
		}
	}

	/**
	 * Add a description to a widget settings box.
	 *
	 * @param $box
	 *
	 * @return string
	 */
	public function box_description( $box ) {
		$method = "describe_{$box}";

		return method_exists( $this, $method ) ? $this->$method() : '';
	}

	/**
	 * Cycle through the fields for a given box, pick the appropriate method, and go.
	 *
	 * @param $instance
	 * @param $fields
	 */
	public function do_fields( $instance, $fields ) {
		foreach ( $fields as $field ) {
			$args = $field['args'];
			include $this->path( $field['method'] );
		}
	}

	/**
	 * Generic function to build a text input for the widget form.
	 *
	 * @param $instance
	 * @param $args
	 */
	public function do_text( $instance, $args ) {
		include $this->path( 'text' );
	}

	/**
	 * Generic function to build a select input for the widget form.
	 *
	 * @param $instance
	 * @param $args
	 */
	public function do_select( $instance, $args ) {
		include $this->path( 'select' );
	}

	/**
	 * Get path for included files.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function path( $file ) {
		return trailingslashit( plugin_dir_path( __FILE__ ) . 'admin' ) . $file . '.php';
	}
}
