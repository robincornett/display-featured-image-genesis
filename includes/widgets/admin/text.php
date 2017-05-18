<?php

$class       = isset( $args['class'] ) ? sprintf( ' class="%s"', esc_attr( $args['class'] ) ) : '';
$label_class = isset( $args['label_class'] ) ? sprintf( ' class="%s"', esc_attr( $args['label_class'] ) ) : '';
echo '<p>';
printf( '<label for="%s"%s>%s </label>', esc_attr( $this->parent->get_field_id( $args['id'] ) ), $label_class, esc_attr( $args['label'] ) );
printf( '<input type="text" id="%s" name="%s" value="%s"%s />', esc_attr( $this->parent->get_field_id( $args['id'] ) ), esc_attr( $this->parent->get_field_name( $args['id'] ) ), esc_attr( $instance[ $args['id'] ] ), $class );
echo '</p>';
