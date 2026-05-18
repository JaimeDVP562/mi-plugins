<?php
/**
 * Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get AutomatorWP settings array.
 */
function automatorwp_mistral_ai_get_settings() {
    $settings = get_option( 'automatorwp_settings', array() );
    return is_array( $settings ) ? $settings : array();
}

/**
 * Persist AutomatorWP settings (merge).
 */
function automatorwp_mistral_ai_update_settings( $new_settings ) {

    $settings = get_option( 'automatorwp_settings', array() );
    if ( ! is_array( $settings ) ) {
        $settings = array();
    }

    foreach ( $new_settings as $key => $value ) {
        $settings[ $key ] = $value;
    }

    update_option( 'automatorwp_settings', $settings );
}

/**
 * Get saved API token.
 */
function automatorwp_mistral_ai_get_token() {
    $settings = automatorwp_mistral_ai_get_settings();
    return isset( $settings['automatorwp_mistral_ai_token'] ) ? sanitize_text_field( $settings['automatorwp_mistral_ai_token'] ) : '';
}

/**
 * Get text model (settings field).
 */
function automatorwp_mistral_ai_get_text_model() {
    $settings = automatorwp_mistral_ai_get_settings();

    if ( ! empty( $settings['automatorwp_mistral_ai_model'] ) ) {
        return sanitize_text_field( $settings['automatorwp_mistral_ai_model'] );
    }

    return 'mistral-large-latest';
}

/**
 * Basic request wrapper.
 */
function automatorwp_mistral_ai_request( $method, $endpoint, $body = null, $token_override = '' ) {

    $token = $token_override !== '' ? $token_override : automatorwp_mistral_ai_get_token();

    if ( empty( $token ) ) {
        return new WP_Error( 'mistral_ai_missing_token', 'Missing Mistral API token.' );
    }

    $url = 'https://api.mistral.ai' . $endpoint;

    $args = array(
        'method'  => $method,
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
    );

    if ( $body !== null ) {
        $args['body'] = wp_json_encode( $body );
    }

    return wp_remote_request( $url, $args );
}

/**
 * Generate a chat completion text with Mistral.
 *
 * Returns generated text on success, or false on error.
 */
function automatorwp_mistral_chat_completion( $prompt ) {

    $prompt = is_string( $prompt ) ? trim( $prompt ) : '';

    if ( $prompt === '' ) {
        return false;
    }

    $model = automatorwp_mistral_ai_get_text_model();

    // Mistral chat-completions style payload (OpenAI-like)
    $payload = array(
        'model'    => $model,
        'messages' => array(
            array(
                'role'    => 'user',
                'content' => $prompt,
            ),
        ),
        'temperature' => 0.7,
    );

    $response = automatorwp_mistral_ai_request( 'POST', '/v1/chat/completions', $payload );

    if ( is_wp_error( $response ) ) {
        return false;
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( $code < 200 || $code >= 300 ) {
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $json = json_decode( $body, true );

    if ( ! is_array( $json ) ) {
        return false;
    }

    // Expected: choices[0].message.content
    if ( isset( $json['choices'][0]['message']['content'] ) && is_string( $json['choices'][0]['message']['content'] ) ) {
        return trim( $json['choices'][0]['message']['content'] );
    }

    // Fallbacks (some APIs may return different shape)
    if ( isset( $json['choices'][0]['text'] ) && is_string( $json['choices'][0]['text'] ) ) {
        return trim( $json['choices'][0]['text'] );
    }

    return false;
}

/**
 * Generate an image with Mistral and upload to WordPress media library.
 *
 * IMPORTANT:
 * - Image generation in Mistral can vary by product/endpoint and your previous implementation used Agents API.
 * - To avoid breaking production, this function is "pluggable" via filter.
 *
 * Returns the final image URL (string) on success, or false on error.
 */
function automatorwp_mistral_generate_image_and_upload( $prompt ) {

    $prompt = is_string( $prompt ) ? trim( $prompt ) : '';
    if ( $prompt === '' ) {
        return false;
    }

    /**
     * Allow overriding image generation implementation (recommended).
     * Your previous Agents API implementation can be moved here by hooking this filter.
     *
     * Return: string image URL or false
     */
    $maybe = apply_filters( 'automatorwp_mistral_ai_generate_image_url', false, $prompt );

    if ( is_string( $maybe ) && $maybe !== '' ) {
        return $maybe;
    }

    // Default: no-op (safe). Implement your real image flow via the filter above.
    return false;
}
