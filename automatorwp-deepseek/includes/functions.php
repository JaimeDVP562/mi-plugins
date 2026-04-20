<?php

/**
 * DeepSeek Functions
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Get AutomatorWP settings array.
 * * @return array
 */
function automatorwp_deepseek_get_settings()
{
    $settings = get_option('automatorwp_settings', array());
    return is_array($settings) ? $settings : array();
}

/**
 * Persist AutomatorWP settings (merge).
 * * @param array $new_settings
 */
function automatorwp_deepseek_update_settings($new_settings)
{
    $settings = automatorwp_deepseek_get_settings();

    foreach ($new_settings as $key => $value) {
        $settings[$key] = sanitize_text_field($value);
    }

    update_option('automatorwp_settings', $settings);
}

/**
 * Get saved API token.
 * * @return string
 */
function automatorwp_deepseek_get_token()
{
    $settings = automatorwp_deepseek_get_settings();
    return isset($settings['automatorwp_deepseek_token']) ? sanitize_text_field($settings['automatorwp_deepseek_token']) : '';
}

/**
 * Get text model (settings field).
 * * @return string
 */
function automatorwp_deepseek_get_text_model()
{
    $settings = automatorwp_deepseek_get_settings();
    return ! empty($settings['automatorwp_deepseek_model']) ? sanitize_text_field($settings['automatorwp_deepseek_model']) : 'deepseek-chat';
}

/**
 * Basic request wrapper for DeepSeek API.
 * * @param string $method
 * @param string $endpoint
 * @param array|null $body
 * @return array|WP_Error
 */
function automatorwp_deepseek_request($method, $endpoint, $body = null)
{
    $token = automatorwp_deepseek_get_token();

    if (empty($token)) {
        return new WP_Error('deepseek_missing_token', __('Missing DeepSeek API token. Please check settings.', 'automatorwp-deepseek'));
    }

    $url = 'https://api.deepseek.com' . $endpoint;

    $args = array(
        'method'  => $method,
        'timeout' => 60,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ),
    );

    if ($body !== null) {
        $args['body'] = wp_json_encode($body);
    }

    return wp_remote_request($url, $args);
}

/**
 * Generate a chat completion text with DeepSeek.
 *
 * @param string $prompt
 * @param string $model
 * @return string|WP_Error|false
 */
function automatorwp_deepseek_api_request($prompt, $model = '')
{
    $prompt = is_string($prompt) ? trim($prompt) : '';
    if (empty($prompt)) {
        return false;
    }

    // Use provided model or fall back to settings default
    if (empty($model)) {
        $model = automatorwp_deepseek_get_text_model();
    }

    $payload = array(
        'model'    => $model,
        'messages' => array(
            array(
                'role'    => 'user',
                'content' => $prompt,
            ),
        ),
        'stream' => false,
    );

    $response = automatorwp_deepseek_request('POST', '/chat/completions', $payload);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        $error_msg = isset($body['error']['message']) ? $body['error']['message'] : __('DeepSeek API Error', 'automatorwp-deepseek');
        return new WP_Error('deepseek_api_error', $error_msg);
    }

    if (isset($body['choices'][0]['message']['content'])) {
        return trim($body['choices'][0]['message']['content']);
    }

    return false;
}
