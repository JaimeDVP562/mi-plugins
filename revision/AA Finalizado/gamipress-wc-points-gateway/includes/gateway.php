<?php
/**
 * Gateway
 *
 * @package GamiPress\WooCommerce\Points_Gateway\Gateway
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Dynamically register all points types as WooCommerce gateways
 *
 * @since  1.0.0
 *
 * @param $gateways
 *
 * @return array
 */
function gamipress_wc_points_gateway_register_gateways( $gateways ) {

    $points_types = gamipress_get_points_types();

    foreach( $points_types as $slug => $points_type ) {

        $gateways[] = new GamiPress_WC_Points_Gateway( $slug, $points_type );

    }

    return $gateways;
}
add_action( 'woocommerce_payment_gateways', 'gamipress_wc_points_gateway_register_gateways' );

/**
 * WooCommerce checkout block registration
 *
 * @since  1.1.6
 */
function gamipress_wc_points_gateway_blocks_loaded() {
    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        add_action( 'woocommerce_blocks_payment_method_type_registration', 'gamipress_wc_points_gateway_register_block_gateways' );
    }
}
add_action( 'woocommerce_blocks_loaded', 'gamipress_wc_points_gateway_blocks_loaded' );

/**
 * @param Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry
 */
function gamipress_wc_points_gateway_register_block_gateways( $payment_method_registry ) {

    require_once GAMIPRESS_WC_POINTS_GATEWAY_DIR . 'classes/class-gamipress-wc-points-blocks-support.php';

    $points_types = gamipress_get_points_types();

    foreach( $points_types as $slug => $points_type ) {

        $payment_method_registry->register( new GamiPress_WC_Points_Gateway_Blocks_Support( $slug, $points_type ) );

    }

}

/**
 * Add total row for points.
 *
 * @since 1.0.0
 *
 * @param array $total_rows
 * @param WC_Order $order
 * @param string $tax_display
 *
 * @return array
 */
function gamipress_wc_points_gateway_get_order_item_totals( $total_rows, $order, $tax_display ) {

    global $woocommerce;

    // Setup vars
    $payment_method = $order->get_payment_method();
    $points_types = gamipress_get_points_types();
    $points_types_slugs = gamipress_get_points_types_slugs();
    $chosen_points_type = str_replace( 'gamipress_', '', $payment_method );

    // Just continue if payment method is one of the provided by this add-on
    if( ! in_array( $chosen_points_type, $points_types_slugs ) )
        return $total_rows;

    $points_type = $points_types[$chosen_points_type];
    $order_total = $order->get_total();

    $total_rows['gamipress_' . $chosen_points_type] = array(
        'label' => $points_type['plural_name'] . ':',
        'value' => gamipress_wc_points_gateway_convert_to_points( $order_total, $chosen_points_type ),
    );

    return $total_rows;

}
add_filter( 'woocommerce_get_order_item_totals', 'gamipress_wc_points_gateway_get_order_item_totals', 10, 3 );
