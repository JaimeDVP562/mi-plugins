<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Pipedrive\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Jonathan Agudo <jonathanagudo8@gmail.com>
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
function automatorwp_pipedrive_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-pipedrive-css', AUTOMATORWP_PIPEDRIVE_URL . 'assets/css/automatorwp-pipedrive' . $suffix . '.css', array(), AUTOMATORWP_PIPEDRIVE_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-pipedrive-js', AUTOMATORWP_PIPEDRIVE_URL . 'assets/js/automatorwp-pipedrive' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_PIPEDRIVE_VER, true );

}
add_action( 'admin_init', 'automatorwp_pipedrive_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_pipedrive_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-pipedrive-css' );

    wp_localize_script( 'automatorwp-pipedrive-js', 'automatorwp_pipedrive', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-pipedrive-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_pipedrive_admin_enqueue_scripts', 100 );