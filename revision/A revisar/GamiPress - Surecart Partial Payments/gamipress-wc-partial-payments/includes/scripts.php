<?php
/**
 * Scripts
 *
 * @package     GamiPress\WooCommerce\Partial_Payments\Scripts
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
function gamipress_wc_partial_payments_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-wc-partial-payments-checkout-js', GAMIPRESS_WC_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-wc-partial-payments-checkout' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_WC_PARTIAL_PAYMENTS_VER, true );

}
add_action( 'init', 'gamipress_wc_partial_payments_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_wc_partial_payments_enqueue_scripts( $hook = null ) {

    // Checkout Scripts
    if( is_checkout() || is_cart() ) {

        $points_types = array();
        $prefix = '_gamipress_wc_partial_payments_';

        foreach( gamipress_get_points_types() as $points_type => $data ) {

            if( (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {

                $data['conversion'] = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );

                // Points types without a conversion rate can't be used for partial payments
                if( empty( $data['conversion'] ) ) continue;

                $points_types[$points_type] = $data;
            }

        }

        ob_start();
        gamipress_wc_partial_payments_form();
        $partial_payments_form = ob_get_clean();

        wp_localize_script( 'gamipress-wc-partial-payments-checkout-js', 'gamipress_wc_partial_payments', array(
            'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce'                 => wp_create_nonce( 'gamipress_wc_partial_payments' ),
            'points_types'          => $points_types,
            'decimals'              => wc_get_price_decimals(),
            'decimal_separator'     => wc_get_price_decimal_separator(),
            'thousand_separator'    => wc_get_price_thousand_separator(),
            'partial_payments_form' => $partial_payments_form,
            'remove_label'          => __( '[Remove]', 'gamipress-wc-partial-payments' ),

        ) );

        wp_enqueue_script( 'gamipress-wc-partial-payments-checkout-js' );
    }

}
add_action( 'wp_enqueue_scripts', 'gamipress_wc_partial_payments_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_wc_partial_payments_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-wc-partial-payments-admin-js', GAMIPRESS_WC_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-wc-partial-payments-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), GAMIPRESS_WC_PARTIAL_PAYMENTS_VER, true );

}
add_action( 'admin_init', 'gamipress_wc_partial_payments_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_wc_partial_payments_admin_enqueue_scripts( $hook ) {

    // Scripts
    wp_enqueue_script( 'gamipress-wc-partial-payments-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_wc_partial_payments_admin_enqueue_scripts', 100 );