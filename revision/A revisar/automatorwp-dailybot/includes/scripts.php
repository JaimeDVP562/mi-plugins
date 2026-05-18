<?php
/**
 * Scripts
 * 
 * @since    1.0.0
 * @package  AutomatorWP\Dailybot\Scripts
 * @author   AutomatorWP
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 * 
 * @since    1.0.0
 * @return   void
 */
function automatorwp_dailybot_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-dailybot-css', AUTOMATORWP_DAILYBOT_URL . 'assets/css/automatorwp-dailybot' . $suffix . '.css', array(), AUTOMATORWP_DAILYBOT_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-dailybot-js',AUTOMATORWP_DAILYBOT_URL . 'assets/js/automatorwp-dailybot' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_DAILYBOT_VER, true );
    
}
add_action( 'admin_init', 'automatorwp_dailybot_admin_register_scripts' );

/**
 * Enqueue admin scripts
 * 
 * @since   1.0.0
 * @return  void
 */
function automatorwp_dailybot_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-dailybot-css' );

    wp_localize_script( 'automatorwp-dailybot-js', 'automatorwp_dailybot', array(
        'nonce' => automatorwp_get_admin_nonce()
    ) );

    wp_enqueue_script( 'automatorwp-dailybot-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_dailybot_admin_enqueue_scripts' );

