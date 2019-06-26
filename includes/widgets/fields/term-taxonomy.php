<?php

$args        = array(
	'public'  => true,
	'show_ui' => true,
);
$taxonomies  = get_taxonomies( $args, 'objects' );
$tax_options = array();
foreach ( $taxonomies as $taxonomy ) {
	$tax_options[ $taxonomy->name ] = $taxonomy->label;
}
if ( empty( $instance ) ) {
	$instance = include 'term-defaults.php';
}

$onchange = is_callable( $this, 'get_field_id ' ) ? sprintf( 'term_postback(\'%s\', this.value );', esc_attr( $this->get_field_id( 'term' ) ) ) : '';

/**
 * Define term specific taxonomy fields.
 */
return array(
	array(
		'method' => 'select',
		'args'   => array(
			'id'       => 'taxonomy',
			'label'    => __( 'Taxonomy:', 'display-featured-image-genesis' ),
			'flex'     => true,
			'onchange' => '',
			'choices'  => $tax_options,
		),
	),
	array(
		'method' => 'select',
		'args'   => array(
			'id'      => 'term',
			'label'   => __( 'Term:', 'display-featured-image-genesis' ),
			'flex'    => true,
			'choices' => $form->get_term_lists( $instance, false ),
		),
	),
);
