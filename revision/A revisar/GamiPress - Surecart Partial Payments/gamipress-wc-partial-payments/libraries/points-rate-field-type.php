<?php

if( ! function_exists( 'gamipress_render_points_rate_field_type' ) ) {

    /**
     * Adds a custom field type for points conversion rates.
     *
     * @param  object $field             The CMB2_Field type object.
     * @param  string $value             The saved (and escaped) value.
     * @param  int    $object_id         The current post ID.
     * @param  string $object_type       The current object type.
     * @param  object $field_type        The CMB2_Types object.
     *
     * @return void
     */
    function gamipress_render_points_rate_field_type( $field, $value, $object_id, $object_type, $field_type ) {

        // Make sure we specify each part of the value we need.
        $value = wp_parse_args( $value, array(
            'points' => 100,
            'money' => 1,
            'currency_symbol' => '&#36;',
        ) );

        // Input order
        $inverse = ( $field->args( 'inverse' ) === true );

        // Points type setup
        if( $field->args( 'points_type' ) ) {
            $points_plural_name = get_post_meta( $field->args( 'points_type' ), '_gamipress_plural_name', true );
        } else {
            $points_plural_name = get_post_meta( $object_id, '_gamipress_plural_name', true );
        }

        // Last check for the points type plural name
        if( ! $points_plural_name ) {
            $points_plural_name = 'points';
        }

        $points_input = $field_type->input( array(
            'name'  => $field_type->_name( '[points]' ),
            'id'    => $field_type->_id( '_points' ),
            'value' => $value['points'],
            'desc'  => '',
            'type' => 'number',
            'step' => 1,
            'min' => 0,
            'class' => 'small-text',
        ) );

        $money_input = $field_type->input( array(
            'name'  => $field_type->_name( '[money]' ),
            'id'    => $field_type->_id( '_money' ),
            'value' => $value['money'],
            'desc'  => '',
            'type' => 'number',
            'step' => 0.01,
            'min' => 0,
            'class' => 'small-text',
        ) );

        $icon = ' <i class="dashicons dashicons-arrow-right-alt"></i> ';

        $currency = $field->args( 'currency_symbol' );

        ?>

        <ul class="cmb-inline">
            <li>
                <?php echo $inverse ? $money_input : $points_input; ?>
            </li>
            <li>
                <?php echo $inverse ? $currency . $icon . strtolower( $points_plural_name ) : strtolower( $points_plural_name ) . $icon . $currency; ?>
            </li>
            <li>
                <?php echo $inverse ? $points_input : $money_input; ?>
            </li>
        </ul>
        <?php

        $field_type->_desc( true, true );
    }
    add_action( 'cmb2_render_points_rate', 'gamipress_render_points_rate_field_type', 10, 5 );


    /**
     * Sanitize the selected value.
     */
    function gamipress_sanitize_points_rate_callback( $override_value, $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $saved_value ) {
                $value[$key] = sanitize_text_field( $saved_value );
            }

            return $value;
        }

        return;
    }
    add_filter( 'cmb2_sanitize_points_rate', 'gamipress_sanitize_points_rate_callback', 10, 2 );

}