<?php
/**
 * Points Rate Field Type
 *
 * Custom CMB2 field that renders two numeric inputs:
 * [X points] = [Y currency]
 *
 * Identical in logic to the EDD version; only the prefix changes.
 *
 * @package GamiPress\FluentCart\Partial_Payments\Libraries
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render the points_rate field
 *
 * @since  1.0.0
 *
 * @param  CMB2_Field $field       The field object.
 * @param  mixed      $value       The stored value.
 * @param  int        $object_id   Object ID.
 * @param  string     $object_type Object type.
 * @param  CMB2_Types $field_type  The CMB2 field types object.
 * @return void
 */
function gamipress_fluentcart_partial_payments_render_points_rate_field( $field, $value, $object_id, $object_type, $field_type ) {

    // Default structure
    $value = wp_parse_args( $value, array(
        'points' => '',
        'money'  => '',
    ) );

    $currency_symbol = isset( $field->args['currency_symbol'] ) ? $field->args['currency_symbol'] : '$';

    ?>
    <div class="gamipress-fluentcart-partial-payments-points-rate">
        <input
            type="number"
            name="<?php echo esc_attr( $field_type->_name( '[points]' ) ); ?>"
            id="<?php echo esc_attr( $field_type->_id( '_points' ) ); ?>"
            value="<?php echo esc_attr( $value['points'] ); ?>"
            placeholder="100"
            min="0"
            step="1"
            class="cmb2-text-small"
        />
        <span class="gamipress-fluentcart-partial-payments-points-rate-label">
            <?php esc_html_e( 'points', 'gamipress-fluentcart-partial-payments' ); ?>
            =
            <?php echo esc_html( $currency_symbol ); ?>
        </span>
        <input
            type="number"
            name="<?php echo esc_attr( $field_type->_name( '[money]' ) ); ?>"
            id="<?php echo esc_attr( $field_type->_id( '_money' ) ); ?>"
            value="<?php echo esc_attr( $value['money'] ); ?>"
            placeholder="1"
            min="0"
            step="0.01"
            class="cmb2-text-small"
        />
    </div>
    <?php

    // Render description if set
    echo $field_type->_desc( true );
}
add_action( 'cmb2_render_points_rate', 'gamipress_fluentcart_partial_payments_render_points_rate_field', 10, 5 );

/**
 * Sanitise the points_rate field value before saving
 *
 * @since  1.0.0
 *
 * @param  mixed      $override_value  Sanitised value override (null by default).
 * @param  mixed      $value           Raw value from POST.
 * @param  int        $object_id       Object ID.
 * @param  array      $field_args      Field arguments.
 * @param  CMB2_Field $field           The field object.
 * @return array
 */
function gamipress_fluentcart_partial_payments_sanitize_points_rate_field( $override_value, $value, $object_id, $field_args, $field ) {

    if ( $field_args['type'] !== 'points_rate' ) {
        return $override_value;
    }

    if ( ! is_array( $value ) ) {
        return array( 'points' => 0, 'money' => 0 );
    }

    return array(
        'points' => absint( $value['points'] ?? 0 ),
        'money'  => floatval( $value['money']  ?? 0 ),
    );
}
add_filter( 'cmb2_sanitize_points_rate', 'gamipress_fluentcart_partial_payments_sanitize_points_rate_field', 10, 5 );
