<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Mailjet\Scripts
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
function automatorwp_mailjet_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-mailjet-css', AUTOMATORWP_MAILJET_URL . 'assets/css/automatorwp-mailjet' . $suffix . '.css', array(), AUTOMATORWP_MAILJET_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-mailjet-js', AUTOMATORWP_MAILJET_URL . 'assets/js/automatorwp-mailjet' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_MAILJET_VER, true );

}
add_action( 'admin_init', 'automatorwp_mailjet_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_mailjet_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-mailjet-css' );

    wp_localize_script( 'automatorwp-mailjet-js', 'automatorwp_mailjet', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-mailjet-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailjet_admin_enqueue_scripts', 100 );