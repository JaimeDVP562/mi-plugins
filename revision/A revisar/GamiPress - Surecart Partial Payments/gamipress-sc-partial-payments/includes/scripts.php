<?php
/**
 * Scripts
 *
 * @package     GamiPress\SureCart\Partial_Payments\Scripts
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
function gamipress_sc_partial_payments_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-sc-partial-payments-checkout-js', GAMIPRESS_SC_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-sc-partial-payments-checkout' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_SC_PARTIAL_PAYMENTS_VER, true );

    // Styles
    wp_register_style( 'gamipress-sc-partial-payments-css', GAMIPRESS_SC_PARTIAL_PAYMENTS_URL . 'assets/css/gamipress-sc-partial-payments.css', array(), GAMIPRESS_SC_PARTIAL_PAYMENTS_VER );

}
add_action( 'init', 'gamipress_sc_partial_payments_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_sc_partial_payments_enqueue_scripts( $hook = null ) {

    // Check if current page has a SureCart checkout form
    // We enqueue on all pages since SureCart can load checkout anywhere via shortcode or block
    if( ! is_admin() && is_user_logged_in() ) {

        $points_types = array();
        $prefix = '_gamipress_sc_partial_payments_';

        foreach( gamipress_get_points_types() as $points_type => $data ) {

            if( (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {

                $data['conversion'] = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );

                // Points types without a conversion rate can't be used for partial payments
                if( empty( $data['conversion'] ) ) continue;

                $points_types[$points_type] = $data;
            }

        }

        // Only enqueue if there are points types configured
        if( empty( $points_types ) ) return;

        $currency_symbol = gamipress_sc_partial_payments_get_currency_symbol();

        wp_localize_script( 'gamipress-sc-partial-payments-checkout-js', 'gamipress_sc_partial_payments', array(
            'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce'                 => wp_create_nonce( 'gamipress_sc_partial_payments' ),
            'points_types'          => $points_types,
            'currency_symbol'       => $currency_symbol,
            'decimals'              => 2,
            'decimal_separator'     => '.',
            'thousand_separator'    => ',',
            'remove_label'          => __( '[Remove]', 'gamipress-sc-partial-payments' ),
        ) );

        wp_enqueue_script( 'gamipress-sc-partial-payments-checkout-js' );
        wp_enqueue_style( 'gamipress-sc-partial-payments-css' );
    }

}
add_action( 'wp_enqueue_scripts', 'gamipress_sc_partial_payments_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_sc_partial_payments_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-sc-partial-payments-admin-js', GAMIPRESS_SC_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-sc-partial-payments-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), GAMIPRESS_SC_PARTIAL_PAYMENTS_VER, true );

}
add_action( 'admin_init', 'gamipress_sc_partial_payments_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_sc_partial_payments_admin_enqueue_scripts( $hook ) {

    // Scripts
    wp_enqueue_script( 'gamipress-sc-partial-payments-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_sc_partial_payments_admin_enqueue_scripts', 100 );
