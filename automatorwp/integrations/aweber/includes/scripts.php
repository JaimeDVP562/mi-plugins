<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\AWeber\Scripts
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
function automatorwp_aweber_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-aweber-css', AUTOMATORWP_AWEBER_URL . 'assets/css/automatorwp-aweber' . $suffix . '.css', array(), AUTOMATORWP_AWEBER_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-aweber-js', AUTOMATORWP_AWEBER_URL . 'assets/js/automatorwp-aweber' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_AWEBER_VER, true );

}
add_action( 'admin_init', 'automatorwp_aweber_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_aweber_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-aweber-css' );

    wp_localize_script( 'automatorwp-aweber-js', 'automatorwp_aweber', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-aweber-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_aweber_admin_enqueue_scripts', 100 );