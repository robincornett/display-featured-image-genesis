<?php

/**
 * Define text fields.
 */
if ( ! isset( $label ) ) {
	$label = '';
}
return array(
	array(
		'method' => 'checkbox',
		'args'   => array(
			'id'    => 'show_title',
			/* translators: this will read Term or Archive */
			'label' => sprintf( __( 'Show %s Title', 'display-featured-image-genesis' ), $label ),
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'show_content',
			/* translators: this will read Term or Archive */
			'label'   => sprintf( __( 'Show %s Intro Text', 'display-featured-image-genesis' ), $label ),
			'choices' => array(
				''       => __( 'None', 'display-featured-image-genesis' ),
				1        => __( 'Intro Text', 'display-featured-image-genesis' ),
				'custom' => __( 'Custom Text (below)', 'display-featured-image-genesis' ),
			),
		),
	),
	array(
		'method' => 'textarea',
		'args'   => array(
			'id'    => 'custom_content',
			'label' => __( 'Custom Intro Text', 'display-featured-image-genesis' ),
		),
	),
);
