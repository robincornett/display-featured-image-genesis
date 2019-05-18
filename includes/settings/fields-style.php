<?php

return array(
	array(
		'id'       => 'scriptless',
		'title'    => __( 'Disable JavaScript', 'display-featured-image-genesis' ),
		'type'     => 'checkbox',
		'section'  => 'style',
		'label'    => __( 'Use a banner image which relies only on CSS.', 'display-featured-image-genesis' ),
	),
	array(
		'id'          => 'less_header',
		'title'       => __( 'Height', 'display-featured-image-genesis' ),
		'section'     => 'style',
		'label'       => __( 'pixels to remove', 'display-featured-image-genesis' ),
		'min'         => 0,
		'max'         => 400,
		'description' => __( 'Changing this number will reduce the banner image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' ),
		'type'        => 'number',
	),
	array(
		'id'          => 'max_height',
		'title'       => __( 'Maximum Height', 'display-featured-image-genesis' ),
		'section'     => 'style',
		'label'       => __( 'pixels', 'display-featured-image-genesis' ),
		'min'         => 100,
		'max'         => 1000,
		'description' => __( 'Optionally, set a max-height value for the banner image; it will be added to your CSS.', 'display-featured-image-genesis' ),
		'type'        => 'number',
	),
	array(
		'id'       => 'centeredX',
		'title'    => __( 'Center Horizontally', 'display-featured-image-genesis' ),
		'section'  => 'style',
		'choices'  => $this->pick_center(),
		'legend'   => __( 'Center the banner image on the horizontal axis?', 'display-featured-image-genesis' ),
		'type'     => 'radio',
	),
	array(
		'id'       => 'centeredY',
		'title'    => __( 'Center Vertically', 'display-featured-image-genesis' ),
		'section'  => 'style',
		'choices'  => $this->pick_center(),
		'legend'   => __( 'Center the banner image on the vertical axis?', 'display-featured-image-genesis' ),
		'type'     => 'radio',
	),
	array(
		'id'       => 'fade',
		'title'    => __( 'Fade', 'display-featured-image-genesis' ),
		'section'  => 'style',
		'label'    => __( 'milliseconds', 'display-featured-image-genesis' ),
		'min'      => 0,
		'max'      => 20000,
		'type'     => 'number',
	),
);
