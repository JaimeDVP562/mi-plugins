<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Sendpulse\Scripts
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
function automatorwp_sendpulse_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets - assets registration for SendPulse
    wp_register_style( 'automatorwp-sendpulse-css', AUTOMATORWP_SENDPULSE_URL . 'assets/css/automatorwp-sendpulse' . $suffix . '.css', array(), AUTOMATORWP_SENDPULSE_VER, 'all' );

    // Scripts - assets registration for SendPulse
    wp_register_script( 'automatorwp-sendpulse-js', AUTOMATORWP_SENDPULSE_URL . 'assets/js/automatorwp-sendpulse' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_SENDPULSE_VER, true );

}
add_action( 'admin_init', 'automatorwp_sendpulse_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_sendpulse_admin_enqueue_scripts( $hook ) {

    // Only enqueue on AutomatorWP pages where admin UI is shown (settings and automations)
    $allowed_pages = array( 'automatorwp_settings', 'automatorwp_automations', 'edit_automatorwp_automations', 'automatorwp' );

    $should_enqueue = false;
    if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $allowed_pages, true ) ) {
        $should_enqueue = true;
    } else {
        // Try to detect AutomatorWP screens via current screen ID
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && strpos( $screen->id, 'automatorwp' ) !== false ) {
                $should_enqueue = true;
            }
        }

        // Also allow when editing AutomatorWP custom post types (some builders render on edit.php)
        if ( isset( $_GET['post_type'] ) && strpos( $_GET['post_type'], 'automatorwp' ) !== false ) {
            $should_enqueue = true;
        }
    }

    if ( ! $should_enqueue ) {
        return;
    }

    wp_enqueue_style( 'automatorwp-sendpulse-css' );

    wp_localize_script( 'automatorwp-sendpulse-js', 'automatorwp_sendpulse', array(
        'nonce'    => automatorwp_get_admin_nonce(),
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        // Exact settings page URL used as OAuth redirect URI (must match provider registration)
        'settings_page_url' => admin_url( 'admin.php?page=automatorwp_settings&tab=sendpulse' ),
    ) );

    wp_enqueue_script( 'automatorwp-sendpulse-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_sendpulse_admin_enqueue_scripts', 100 );