<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Smoove\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Daniel Alos<alospratsdani@gmail.com>
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
function automatorwp_Smoove_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-smoove-css', AUTOMATORWP_SMOOVE_URL . 'assets/css/automatorwp-smoove' . $suffix . '.css', array(), AUTOMATORWP_SMOOVE_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-smoove-js', AUTOMATORWP_SMOOVE_URL . 'assets/js/automatorwp-smoove' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_SMOOVE_VER, true );

}
add_action( 'admin_init', 'automatorwp_smoove_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_smoove_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-smoove-css' );

    wp_localize_script( 'automatorwp-smoove-js', 'automatorwp_smoove', array(
        'nonce' => wp_create_nonce('automatorwp_admin'),
    ) );

    wp_enqueue_script( 'automatorwp-smoove-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_smoove_admin_enqueue_scripts', 100 );