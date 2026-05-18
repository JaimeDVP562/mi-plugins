<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Pipedrive\Ajax_Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Handler to save OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_pipedrive_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_pipedrive_";
    
    /* sanitize incoming data */
    $consumer_key = sanitize_text_field( $_POST["consumer_key"] );

    if( empty( $consumer_key ) ) {
        // return error if the field is missing
        wp_send_json_error( array( 'message' => __( 'API Key is required to connect with Pipedrive', 'automatorwp-pipedrive' ) ) );
    } else {
        $credentials = get_option( 'automatorwp_settings' );

        $credentials[$prefix . 'consumer_key'] = $consumer_key;
        $credentials = array_filter( $credentials );

        update_option( 'automatorwp_settings', $credentials );

        wp_send_json_success( array( 'message' => __( 'Successfully connected to Pipedrive', 'automatorwp-pipedrive' ) ) );
    }
}
add_action( 'wp_ajax_automatorwp_pipedrive_save_oauth_credentials', 'automatorwp_pipedrive_ajax_save_oauth_credentials' );

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_pipedrive_ajax_delete_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = "automatorwp_pipedrive_";
    $credentials = get_option( 'automatorwp_settings' );
    $credentials[$prefix . 'consumer_key'] = null;
    $credentials = array_filter( $credentials );

    update_option( 'automatorwp_settings', $credentials );

    wp_send_json_success( array( 'message' => __( 'Credentials deleted successfully', 'automatorwp-pipedrive' ) ) );

}
add_action( 'wp_ajax_automatorwp_pipedrive_delete_oauth_credentials', 'automatorwp_pipedrive_ajax_delete_oauth_credentials' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_pipedrive_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_pipedrive_';

    $consumer_key = sanitize_text_field( $_POST['consumer_key'] );

    if( empty( $consumer_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API Key is required to connect with Pipedrive', 'automatorwp-pipedrive' ) ) );
        return;
    }
    
    $status = automatorwp_pipedrive_check_settings_status(['consumer_key' => $consumer_key]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API consumer_key
    $settings[$prefix . 'consumer_key'] = $consumer_key;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-pipedrive';

    wp_send_json_success( array(
        'message' => __( 'Successfully connected to Pipedrive', 'automatorwp-pipedrive' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_pipedrive_authorize',  'automatorwp_pipedrive_ajax_authorize' );