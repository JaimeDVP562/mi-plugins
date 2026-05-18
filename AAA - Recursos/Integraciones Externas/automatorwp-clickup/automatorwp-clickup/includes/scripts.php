<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\senpulse\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_senpulse_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'automatorwp-senpulse-js', AUTOMATORWP_senpulse_URL . 'assets/js/automatorwp-senpulse' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_senpulse_VER, true );
    
}
add_action( 'admin_init', 'automatorwp_senpulse_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_senpulse_admin_enqueue_scripts( $hook ) {

    wp_localize_script( 'automatorwp-senpulse-js', 'automatorwp_senpulse', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-senpulse-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_senpulse_admin_enqueue_scripts', 100 );