<?php
/**
 * API helper functions.
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get stored API key.
 *
 * @return string
 */
// `automatorwp_grok_get_api_key()` is provided by includes/functions.php.
if ( ! function_exists( 'automatorwp_grok_get_api_key' ) ) {
    function automatorwp_grok_get_api_key() {
        $key = get_option( 'automatorwp_grok_api_key', '' );
        return is_string( $key ) ? trim( $key ) : '';
    }
}

/**
 * Verify API key (lightweight).
 *
 * @param string $api_key API key.
 * @return array
 */
function automatorwp_grok_verify_api_key( $api_key ) {

    if ( empty( $api_key ) ) {
        error_log( '[automatorwp-grok] verify_api_key: API key is empty.' );
        return new WP_Error( 'grok_no_api_key', __( 'API key not saved.', 'automatorwp-grok' ) );
    }

    $endpoint = untrailingslashit( AUTOMATORWP_GROK_API_BASE_URL ) . '/v1/models';

    $response = wp_remote_get(
        $endpoint,
        array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        error_log( '[automatorwp-grok] verify_api_key: wp_remote_get error: ' . $response->get_error_message() );
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $body   = wp_remote_retrieve_body( $response );
    if ( 200 !== $status ) {
        error_log( sprintf( '[automatorwp-grok] verify_api_key: HTTP %d. Body: %s', $status, $body ) );
    } else {
        error_log( sprintf( '[automatorwp-grok] verify_api_key: HTTP %d. OK', $status ) );
    }

    if ( 200 === $status ) {
        return array(
            'success' => true,
            'message' => __( 'Connected successfully.', 'automatorwp-grok' ),
        );
    }

    return new WP_Error( 'grok_invalid_key', __( 'Invalid API key.', 'automatorwp-grok' ) );
}

/**
 * Generate text using xAI chat completions (OpenAI-compatible).
 *
 * Endpoint: POST /v1/chat/completions
 * Docs: https://docs.x.ai/docs/api-reference (OpenAI compatibility)
 *
 * @param string $prompt Prompt.
 * @param string $model  Model.
 * @return array { success: bool, response: string, message: string }
 */
function automatorwp_grok_generate_text( $prompt, $model ) {

    $api_key = automatorwp_grok_get_api_key();

    if ( empty( $api_key ) ) {
        return new WP_Error( 'grok_no_api_key', __( 'API key not saved.', 'automatorwp-grok' ) );
    }

    $prompt = is_string( $prompt ) ? trim( $prompt ) : '';
    $model  = is_string( $model ) ? trim( $model ) : '';

    if ( '' === $prompt ) {
        return new WP_Error( 'grok_no_prompt', __( 'Prompt is required.', 'automatorwp-grok' ) );
    }

    if ( '' === $model ) {
        $model = 'grok-beta';
    }

    $endpoint = untrailingslashit( AUTOMATORWP_GROK_API_BASE_URL ) . '/v1/chat/completions';

    $payload = array(
        'model'    => $model,
        'messages' => array(
            array(
                'role'    => 'user',
                'content' => $prompt,
            ),
        ),
    );

    $response = wp_remote_post(
        $endpoint,
        array(
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $body   = wp_remote_retrieve_body( $response );
    $data   = json_decode( $body, true );

    if ( 200 !== $status ) {
        return new WP_Error( 'grok_api_error', __( 'Request failed. Please verify your API key and model.', 'automatorwp-grok' ) );
    }

    $text = '';

    // OpenAI-compatible shape: choices[0].message.content
    if ( is_array( $data )
        && isset( $data['choices'][0]['message']['content'] )
        && is_string( $data['choices'][0]['message']['content'] )
    ) {
        $text = trim( $data['choices'][0]['message']['content'] );
    }

    return array(
        'success'  => true,
        'response' => $text,
        'message'  => __( 'OK', 'automatorwp-grok' ),
    );
}
