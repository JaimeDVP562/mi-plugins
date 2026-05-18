<?php
/**
 * Gateway Registration
 *
 * Registers all GamiPress points types as FluentCart payment gateways.
 *
 * @package GamiPress\FluentCart\Points_Gateway\Gateway
 * @since 1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register all GamiPress points types as FluentCart payment gateways
 *
 * Uses fluent_cart/register_payment_methods action hook to register
 * each points type as a separate payment gateway.
 *
 * @since 1.0.0
 */
function gamipress_fluentcart_points_gateway_register_gateways() {

    // Include the gateway class
    require_once GAMIPRESS_FC_POINTS_GATEWAY_DIR . 'classes/class-gamipress-fc-points-gateway.php';

    $points_types = gamipress_get_points_types();

    foreach ( $points_types as $slug => $points_type ) {
        GamiPress_FC_Points_Gateway_Handler::register( $slug, $points_type );
    }
}
add_action( 'fluent_cart/register_payment_methods', 'gamipress_fluentcart_points_gateway_register_gateways', 10 );

/**
 * Add points total row on FluentCart order details
 *
 * @since 1.0.0
 *
 * @param array  $total_rows
 * @param object $order
 *
 * @return array
 */
function gamipress_fluentcart_points_gateway_order_totals( $total_rows, $order ) {

    // Get the payment method used
    $payment_method = $order->payment_method ?? '';

    if ( empty( $payment_method ) || strpos( $payment_method, 'gamipress_' ) !== 0 ) {
        return $total_rows;
    }

    $chosen_points_type = str_replace( 'gamipress_', '', $payment_method );
    $points_types       = gamipress_get_points_types();
    $points_types_slugs = gamipress_get_points_types_slugs();

    // Check if the payment method is one of ours
    if ( ! in_array( $chosen_points_type, $points_types_slugs ) ) {
        return $total_rows;
    }

    $points_type = $points_types[ $chosen_points_type ];
    $deducted    = $order->getMeta( 'gamipress_points_deducted' );

    if ( $deducted ) {
        $total_rows['gamipress_' . $chosen_points_type] = array(
            'label' => $points_type['plural_name'] . ':',
            'value' => $deducted,
        );
    }

    return $total_rows;
}
add_filter( 'fluent_cart/order_item_totals', 'gamipress_fluentcart_points_gateway_order_totals', 10, 2 );

/**
 * Handle refund action via FluentCart
 *
 * @since 1.0.0
 *
 * @param object $order  The order being refunded
 * @param float  $amount The refund amount
 * @param string $reason The refund reason
 */
function gamipress_fluentcart_points_gateway_process_refund( $order, $amount = null, $reason = '' ) {

    $payment_method = $order->payment_method ?? '';

    if ( empty( $payment_method ) || strpos( $payment_method, 'gamipress_' ) !== 0 ) {
        return;
    }

    $chosen_points_type = str_replace( 'gamipress_', '', $payment_method );
    $points_types_slugs = gamipress_get_points_types_slugs();

    if ( ! in_array( $chosen_points_type, $points_types_slugs ) ) {
        return;
    }

    $points_types = gamipress_get_points_types();
    $points_type  = $points_types[ $chosen_points_type ];

    // Create a temporary gateway instance to process the refund
    require_once GAMIPRESS_FC_POINTS_GATEWAY_DIR . 'classes/class-gamipress-fc-points-gateway.php';

    $gateway = new GamiPress_FC_Points_Gateway_Handler( $chosen_points_type, $points_type );
    $gateway->processRefund( $order, $amount, $reason );
}
add_action( 'fluent_cart/order_refunded', 'gamipress_fluentcart_points_gateway_process_refund', 10, 3 );
