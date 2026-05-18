<?php
/**
 * Scripts
 *
 * @package     GamiPress\SureCart\Points_Gateway\Scripts
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
function gamipress_sc_points_gateway_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-sc-points-gateway-checkout-js', GAMIPRESS_SC_POINTS_GATEWAY_URL . 'assets/js/gamipress-sc-points-gateway-checkout' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_SC_POINTS_GATEWAY_VER, true );
}
add_action( 'init', 'gamipress_sc_points_gateway_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_sc_points_gateway_enqueue_scripts( $hook = null ) {

    // Checkout Scripts
    if( is_page() ) {
        wp_enqueue_script( 'gamipress-sc-points-gateway-checkout-js' );
    }

}
add_action( 'wp_enqueue_scripts', 'gamipress_sc_points_gateway_enqueue_scripts', 100 );