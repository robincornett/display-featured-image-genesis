<?php

$onchange = isset( $args['onchange'] ) ? sprintf( ' onchange="%s"', $args['onchange'] ) : '';
$class    = isset( $args['class'] ) ? sprintf( ' class="%s"', $args['class'] ) : '';
$flex     = isset( $args['flex'] ) && $args['flex'] ? ' class="flex"' : '';
printf( '<p%s>', wp_kses( $flex, array( 'class' ) ) );
printf( '<label for="%1$s">%2$s </label>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $args['label'] ) );
printf( '<select id="%1$s" name="%2$s"%3$s%4$s style="max-width:220px;">', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $this->parent->get_field_name( $args['id'] ) ), $class, $onchange );
$function = "get_{$args['id']}";
$options  = method_exists( $this->parent, $function ) ? $this->parent->$function( $instance ) : $args['choices'];
foreach ( $options as $option => $label ) {
	printf( '<option value="%1$s" %2$s>%3$s</option>', $option, selected( $option, $instance[ $args['id'] ], false ), $label );
}
echo '</select>';
echo '</p>';
