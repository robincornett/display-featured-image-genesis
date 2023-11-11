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
	 * Get the public registered post types on the site.
	 *
	 * @return mixed
	 */
	public function get_post_types() {
		$args       = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output     = 'objects';
		$post_types = get_post_types( $args, $output );

		$options = array(
			''     => '--',
			'post' => __( 'Posts', 'display-featured-image-genesis' ),
		);
		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->label;
		}

		return $options;
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
	 * @param $instance
	 * @param bool $ajax
	 *
	 * @return mixed
	 */
	public function get_term_lists( $instance, $ajax = false ) {
		$taxonomy    = $ajax ? filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_SPECIAL_CHARS ) : $instance['taxonomy'];
		$args        = array(
			'taxonomy'   => $taxonomy,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
		);
		$terms       = get_terms( $args );
		$options[''] = '--';
		foreach ( $terms as $term ) {
			if ( is_object( $term ) ) {
				$options[ $term->term_id ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Handles the callback to populate the custom term dropdown. The
	 * selected post type is provided in $_POST['taxonomy'], and the
	 * calling script expects a JSON array of term objects.
	 */
	public function term_action_callback() {

		$list = $this->get_term_lists( array(), true );

		// And emit it
		echo wp_json_encode( $list );
		die();
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
