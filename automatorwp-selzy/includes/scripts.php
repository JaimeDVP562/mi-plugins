<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\Selzy\Scripts
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
function automatorwp_selzy_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-selzy-css', AUTOMATORWP_SELZY_URL . 'assets/css/automatorwp-selzy' . $suffix . '.css', array(), AUTOMATORWP_SELZY_VER, 'all' );


    // Scripts
    wp_register_script( 'automatorwp-selzy-js', AUTOMATORWP_SELZY_URL . 'assets/js/automatorwp-selzy' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_SELZY_VER, true );

}
add_action( 'admin_init', 'automatorwp_selzy_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_selzy_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-selzy-css' );

    // Scripts
    wp_localize_script( 'automatorwp-selzy-js', 'automatorwp_selzy', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-selzy-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_selzy_admin_enqueue_scripts', 100 );