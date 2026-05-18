<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Keap\Scripts
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
function automatorwp_keap_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-keap-css', AUTOMATORWP_KEAP_URL . 'assets/css/automatorwp-keap' . $suffix . '.css', array(), AUTOMATORWP_KEAP_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-keap-js', AUTOMATORWP_KEAP_URL . 'assets/js/automatorwp-keap' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_KEAP_VER, true );

}
add_action( 'admin_init', 'automatorwp_keap_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_keap_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-keap-css' );

    wp_localize_script( 'automatorwp-keap-js', 'automatorwp_keap', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-keap-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_keap_admin_enqueue_scripts', 100 );