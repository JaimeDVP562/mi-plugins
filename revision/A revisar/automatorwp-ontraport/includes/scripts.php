<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Ontraport\Scripts
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
function automatorwp_ontraport_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-ontraport-css', AUTOMATORWP_ONTRAPORT_URL . 'assets/css/automatorwp-ontraport' . $suffix . '.css', array(), AUTOMATORWP_ONTRAPORT_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-ontraport-js', AUTOMATORWP_ONTRAPORT_URL . 'assets/js/automatorwp-ontraport' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_ONTRAPORT_VER, true );

}
add_action( 'admin_init', 'automatorwp_ontraport_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_ontraport_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-ontraport-css' );

    wp_localize_script( 'automatorwp-ontraport-js', 'automatorwp_ontraport', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-ontraport-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_ontraport_admin_enqueue_scripts', 100 );