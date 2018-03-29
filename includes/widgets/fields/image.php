<?php

/**
 * Define image fields.
 */
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
			'choices' => $form->get_image_size(),
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'image_alignment',
			'label'   => __( 'Image Alignment', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $form->get_image_alignment(),
		),
	),
);
