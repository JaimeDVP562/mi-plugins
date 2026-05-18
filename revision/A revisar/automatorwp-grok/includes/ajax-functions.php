<?php

/**
 * AJAX functions for Grok integration
 */

if (! defined('ABSPATH')) exit;

/**
 * Handle the authorization request to verify the API Key
 */
add_action('wp_ajax_automatorwp_grok_authorize', 'automatorwp_grok_authorize_callback');

function automatorwp_grok_authorize_callback()
{

    // Check security nonce
    check_ajax_referer('automatorwp_grok_nonce', 'nonce');

    // Get the token from the AJAX request
    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';

    if (empty($token)) {
        wp_send_json_error(array('message' => __('API token is required.', 'automatorwp-grok')));
    }

    // Attempt a simple request to verify the key (listing models or a tiny prompt)
    $url  = 'https://api.x.ai/v1/chat/completions';
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode(array(
            'model'    => 'grok-2-1212',
            'messages' => array(array('role' => 'user', 'content' => 'Say hi')),
            'max_tokens' => 5
        )),
        'timeout' => 15,
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code === 200) {
        // Save the valid token to the database
        update_option('automatorwp_grok_api_key', $token);

        wp_send_json_success(array(
            'message'      => __('Connected successfully with Grok!', 'automatorwp-grok'),
            'redirect_url' => admin_url('admin.php?page=automatorwp_settings&tab=integrations&section=grok')
        ));
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $error_msg = isset($body['error']['message']) ? $body['error']['message'] : __('Invalid API Key. Please try again.', 'automatorwp-grok');

        wp_send_json_error(array('message' => $error_msg));
    }
}
