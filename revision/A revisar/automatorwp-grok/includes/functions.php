<?php

/**
 * Main functions for Grok integration
 */

if (! defined('ABSPATH')) exit;

/**
 * Perform a request to the xAI (Grok) API
 * * @param string $prompt The user input
 * @param string $model  The model to use (default: grok-2-1212)
 * @return string|WP_Error The AI response or error object
 */
function automatorwp_grok_api_request($prompt, $model = 'grok-2-1212')
{

    $api_key = get_option('automatorwp_grok_api_key');

    if (empty($api_key)) {
        return new WP_Error('missing_api_key', __('Grok API Key is not configured.', 'automatorwp-grok'));
    }

    // xAI API endpoint for chat completions
    $url = 'https://api.x.ai/v1/chat/completions';

    $body = array(
        'model'    => $model,
        'messages' => array(
            array(
                'role'    => 'system',
                'content' => 'You are a helpful assistant integrated into a WordPress site.'
            ),
            array(
                'role'    => 'user',
                'content' => $prompt
            )
        ),
        'temperature' => 0.7,
    );

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode($body),
        'timeout' => 60, // Higher timeout for AI processing
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_code !== 200) {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : 'Unknown API error';
        return new WP_Error('api_error', $error_message);
    }

    // Return the content of the message from the first choice
    return $response_body['choices'][0]['message']['content'] ?? '';
}
