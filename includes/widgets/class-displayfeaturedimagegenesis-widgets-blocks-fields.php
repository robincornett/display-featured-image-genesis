<?php

/**
 * Class DisplayFeaturedImageGenesisWidgetsBlocksFields
 */
class DisplayFeaturedImageGenesisWidgetsBlocksFields {

	/**
	 * @var \DisplayFeaturedImageGenesisWidgetsForm
	 */
	private $form;

	/**
	 * Get the data for localizing everything.
	 * @return array
	 */
	public function get_localization_data() {
		$common = array(
			'icon'     => 'format-image',
			'category' => 'widgets',
		);
		$output = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'security' => wp_create_nonce( 'displayfeaturedimagegenesis-block-nonce' ),
		);
		$blocks = include 'fields/blocks.php';
		foreach ( $blocks as $block => $data ) {
			if ( empty( $data['nickname'] ) ) {
				continue;
			}
			$common['panels'] = array(
				'main' => array(
					'title'       => __( 'Block Settings', 'display-featured-image-genesis' ),
					'initialOpen' => true,
					'attributes'  => $this->fields( $block ),
				),
			);
			$common['block']  = "displayfeaturedimagegenesis/{$block}";
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
	public function fields( $block ) {
		$output = array();
		foreach ( $this->get_all_fields( $block ) as $key => $value ) {
			if ( ! empty( $value['args']['id'] ) ) {
				$key = $value['args']['id'];
			}
			$output[ $key ] = $this->get_individual_field_attributes( $value, $block );
		}

		return $output;
	}

	/**
	 * Set the block term action callback here since this class already has a getter for the form class.
	 */
	public function term_action_callback() {
		$form = $this->get_form_class();
		$form->term_action_callback();
	}

	/**
	 * @param $block
	 *
	 * @return array
	 */
	private function get_all_fields( $block ) {
		$fields     = "{$block}_fields";
		$attributes = array_merge(
			include 'fields/blocks-attributes.php',
			$this->$fields()
		);

		return $attributes;
	}

	/**
	 * @return array
	 */
	protected function cpt_fields() {
		$form = $this->get_form_class();

		return array_merge(
			include 'fields/cpt-post_type.php',
			include 'fields/text.php',
			include 'fields/image.php',
			include 'fields/archive.php'
		);
	}

	/**
	 * @return array
	 */
	protected function author_fields() {
		$form = $this->get_form_class();
		$user = array(
			array(
				'method' => 'select',
				'args'   => include 'fields/author-user.php',
			),
		);

		return array_merge(
			$user,
			include 'fields/author-image.php',
			include 'fields/author-gravatar.php',
			include 'fields/author-description.php',
			include 'fields/author-archive.php'
		);
	}

	/**
	 * @return array
	 */
	protected function term_fields() {
		$form = $this->get_form_class();

		return array_merge(
			include 'fields/text.php',
			include 'fields/term-taxonomy.php',
			include 'fields/image.php',
			include 'fields/archive.php'
		);
	}

	/**
	 * Get an array of attributes for an individual field.
	 *
	 * @param $field
	 * @param $block
	 *
	 * @return array
	 */
	private function get_individual_field_attributes( $field, $block ) {
		$method     = empty( $field['method'] ) ? 'text' : $field['method'];
		$field_type = $this->get_field_type( $method );
		if ( empty( $field['args']['label'] ) ) {
			return $field;
		}
		$defaults   = include "fields/{$block}-defaults.php";
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

	/**
	 * Get the widget builder form class.
	 *
	 * @return \DisplayFeaturedImageGenesisWidgetsForm
	 */
	private function get_form_class() {
		if ( isset( $this->form ) ) {
			return $this->form;
		}
		$this->form = new DisplayFeaturedImageGenesisWidgetsForm( $this, array() );

		return $this->form;
	}
}
