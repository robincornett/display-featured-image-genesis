<?php

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
			'choices' => $this->get_gravatar_sizes(),
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
