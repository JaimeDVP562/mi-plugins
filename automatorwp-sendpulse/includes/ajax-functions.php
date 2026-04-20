<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Handler to save OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_sendpulse_ajax_save_oauth_credentials() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Capability check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    $prefix = "automatorwp_sendpulse_";

    /* sanitize incoming data */
    $application_id = sanitize_text_field( $_POST["application_id"] );
    $application_secret = sanitize_text_field( $_POST["application_secret"] );

    if ( $application_id === '' && $application_secret === '' ) {
        // return error one of the field missing
        wp_send_json_error();
    } else {
        // DO NOT store secrets in the autoloaded `automatorwp_settings` option.
        // Store credentials in individual options with autoload disabled.
        automatorwp_sendpulse_set_option_noautoload( $prefix . 'application_id', $application_id );
        automatorwp_sendpulse_set_option_noautoload( $prefix . 'application_secret', $application_secret );

        wp_send_json_success();
    }
}
add_action( 'wp_ajax_automatorwp_sendpulse_save_oauth_credentials', 'automatorwp_sendpulse_ajax_save_oauth_credentials' );

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_sendpulse_ajax_authorize() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Capability check
    if ( ! current_user_can( 'manage_options' ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[automatorwp_sendpulse] ajax_authorize insufficient permissions for user id: ' . get_current_user_id() );
        }
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
        return;
    }

    $prefix = 'automatorwp_sendpulse_';

    $application_id = sanitize_text_field( $_POST['application_id'] );
    $application_secret = sanitize_text_field( $_POST['application_secret'] );

    // credentials received (not logged)

    if ( empty( $application_id ) || empty( $application_secret ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[automatorwp_sendpulse] ajax_authorize missing application_id or application_secret' );
        }
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with sendpulse', 'automatorwp-sendpulse' ) ) );
        return;
    }

    // Basic validation passed — save credentials in no-autoload options
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'application_id', $application_id );
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'application_secret', $application_secret );

    // Perform Client Credentials flow server-side
    $token_url = 'https://api.sendpulse.com/oauth/access_token';

    $response = wp_remote_post( $token_url, array(
        'body'    => array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $application_id,
            'client_secret' => $application_secret,
        ),
        'timeout' => 20,
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => __( 'SendPulse token request failed.', 'automatorwp-sendpulse' ) . ' ' . $response->get_error_message() ) );
        return;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( 200 !== intval( $code ) || ! isset( $data['access_token'] ) ) {
        $msg = isset( $data['error_description'] ) ? $data['error_description'] : ( isset( $data['error'] ) ? $data['error'] : __( 'Invalid response from SendPulse.', 'automatorwp-sendpulse' ) );
        wp_send_json_error( array( 'message' => sprintf( __( 'Failed to obtain access token: %s', 'automatorwp-sendpulse' ), $msg ) ) );
        return;
    }

    $access_token = sanitize_text_field( $data['access_token'] );

    // Save token and mark access as valid (no-autoload)
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_token', $access_token );
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_valid', true );
    if ( isset( $data['expires_in'] ) ) {
        automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_expires_in', intval( $data['expires_in'] ) );
    }
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_token_obtained_at', time() );

    wp_send_json_success( array( 'message' => __( 'Connected to SendPulse successfully.', 'automatorwp-sendpulse' ) ) );
}
add_action( 'wp_ajax_automatorwp_sendpulse_authorize',  'automatorwp_sendpulse_ajax_authorize' );

/**
 * AJAX: List addressbooks (address books)
 */
function automatorwp_sendpulse_ajax_list_addressbooks() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    $result = automatorwp_sendpulse_request( 'GET', '/addressbooks' );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    // Normalize response into array of id/name
    $items = array();
    if ( isset( $result['data'] ) && is_array( $result['data'] ) ) {
        $items = $result['data'];
    } elseif ( isset( $result['data']['data'] ) && is_array( $result['data']['data'] ) ) {
        $items = $result['data']['data'];
    } elseif ( is_array( $result ) ) {
        $items = $result;
    }

    $books = array();
    foreach ( $items as $item ) {
        $id = isset( $item['id'] ) ? $item['id'] : ( isset( $item['_id'] ) ? $item['_id'] : ( isset( $item['addressbook_id'] ) ? $item['addressbook_id'] : '' ) );
        $name = isset( $item['name'] ) ? $item['name'] : ( isset( $item['title'] ) ? $item['title'] : ( isset( $item['addressbook'] ) ? $item['addressbook'] : '' ) );
        if ( empty( $id ) ) {
            continue;
        }
        $books[] = array( 'id' => $id, 'name' => $name );
    }

    // Prepare select2-compatible results (id/text) while keeping legacy key for existing callers
    $results = array();
    foreach ( $books as $b ) {
        $results[] = array( 'id' => $b['id'], 'text' => $b['name'] );
    }

    // Allow core helper to inject extra options (none/custom)
    $results = automatorwp_ajax_parse_extra_options( $results );

    wp_send_json_success( array( 'addressbooks' => $books, 'results' => $results ) );

}
add_action( 'wp_ajax_automatorwp_sendpulse_list_addressbooks', 'automatorwp_sendpulse_ajax_list_addressbooks' );

/**
 * AJAX: List subscribers from an addressbook
 */
function automatorwp_sendpulse_ajax_list_subscribers() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    $addressbook_id = isset( $_POST['addressbook_id'] ) ? sanitize_text_field( wp_unslash( $_POST['addressbook_id'] ) ) : '';
    $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
    $per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 50;

    if ( empty( $addressbook_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Addressbook ID is required.', 'automatorwp-sendpulse' ) ) );
    }

    $result = automatorwp_sendpulse_list_subscribers( $addressbook_id, $page, $per_page );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success( array( 'data' => $result ) );

}
add_action( 'wp_ajax_automatorwp_sendpulse_list_subscribers', 'automatorwp_sendpulse_ajax_list_subscribers' );

/**
 * AJAX: Remove a subscriber from an addressbook
 */
function automatorwp_sendpulse_ajax_remove_subscriber() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $addressbook_id = isset( $_POST['addressbook_id'] ) && $_POST['addressbook_id'] !== '' ? sanitize_text_field( wp_unslash( $_POST['addressbook_id'] ) ) : null;

    if ( empty( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Email is required', 'automatorwp-sendpulse' ) ) );
    }

    $result = automatorwp_sendpulse_remove_subscriber( $email, $addressbook_id );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success( array( 'data' => $result ) );

}
add_action( 'wp_ajax_automatorwp_sendpulse_remove_subscriber', 'automatorwp_sendpulse_ajax_remove_subscriber' );

/**
 * Note: Temporary test and diagnostic endpoints (test_api, test_add_subscriber,
 * remove_subscriber_everywhere, diag_remove_subscriber) were removed.
 */


/**
 * AJAX: Generate a secure webhook token and save it (no-autoload)
 */
function automatorwp_sendpulse_ajax_generate_webhook_token() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    // Generate secure token
    try {
        $token = bin2hex( random_bytes( 32 ) );
    } catch ( Exception $e ) {
        // Fallback
        $token = wp_generate_password( 64, false, false );
    }

    automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_webhook_token', $token );

    wp_send_json_success( array( 'token' => $token ) );

}
add_action( 'wp_ajax_automatorwp_sendpulse_generate_webhook_token', 'automatorwp_sendpulse_ajax_generate_webhook_token' );


/**
 * AJAX: Test webhook by sending a POST to the REST endpoint using stored token/secret
 */
function automatorwp_sendpulse_ajax_test_webhook() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'automatorwp-sendpulse' ) ) );
    }

    $prefix = 'automatorwp_sendpulse_';
    $token = automatorwp_sendpulse_get_option( 'webhook_token', '' );
    $secret = automatorwp_sendpulse_get_option( 'webhook_secret', '' );

    $endpoint = rest_url( 'automatorwp-sendpulse/v1/webhook' );

    $payload = array(
        'event' => 'test',
        'email' => 'test@example.com',
        'timestamp' => time(),
    );

    $body = wp_json_encode( $payload );

    $headers = array( 'Content-Type' => 'application/json' );

    if ( ! empty( $token ) ) {
        $headers['X-SendPulse-Token'] = $token;
    }

    if ( ! empty( $secret ) ) {
        // compute hex signature and include
        $sig_hex = hash_hmac( 'sha256', $body, $secret );
        $headers['X-SendPulse-Signature'] = $sig_hex;
    }

    $response = wp_remote_post( $endpoint, array( 'headers' => $headers, 'body' => $body, 'timeout' => 20 ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $resp_body = wp_remote_retrieve_body( $response );

    wp_send_json_success( array( 'code' => $code, 'body' => $resp_body ) );

}
add_action( 'wp_ajax_automatorwp_sendpulse_test_webhook', 'automatorwp_sendpulse_ajax_test_webhook' );
