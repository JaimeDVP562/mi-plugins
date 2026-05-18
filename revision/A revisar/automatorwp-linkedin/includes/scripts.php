<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\LinkedIn\Scripts
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
function automatorwp_linkedin_admin_register_scripts() {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-linkedin-css', AUTOMATORWP_LINKEDIN_URL . 'assets/css/automatorwp-linkedin' . $suffix . '.css', array(), AUTOMATORWP_LINKEDIN_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-linkedin-js', AUTOMATORWP_LINKEDIN_URL . 'assets/js/automatorwp-linkedin' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_LINKEDIN_VER, true );

}
add_action( 'admin_init', 'automatorwp_linkedin_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_linkedin_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-linkedin-css' );

    wp_localize_script( 'automatorwp-linkedin-js', 'automatorwp_linkedin', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-linkedin-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_linkedin_admin_enqueue_scripts', 100 );