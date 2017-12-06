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
	protected function get_image_size() {
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
	protected function get_image_alignment() {
		return array(
			'alignnone'   => __( 'None', 'display-featured-image-genesis' ),
			'alignleft'   => __( 'Left', 'display-featured-image-genesis' ),
			'alignright'  => __( 'Right', 'display-featured-image-genesis' ),
			'aligncenter' => __( 'Center', 'display-featured-image-genesis' ),
		);
	}


	/**
	 * Get image fields (used in CPT and term).
	 *
	 * @return array
	 */
	public function get_image_fields() {
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_image',
					'label' => __( 'Show Featured Image', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'image_size',
					'label'   => __( 'Image Size:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_image_size(),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'image_alignment',
					'label'   => __( 'Image Alignment', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_image_alignment(),
				),
			),
		);
	}

	/**
	 * Get the image fields for the author widget.
	 *
	 * @return array
	 */
	public function get_author_image_fields() {
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_featured_image',
					'label' => __( 'Show the user\'s featured image.', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'featured_image_size',
					'label'   => __( 'Image Size:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_image_size(),
				),
			),
			array(
				'method' => 'select',
				'args'   => array(
					'id'      => 'featured_image_alignment',
					'label'   => __( 'Image Alignment:', 'display-featured-image-genesis' ),
					'flex'    => true,
					'choices' => $this->get_image_alignment(),
				),
			),
		);
	}

	/**
	 * Get the text fields (used in CPT and term).
	 *
	 * @return array
	 */
	public function get_text_fields() {
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_title',
					'label' => __( 'Show Term Title', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'show_content',
					'label' => __( 'Show Term Intro Text', 'display-featured-image-genesis' ),
				),
			),
		);
	}

	/**
	 * Get the archive fields (used in term and CPT widgets).
	 *
	 * @return array
	 */
	public function get_archive_fields() {
		return array(
			array(
				'method' => 'checkbox',
				'args'   => array(
					'id'    => 'archive_link',
					'label' => __( 'Show Archive Link', 'display-featured-image-genesis' ),
				),
			),
			array(
				'method' => 'text',
				'args'   => array(
					'id'    => 'archive_link_text',
					'label' => __( 'Archive Link Text', 'display-featured-image-genesis' ),
				),
			),
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
			$method = "do_{$field['method']}";
			if ( method_exists( $this, $method ) ) {
				$this->$method( $instance, $field['args'] );
			}
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
	 * Generic function to build a number input.
	 *
	 * @param $instance
	 * @param $args
	 */
	public function do_number( $instance, $args ) {
		include $this->path( 'number' );
	}

	/**
	 * Generic function to build a checkbox input.
	 *
	 * @param $instance
	 * @param $args
	 */
	public function do_checkbox( $instance, $args ) {
		include $this->path( 'checkbox' );
	}

	/**
	 * Generic function to build a textarea input
	 *
	 * @param $instance
	 * @param $args
	 */
	public function do_textarea( $instance, $args ) {
		include $this->path( 'textarea' );
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
