<?php
/**
 * Template Functions
 *
 * @package     GamiPress\WooCommerce\Points_Gateway\Template_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @since 1.0.0
 *
 * @param array $file_paths
 *
 * @return array
 */
function gamipress_wc_points_gateway_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/wc-points-gateway/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/wc-points-gateway/';
    $file_paths[] = GAMIPRESS_WC_POINTS_GATEWAY_DIR . 'templates/';

    return $file_paths;

}
add_filter( 'gamipress_template_paths', 'gamipress_wc_points_gateway_template_paths' );

/**
 * Display a points balance on checkout
 *
 * @since 1.0.0
 */
function gamipress_wc_points_gateway_after_order_total() {

    global $woocommerce, $gamipress_wc_points_gateway_template_args;

    // Guests not supported yet (basically because guests has not points)
    if ( ! is_user_logged_in() ) {
        return;
    }

    $gateways = $woocommerce->payment_gateways->payment_gateways();

    $points_types = gamipress_get_points_types();

    foreach( $points_types as $slug => $points_type ) {

        if( isset( $gateways['gamipress_' . $slug] ) ) {

            // Setup template vars
            $gamipress_wc_points_gateway_template_args = array(
                'points_type' => $slug,
                'user_points' => absint( gamipress_get_user_points( get_current_user_id(), $slug ) ),
                'cart_points' => gamipress_wc_points_gateway_convert_to_points( $woocommerce->cart->total, $slug ),
            );

            // Try to load wc-points-checkout-{points-type}.php, if not exists then load wc-points-checkout.php
            gamipress_get_template_part( 'wc-points-checkout', $slug );

        }

    }

}
add_action( 'woocommerce_review_order_after_order_total', 'gamipress_wc_points_gateway_after_order_total' );

/**
 * Display a points balance on checkout block
 *
 * @since 1.0.0
 */
function gamipress_wc_points_gateway_after_order_total_blocks() {

    global $woocommerce, $gamipress_wc_points_gateway_template_args;

    // Guests not supported yet (basically because guests has not points)
    if ( ! is_user_logged_in() ) {
        return;
    }

    // Bail if there is nothing in the cart
    if( $woocommerce->cart === null ) {
        return;
    }

    $gateways = $woocommerce->payment_gateways->payment_gateways();

    $points_types = gamipress_get_points_types();

    foreach( $points_types as $slug => $points_type ) {

        if( isset( $gateways['gamipress_' . $slug] ) ) {

            // Setup template vars
            $gamipress_wc_points_gateway_template_args = array(
                'points_type' => $slug,
                'user_points' => absint( gamipress_get_user_points( get_current_user_id(), $slug ) ),
                'cart_points' => gamipress_wc_points_gateway_convert_to_points( $woocommerce->cart->total, $slug ),
            );

            // Try to load wc-points-checkout-{points-type}.php, if not exists then load wc-points-checkout.php
            gamipress_get_template_part( 'wc-points-checkout-block', $slug );

        }

    }

}