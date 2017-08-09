<?php
$defaults = $this->parent->defaults();
$value    = isset( $instance[ $args['id'] ] ) ? $instance[ $args['id'] ] : $defaults[ $args['id'] ];
echo '<p>';
printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $this->parent->get_field_name( $args['id'] ) ) );
printf( '<input id="%s" type="checkbox" name="%s" value="1" %s/>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $this->parent->get_field_name( $args['id'] ) ), checked( 1, esc_attr( $value ), false ) );
printf( '<label for="%s">%s</label>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $args['label'] ) );
echo '</p>';
