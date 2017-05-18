<?php

$after = isset( $args['after'] ) ? $args['after'] : '';
$flex  = isset( $args['flex'] ) && $args['flex'] ? ' class="flex"' : '';
printf( '<p%s>', wp_kses( $flex, array( 'class' ) ) );
printf( '<label for="%s">%s </label>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $args['label'] ) );
printf( '<input type="number" min="%s" max="%s" id="%s" name="%s" value="%s" />%s', intval( $args['min'] ), intval( $args['max'] ), esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $this->parent->get_field_name( $args['id'] ) ), esc_attr( $instance[ $args['id'] ] ), esc_attr( $after ) );
echo '</p>';
