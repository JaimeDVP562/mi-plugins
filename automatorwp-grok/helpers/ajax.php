<?php
/**
 * AJAX handlers.
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX: Verify Grok API key.
 *
 * @return void
 */
function automatorwp_grok_ajax_verify() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error(
            array( 'message' => __( 'You are not allowed to do this.', 'automatorwp-grok' ) ),
            403
        );
    }

    check_ajax_referer( 'automatorwp_grok_verify', 'nonce' );

    $api_key = automatorwp_grok_get_api_key();

    $result = automatorwp_grok_verify_api_key( $api_key );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
    }

    wp_send_json_success( array( 'message' => isset( $result['message'] ) ? $result['message'] : __( 'Connected successfully.', 'automatorwp-grok' ) ) );
}
add_action( 'wp_ajax_automatorwp_grok_verify', 'automatorwp_grok_ajax_verify' );
