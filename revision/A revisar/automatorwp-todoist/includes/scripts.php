<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Todoist\Scripts
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
function automatorwp_todoist_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-todoist-css', AUTOMATORWP_TODOIST_URL . 'assets/css/automatorwp-todoist' . $suffix . '.css', array(), AUTOMATORWP_TODOIST_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-todoist-js', AUTOMATORWP_TODOIST_URL . 'assets/js/automatorwp-todoist' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_TODOIST_VER, true );

}
add_action( 'admin_init', 'automatorwp_todoist_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_todoist_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-todoist-css' );

    wp_localize_script( 'automatorwp-todoist-js', 'automatorwp_todoist', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-todoist-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_todoist_admin_enqueue_scripts', 100 );