<?php

/**
 * Define archive fields.
 */
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
