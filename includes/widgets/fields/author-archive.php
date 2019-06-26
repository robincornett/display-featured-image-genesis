<?php

$page_ids     = get_pages(
	array(
		'post_type' => 'page',
	)
);
$page_choices = array(
	'' => __( 'None', 'display-featured-image-genesis' ),
);
if ( $page_ids ) {
	foreach ( $page_ids as $page_id ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$title                        = empty( $page_id->post_title ) ? '#' . $page_id->ID . __( ' (no title)', 'sixtenpress-featured-content' ) : $page_id->post_title;
		$page_choices[ $page_id->ID ] = $title;
	}
}

/**
 * Define author specific archive fields.
 */
return array(
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'page',
			'label'   => __( 'Choose your extended "About Me" page from the list below. This will be the page linked to at the end of the author description.', 'display-featured-image-genesis' ),
			'choices' => $page_choices,
		),
	),
	array(
		'method' => 'text',
		'args'   => array(
			'id'    => 'page_link_text',
			'label' => __( 'Extended page link text', 'display-featured-image-genesis' ),
		),
	),
	array(
		'method' => 'checkbox',
		'args'   => array(
			'id'    => 'posts_link',
			'label' => __( 'Show Author Archive Link?', 'display-featured-image-genesis' ),
		),
	),
	array(
		'method' => 'text',
		'args'   => array(
			'id'    => 'link_text',
			'label' => __( 'Link Text:', 'display-featured-image-genesis' ),
		),
	),
);
