<?php
/**
 * Scripts
 *
 * @package     GamiPress\FluentCart\Points_Gateway\Scripts
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
function gamipress_fluentcart_points_gateway_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script(
        'gamipress-fluentcart-points-gateway-checkout-js',
        GAMIPRESS_FC_POINTS_GATEWAY_URL . 'assets/js/gamipress-fluentcart-points-gateway-checkout' . $suffix . '.js',
        array( 'jquery' ),
        GAMIPRESS_FC_POINTS_GATEWAY_VER,
        true
    );

    // Localize script with points data for the checkout
    if ( is_user_logged_in() ) {

        $points_data = array();
        $points_types = gamipress_get_points_types();
        $user_id = get_current_user_id();

        foreach ( $points_types as $slug => $points_type ) {

            $conversion_rate = gamipress_fluentcart_points_gateway_get_conversion_rate( $slug );

            $points_data[ $slug ] = array(
                'user_points'     => absint( gamipress_get_user_points( $user_id, $slug ) ),
                'plural_name'     => $points_type['plural_name'],
                'singular_name'   => $points_type['singular_name'],
                'conversion_rate' => $conversion_rate,
                'gateway_id'      => 'gamipress_' . $slug,
            );
        }

        wp_localize_script(
            'gamipress-fluentcart-points-gateway-checkout-js',
            'gamipress_fc_points_gateway',
            array(
                'points_data' => $points_data,
                'i18n'        => array(
                    'current_balance'   => __( 'Current %s:', 'gamipress-fluentcart-points-gateway' ),
                    'required_points'   => __( 'Required %s:', 'gamipress-fluentcart-points-gateway' ),
                    'balance_after'     => __( '%s after purchase:', 'gamipress-fluentcart-points-gateway' ),
                    'insufficient'      => __( 'Insufficient %s to complete this purchase.', 'gamipress-fluentcart-points-gateway' ),
                ),
            )
        );
    }
}
add_action( 'init', 'gamipress_fluentcart_points_gateway_register_scripts' );

/**
 * Enqueue frontend scripts on FluentCart checkout pages
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_fluentcart_points_gateway_enqueue_scripts() {

    // Enqueue on all frontend pages as FluentCart uses dynamic rendering
    // FluentCart's checkout can be on any page with a Gutenberg block
    wp_enqueue_script( 'gamipress-fluentcart-points-gateway-checkout-js' );
}
add_action( 'wp_enqueue_scripts', 'gamipress_fluentcart_points_gateway_enqueue_scripts', 100 );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_fluentcart_points_gateway_admin_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Only enqueue on FluentCart admin pages
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( $screen && strpos( $screen->id, 'fluent-cart' ) !== false ) {
        wp_enqueue_style(
            'gamipress-fluentcart-points-gateway-admin-css',
            GAMIPRESS_FC_POINTS_GATEWAY_URL . 'assets/css/admin.css',
            array(),
            GAMIPRESS_FC_POINTS_GATEWAY_VER
        );
    }
}
add_action( 'admin_enqueue_scripts', 'gamipress_fluentcart_points_gateway_admin_scripts' );
