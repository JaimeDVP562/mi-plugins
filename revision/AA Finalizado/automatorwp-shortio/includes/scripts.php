<?php
/**
 * Scripts 
 *
 * @package     AutomatorWP\Shortio\Scripts
 * @since       1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts.
 *
 * @since 1.0.0
 */
function automatorwp_shortio_admin_register_scripts() {
    wp_register_script( 
        'automatorwp-shortio-js', 
        AUTOMATORWP_SHORT_IO_URL . 'assets/js/automatorwp-shortio.js', 
        array( 'jquery' ), 
        AUTOMATORWP_SHORT_IO_VER, 
        true 
    );
}
add_action( 'admin_init', 'automatorwp_shortio_admin_register_scripts' );

/**
 * Enqueue admin scripts.
 *
 * @since 1.0.0
 */
function automatorwp_shortio_admin_enqueue_scripts( $hook ) {
    // Only enqueue if we have our JS registered
    if ( wp_script_is( 'automatorwp-shortio-js', 'registered' ) ) {
        wp_localize_script( 'automatorwp-shortio-js', 'automatorwp_shortio', array(
            'nonce' => automatorwp_get_admin_nonce(),
        ) );

        wp_enqueue_script( 'automatorwp-shortio-js' );
    }
}
add_action( 'admin_enqueue_scripts', 'automatorwp_shortio_admin_enqueue_scripts', 100 );