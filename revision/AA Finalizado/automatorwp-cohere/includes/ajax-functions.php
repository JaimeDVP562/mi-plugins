<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Cohere\Ajax_Functions
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action.
 * Validates the API key by making a minimal chat request, saves it on success.
 *
 * @since 1.0.0
 */
function automatorwp_cohere_ajax_authorize()
{
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API Key is required to connect with Cohere.', 'automatorwp-cohere' ) ) );
        return;
    }

    // Verify credentials with a lightweight GET request (no token consumption)
    $http = wp_remote_get( 'https://api.cohere.com/v1/models', array(
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Accept'        => 'application/json',
        ),
    ) );

    if ( is_wp_error( $http ) ) {
        wp_send_json_error( array( 'message' => $http->get_error_message() ) );
        return;
    }

    $code = (int) wp_remote_retrieve_response_code( $http );

    if ( $code !== 200 ) {
        $body = json_decode( wp_remote_retrieve_body( $http ), true );
        $msg  = isset( $body['message'] ) ? $body['message']
              : sprintf( __( 'HTTP %d error. Please check your API key.', 'automatorwp-cohere' ), $code );
        wp_send_json_error( array( 'message' => $msg ) );
        return;
    }

    // Save the API key to automatorwp_settings
    $settings = get_option( 'automatorwp_settings', array() );
    $settings['automatorwp_cohere_api_key'] = $api_key;
    update_option( 'automatorwp_settings', $settings );

    wp_send_json_success( array(
        'message'      => __( 'Connected with Cohere successfully.', 'automatorwp-cohere' ),
        'redirect_url' => get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-cohere',
    ) );
}
add_action( 'wp_ajax_automatorwp_cohere_authorize', 'automatorwp_cohere_ajax_authorize' );
