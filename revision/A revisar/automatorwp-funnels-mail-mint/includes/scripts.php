<?php
/**
 * Scripts
 * 
 * @package AutomatorWP\Mailmint\Scripts
 * @author  AutomatorWP
 * @since   0.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 * 
 * @since    0.1.0
 * @return   void
 */
function automatorwp_mailmint_register_scripts() {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-mailmint-admin-css', AUTOMATORWP_MAILMINT_URL . 'assets/admin' . $suffix . '.css', array(), AUTOMATORWP_MAILMINT_VER );
    
    // Scripts
    wp_register_script( 'automatorwp-mailmint-admin-js', AUTOMATORWP_MAILMINT_URL . 'assets/admin' . $suffix . '.js', array('jquery'), AUTOMATORWP_MAILMINT_VER, true );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailmint_register_scripts' );

/**
 * Enqueue admin scripts
 * 
 * @since    0.1.0
 * @return   void
 */
function automatorwp_mailmint_enqueue_settings_scripts( $hook ) {
    // Only enqueue on AutomatorWP settings screen
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'automatorwp_settings' ) {
        
        // Stylesheets
        wp_enqueue_style( 'automatorwp-mailmint-admin-css' );
        
        // Scripts
        wp_localize_script( 'automatorwp-mailmint-admin-js', 'automatorwp_mailmint_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'automatorwp_admin' ),
        ) );
         wp_enqueue_script( 'automatorwp-mailmint-admin-js' );
    }
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailmint_enqueue_settings_scripts' );
