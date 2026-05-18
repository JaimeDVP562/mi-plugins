<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Slack\Scripts
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
function automatorwp_slack_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-slack-css', AUTOMATORWP_SLACK_URL . 'assets/css/automatorwp-slack' . $suffix . '.css', array( ), AUTOMATORWP_SLACK_VER, 'all' );
    
    // Scripts
    wp_register_script( 'automatorwp-slack-js', AUTOMATORWP_SLACK_URL . 'assets/js/automatorwp-slack' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_SLACK_VER, true );

    
}
add_action( 'admin_init', 'automatorwp_slack_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_slack_admin_enqueue_scripts( $hook ) {
    // Stylesheet
    wp_enqueue_style( 'automatorwp-slack-css' );

    // Script
    wp_localize_script( 'automatorwp-slack-js', 'automatorwp_slack', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-slack-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_slack_admin_enqueue_scripts', 100 );