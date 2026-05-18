<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\Google_Sheets\Scripts
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
function automatorwp_google_sheets_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-google-sheets-css', AUTOMATORWP_GOOGLE_SHEETS_URL . 'assets/css/automatorwp-google-sheets' . $suffix . '.css', array(), AUTOMATORWP_GOOGLE_SHEETS_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-google-sheets-js', AUTOMATORWP_GOOGLE_SHEETS_URL . 'assets/js/automatorwp-google-sheets' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_GOOGLE_SHEETS_VER, true );

}
add_action( 'admin_init', 'automatorwp_google_sheets_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_google_sheets_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-google-sheets-css' );

    wp_localize_script( 'automatorwp-google-sheets-js', 'automatorwp_google_sheets', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-google-sheets-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_google_sheets_admin_enqueue_scripts', 100 );