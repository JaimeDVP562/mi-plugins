<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\Constant_Contact\Scripts
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
function automatorwp_constant_contact_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-constant-contact-css', AUTOMATORWP_CONSTANT_CONTACT_URL . 'assets/css/automatorwp-constant-contact' . $suffix . '.css', array(), AUTOMATORWP_CONSTANT_CONTACT_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-constant-contact-js', AUTOMATORWP_CONSTANT_CONTACT_URL . 'assets/js/automatorwp-constant-contact' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_CONSTANT_CONTACT_VER, true );

}
add_action( 'admin_init', 'automatorwp_constant_contact_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_constant_contact_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-constant-contact-css' );

    wp_localize_script( 'automatorwp-constant-contact-js', 'automatorwp_constant_contact', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-constant-contact-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_constant_contact_admin_enqueue_scripts', 100 );


