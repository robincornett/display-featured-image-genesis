<?php

echo '<p>';
$rows = isset( $args['rows'] ) ? $args['rows'] : 3;
printf( '<label for="%s">%s</label>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $args['label'] ) );
printf( '<textarea class="large-text" rows="%s" id="%s" name="%s">%s</textarea>', absint( $rows ), esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $this->parent->get_field_name( $args['id'] ) ), esc_attr( $instance[ $args['id'] ] ) );
echo '</p>';
