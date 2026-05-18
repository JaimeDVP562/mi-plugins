<?php
/**
 * Ajax functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_automatorwp_mistral_authorize', 'automatorwp_mistral_ai_ajax_authorize' );

/**
 * AJAX handler for "Authorize" button.
 */
function automatorwp_mistral_ai_ajax_authorize() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You are not allowed to perform this action.', 'automatorwp-mistral-ai' ),
        ) );
    }

    $token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

    if ( empty( $token ) ) {
        wp_send_json_error( array(
            'message' => __( 'API token is required to connect with Mistral AI', 'automatorwp-mistral-ai' ),
        ) );
    }

    // Validate token WITHOUT persisting first
    $response = wp_remote_get(
        'https://api.mistral.ai/v1/models',
        array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array(
            'message' => $response->get_error_message(),
        ) );
    }

    $code = (int) wp_remote_retrieve_response_code( $response );

    if ( $code !== 200 ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid API token. Please check your credentials.', 'automatorwp-mistral-ai' ),
        ) );
    }

    // Persist token only on success
    automatorwp_mistral_ai_update_settings( array(
        'automatorwp_mistral_ai_token' => $token,
    ) );

    // Redirect back to settings tab (adjust if your panel uses a different tab parameter)
    $redirect_url = admin_url( 'admin.php?page=automatorwp_settings&tab=mistral_ai' );

    wp_send_json_success( array(
        'message'      => __( 'Correct data to connect with Mistral AI', 'automatorwp-mistral-ai' ),
        'redirect_url' => $redirect_url,
    ) );
}
