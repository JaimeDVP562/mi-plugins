<?php
/**
 * Gateway
 *
 * @package GamiPress\SureCart\Points_Gateway\Gateway
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Dynamically register all points types as SureCart payment methods
 *
 * @since  1.0.0
 */
function gamipress_sc_points_gateway_register_gateways( $methods ) {

    $points_types = gamipress_get_points_types();

    foreach( $points_types as $slug => $points_type ) {

        $methods[ 'gamipress_' . $slug ] = array(
            'id'          => 'gamipress_' . $slug,
            'name'        => $points_type['plural_name'],
            'description' => sprintf( __( 'Pay using %s.', 'gamipress-sc-points-gateway' ), $points_type['plural_name'] ),
        );
    }

    return $methods;
}
add_filter( 'surecart/payment_methods', 'gamipress_sc_points_gateway_register_gateways' );

/**
 * Add total row for points.
 *
 * @since 1.0.0
 */
function gamipress_sc_points_gateway_get_order_item_totals( $total_rows, $order ) {

    $payment_method = isset( $order->payment_method_id ) ? $order->payment_method_id : '';
    $points_types = gamipress_get_points_types();
    $points_types_slugs = gamipress_get_points_types_slugs();
    $chosen_points_type = str_replace( 'gamipress_', '', $payment_method );

    if( ! in_array( $chosen_points_type, $points_types_slugs ) )
        return $total_rows;

    $points_type = $points_types[$chosen_points_type];
    $order_total = $order->total_amount / 100;

    $total_rows['gamipress_' . $chosen_points_type] = array(
        'label' => $points_type['plural_name'] . ':',
        'value' => gamipress_sc_points_gateway_convert_to_points( $order_total, $chosen_points_type ),
    );

    return $total_rows;
}
add_filter( 'surecart/order_item_totals', 'gamipress_sc_points_gateway_get_order_item_totals', 10, 2 );

/**
 * Validate points balance before SureCart confirms the order
 *
 * @since 1.0.0
 */
function gamipress_sc_points_gateway_validate_checkout( $checkout ) {

    if( ! is_object( $checkout ) ) return;

    $payment_method = isset( $checkout->payment_method_id ) ? $checkout->payment_method_id : '';

    if( strpos( $payment_method, 'gamipress_' ) !== 0 ) return;

    if( ! is_user_logged_in() ) {
        throw new Exception( __( 'You must be logged in to pay with points.', 'gamipress-sc-points-gateway' ) );
    }

    $points_type_slug = str_replace( 'gamipress_', '', $payment_method );
    $conversion_rate  = gamipress_sc_points_gateway_get_conversion_rate( $points_type_slug );
    $total            = $checkout->total_amount / 100;
    $required_points  = max( 1, round( $total * $conversion_rate ) );
    $user_points      = gamipress_get_user_points( get_current_user_id(), $points_type_slug );

    if( $user_points < $required_points ) {
        $points_types = gamipress_get_points_types();
        $type_name    = $points_types[ $points_type_slug ]['plural_name'];
        throw new Exception( sprintf(
            __( 'Insufficient %s. You need %d but only have %d.', 'gamipress-sc-points-gateway' ),
            $type_name, $required_points, $user_points
        ) );
    }
}
add_action( 'surecart/checkout_before_confirmed', 'gamipress_sc_points_gateway_validate_checkout' );