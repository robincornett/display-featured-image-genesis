<?php

$fields = array(
	array(
		'id'      => 'skip',
		'title'   => __( 'Skip Content Types', 'display-featured-image-genesis' ),
		'type'    => 'checkbox_array',
		'section' => 'cpt_sitewide',
		'options' => $this->get_post_types(),
		'skip'    => true,
	),
	array(
		'id'          => 'fallback',
		'title'       => __( 'Prefer Fallback Images', 'display-featured-image-genesis' ),
		'type'        => 'checkbox_array',
		'section'     => 'cpt_sitewide',
		'options'     => $this->get_post_types(),
		'description' => __( 'Select content types which should always use a fallback image, even if a featured image has been set.', 'display-featured-image-genesis' ),
		'skip'        => true,
	),
	array(
		'id'          => 'large',
		'title'       => __( 'Force Large Images', 'display-featured-image-genesis' ),
		'type'        => 'checkbox_array',
		'section'     => 'cpt_sitewide',
		'options'     => $this->get_post_types(),
		'description' => __( 'Select content types which should always prefer to use the large image size instead of the banner, even if a banner size image is available (singular posts/pages, not archives).', 'display-featured-image-genesis' ),
		'skip'        => true,
	),
);

$custom_pages = array(
	'search'     => __( 'Search Results', 'display-featured-image-genesis' ),
	'fourohfour' => __( '404 Page', 'display-featured-image-genesis' ),
);
$post_types   = array_merge( $custom_pages, $this->get_post_types() );
foreach ( $post_types as $post_type => $label ) {
	$fields[] = array(
		'id'       => esc_attr( $post_type ),
		'title'    => esc_attr( $label ),
		'section'  => 'cpt',
		'type'     => 'image',
	);
}

return $fields;
