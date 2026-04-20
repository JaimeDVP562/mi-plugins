<?php

/**
 * Ajax functions for DeepSeek.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register the AJAX handler for the "Authorize" button
 * The action name must match the one used in the JS file
 */
add_action('wp_ajax_automatorwp_deepseek_authorize', 'automatorwp_deepseek_ajax_authorize');

/**
 * AJAX handler for DeepSeek authorization/verification.
 */
function automatorwp_deepseek_ajax_authorize()
{
    // Security check using AutomatorWP's standard admin nonce
    check_ajax_referer('automatorwp_admin', 'nonce');

    // Capability check for security
    if (! current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('You are not allowed to perform this action.', 'automatorwp-deepseek'),
        ));
    }

    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';

    if (empty($token)) {
        wp_send_json_error(array(
            'message' => __('API token is required to connect with DeepSeek', 'automatorwp-deepseek'),
        ));
    }

    /**
     * Validate token WITHOUT persisting first.
     * We use the /models endpoint to verify the API Key is valid.
     */
    $response = wp_remote_get(
        'https://api.deepseek.com/models',
        array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
        )
    );

    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => $response->get_error_message(),
        ));
    }

    $code = (int) wp_remote_retrieve_response_code($response);

    // If the API returns 200, the token is valid
    if ($code !== 200) {
        wp_send_json_error(array(
            'message' => __('Invalid API token. Please check your credentials.', 'automatorwp-deepseek'),
        ));
    }

    /**
     * Persist token only on success using our helper function in functions.php
     */
    if (function_exists('automatorwp_deepseek_update_settings')) {
        automatorwp_deepseek_update_settings(array(
            'automatorwp_deepseek_token' => $token,
        ));
    } else {
        // Fallback to standard WordPress option if helper is missing
        update_option('automatorwp_deepseek_token', $token);
    }

    // Redirect back to the DeepSeek settings tab
    $redirect_url = admin_url('admin.php?page=automatorwp_settings&tab=deepseek');

    wp_send_json_success(array(
        'message'      => __('Connection successful! Your DeepSeek token is valid.', 'automatorwp-deepseek'),
        'redirect_url' => $redirect_url,
    ));
}
