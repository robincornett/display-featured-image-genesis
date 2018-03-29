<?php

/**
 * Define author specific desctiption fields.
 */
return array(
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'author_info',
			'label'   => __( 'Text to use as the author description:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => array(
				''     => __( 'None', 'display-featured-image-genesis' ),
				'bio'  => __( 'Author Bio (from profile)', 'display-featured-image-genesis' ),
				'text' => __( 'Custom Text (below)', 'display-featured-image-genesis' ),
			),
		),
	),
	array(
		'method' => 'textarea',
		'args'   => array(
			'id'    => 'bio_text',
			'label' => __( 'Custom Text Content', 'display-featured-image-genesis' ),
			'flex'  => true,
			'rows'  => 6,
		),
	),
);
