<?php

/**
 * Define term specific taxonomy fields.
 */
return array(
	array(
		'method' => 'select',
		'args'   => array(
			'id'       => 'taxonomy',
			'label'    => __( 'Taxonomy:', 'display-featured-image-genesis' ),
			'flex'     => true,
			'onchange' => sprintf( 'term_postback(\'%s\', this.value );', esc_attr( $this->get_field_id( 'term' ) ) ),
			'choices'  => $this->get_taxonomies(),
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'term',
			'label'   => __( 'Term:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $this->get_term_lists( $instance, false ),
		),
	),
);
