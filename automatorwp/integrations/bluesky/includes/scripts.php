<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Bluesky\Scripts
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
function automatorwp_bluesky_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-bluesky-css', AUTOMATORWP_BLUESKY_URL . 'assets/css/automatorwp-bluesky' . $suffix . '.css', array(), AUTOMATORWP_BLUESKY_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-bluesky-js', AUTOMATORWP_BLUESKY_URL . 'assets/js/automatorwp-bluesky' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_BLUESKY_VER, true );

}
add_action( 'admin_init', 'automatorwp_bluesky_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_bluesky_admin_enqueue_scripts( $hook ) {

    wp_enqueue_style( 'automatorwp-bluesky-css' );

    wp_localize_script( 'automatorwp-bluesky-js', 'automatorwp_bluesky', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-bluesky-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_bluesky_admin_enqueue_scripts', 100 );