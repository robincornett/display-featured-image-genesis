<?php

/**
 * Get gravatar sizes.
 *
 * @return array
 */
$sizes   = apply_filters(
	'genesis_gravatar_sizes',
	array(
		__( 'Small', 'display-featured-image-genesis' )       => 45,
		__( 'Medium', 'display-featured-image-genesis' )      => 65,
		__( 'Large', 'display-featured-image-genesis' )       => 85,
		__( 'Extra Large', 'display-featured-image-genesis' ) => 125,
	)
);
$options = array();
foreach ( (array) $sizes as $label => $size ) {
	$options[ $size ] = $label;
}

/**
 * Define author specific gravatar fields.
 */
return array(
	array(
		'method' => 'checkbox',
		'args'   => array(
			'id'    => 'show_gravatar',
			'label' => __( 'Show the user\'s gravatar.', 'display-featured-image-genesis' ),
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'size',
			'label'   => __( 'Gravatar Size:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $options,
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'gravatar_alignment',
			'label'   => __( 'Gravatar Alignment:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => array(
				''      => __( 'None', 'display-featured-image-genesis' ),
				'left'  => __( 'Left', 'display-featured-image-genesis' ),
				'right' => __( 'Right', 'display-featured-image-genesis' ),
			),
		),
	),
);
