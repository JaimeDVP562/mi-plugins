<?php
/**
 * Scripts
 *
 * @package     GamiPress\WooCommerce\Points_Gateway\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_wc_points_gateway_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-wc-points-gateway-checkout-js', GAMIPRESS_WC_POINTS_GATEWAY_URL . 'assets/js/gamipress-wc-points-gateway-checkout' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_WC_POINTS_GATEWAY_VER, true );
}
add_action( 'init', 'gamipress_wc_points_gateway_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_wc_points_gateway_enqueue_scripts( $hook = null ) {

    // Checkout Scripts
    if( is_checkout() ) {
        wp_enqueue_script( 'gamipress-wc-points-gateway-checkout-js' );
    }

}
add_action( 'wp_enqueue_scripts', 'gamipress_wc_points_gateway_enqueue_scripts', 100 );