<?php
/**
 * Ajax Functions
 * 
 * @package AutomatorWP\Mailmint\Ajax_Functions
 * @author  AutomatorWP
 * @since   0.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_automatorwp_mailmint_authorize', 'automatorwp_mailmint_ajax_authorize' );
/**
 * Authorize Ajax Mailmint
 * 
 * @since   0.1.0
 * 
 * @return  void
 */
function automatorwp_mailmint_ajax_authorize() {

    // Security Check nonce
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    if ( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        // Fail JSON message 'No permission'
        wp_send_json_error( __( 'No permission', 'automatorwp-funnels-mail-mint' ) );
    
    }

    // Selected Auth method
    $auth_method = isset( $_POST['auth_method'] ) ? sanitize_text_field( $_POST['auth_method'] ) : '';
    // Mailmint API key
    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
    // Mailmint API base
    $api_base = isset( $_POST['api_base'] ) ? esc_url_raw( $_POST['api_base'] ) : '';

    // Save options settings
    $settings = get_option( 'automatorwp_settings', array() );
    $settings['automatorwp_mailmint_auth_method'] = $auth_method;
    $settings['automatorwp_mailmint_api_key'] = $api_key;
    $settings['automatorwp_mailmint_api_base'] = $api_base;
    // Update options settings
    update_option( 'automatorwp_settings', $settings );

    if ( $auth_method === 'internal' ) {
        // Accept multiple possible function names used by different Mail Mint versions
        $ok = function_exists( 'mailmint_create_single_contact' ) || function_exists( 'mailmint_create_multiple_contacts' ) || function_exists( 'mailmint_add_contact' ) || function_exists( 'mrm_add_contact' );
        if ( ! $ok ) { // Fail JSON message 'No internal Mail Mint functions detected.'
            wp_send_json_error( array( 'message' => __( 'No internal Mail Mint functions detected. Switch to API mode or ensure Mail Mint plugin exposes functions.', 'automatorwp-funnels-mail-mint' ) ) );
        }
    } elseif ( $auth_method === 'api' ) {
        if ( empty( $api_key ) || empty( $api_base ) ) { // Fail JSON message 'API Key and base URL required for API auth'
            wp_send_json_error( array( 'message' => __( 'API key and base URL required for API auth', 'automatorwp-funnels-mail-mint' ) ) );
        }
        // basic ping test (endpoint may differ)
        $resp = automatorwp_mailmint_api_request( 'GET', rtrim( $api_base, '/' ) . '/ping', array(), $api_key );
        $code = is_wp_error( $resp ) ? 0 : wp_remote_retrieve_response_code( $resp );
        if ( $code >= 200 && $code < 300 ) { // Success JSON message 'Credentials saved and validated'
            wp_send_json_success( array( 'message' => __( 'Credentials saved and validated', 'automatorwp-funnels-mail-mint' ) ) );
        } else { // Fail JSON message 'API validation failed'
            wp_send_json_error( array( 'message' => __( 'API validation failed (check base URL/auth).', 'automatorwp-funnels-mail-mint' ), 'code' => $code ) );
        }
    }

    // Success JSON message 'Credentials saved'
    wp_send_json_success( array( 'message' => __( 'Credentials saved', 'automatorwp-funnels-mail-mint' ) ) );
}
