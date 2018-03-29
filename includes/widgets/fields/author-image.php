<?php

/**
 * Define author specific image fields.
 */
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
			'choices' => $form->get_image_size(),
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'featured_image_alignment',
			'label'   => __( 'Image Alignment:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $form->get_image_alignment(),
		),
	),
);
