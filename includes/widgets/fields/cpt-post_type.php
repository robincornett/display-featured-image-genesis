<?php

/**
 * Define CPT specific post type fields.
 */
return array(
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'post_type',
			'label'   => __( 'Post Type:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $this->get_post_types(),
		),
	),
);
