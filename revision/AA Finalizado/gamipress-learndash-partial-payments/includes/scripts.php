<?php
/**
 * Scripts
 *
 * @package GamiPress\LearnDash\Partial_Payments\Scripts
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue frontend scripts
 *
 * @since 1.0.0
 */
function gamipress_ld_partial_payments_enqueue_scripts() {
    
    wp_enqueue_script(
        'gamipress-ld-partial-payments-js',
        GAMIPRESS_LD_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-ld-partial-payments.js',
        array( 'jquery' ), // Le decimos que dependemos de jQuery
        GAMIPRESS_LD_PARTIAL_PAYMENTS_VER,
        true // Cargar en el footer
    );

    wp_localize_script(
        'gamipress-ld-partial-payments-js',
        'gamipress_ld_partial_payments_vars',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'gamipress-ld-partial-payments-nonce' )
        )
    );

}
add_action( 'wp_enqueue_scripts', 'gamipress_ld_partial_payments_enqueue_scripts' );