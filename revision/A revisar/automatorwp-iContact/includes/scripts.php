<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\IContact\Scripts
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
function automatorwp_iContact_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-iContact-css', AUTOMATORWP_ICONTACT_URL . 'assets/css/automatorwp-iContact' . $suffix . '.css', array(), AUTOMATORWP_ICONTACT_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-iContact-js', AUTOMATORWP_ICONTACT_URL . 'assets/js/automatorwp-iContact' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_ICONTACT_VER, true );

}
add_action( 'admin_init', 'automatorwp_iContact_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_iContact_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-iContact-css' );

    wp_localize_script( 'automatorwp-iContact-js', 'automatorwp_iContact', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-iContact-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_iContact_admin_enqueue_scripts', 100 );