<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register GoHighLevel send webhook action.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_register_action_send_webhook()
{
    if (! function_exists('automatorwp_register_action')) {
        return;
    }

    automatorwp_register_action('gohighlevel_send_webhook', array(
        'integration'   => 'gohighlevel',
            'label'         => __('GoHighLevel: Send webhook', 'automatorwp-gohighlevel'),
            'select_option' => __('GoHighLevel: Send webhook', 'automatorwp-gohighlevel'),
            'edit_label'    => __('Send webhook to "{gohighlevel_webhook_url}"', 'automatorwp-gohighlevel'),
        'options'       => array(
            'webhook' => array(
                'from'    => '',
                'default' => '',
                'fields'  => array(
                    'gohighlevel_webhook_url' => array(
                        'name'    => __('Webhook URL', 'automatorwp-gohighlevel'),
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_webhook_method' => array(
                        'name'    => __('HTTP method', 'automatorwp-gohighlevel'),
                        'type'    => 'select',
                        'options' => array(
                            'POST'  => 'POST',
                            'PUT'   => 'PUT',
                            'PATCH' => 'PATCH',
                        ),
                        'default' => 'POST',
                    ),
                    'gohighlevel_webhook_secret' => array(
                        'name'    => __('Secret (optional)', 'automatorwp-gohighlevel'),
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_webhook_custom_body' => array(
                        'name'    => __('Body (JSON)', 'automatorwp-gohighlevel'),
                        'type'    => 'textarea',
                        'default' => '',
                    ),
                    'gohighlevel_webhook_timeout' => array(
                        'name'    => __('Timeout (seconds)', 'automatorwp-gohighlevel'),
                        'type'    => 'number',
                        'default' => 15,
                        'min'     => 3,
                        'step'    => 1,
                    ),
                ),
            ),
        ),
    ));
}
add_action('automatorwp_init', 'awp_gohighlevel_register_action_send_webhook', 25);

/**
 * Execute GoHighLevel send webhook action.
 *
 * @since 1.0.0
 * @param stdClass $action Action object.
 * @param int      $user_id User ID.
 * @param array    $event Event payload.
 * @param array    $action_options Action options.
 * @param stdClass $automation Automation object.
 * @return void
 */
function awp_gohighlevel_execute_action_send_webhook($action, $user_id, $event, $action_options, $automation)
{
    if (! is_object($action) || empty($action->type) || $action->type !== 'gohighlevel_send_webhook') {
        return;
    }

    $webhook_url = isset($action_options['gohighlevel_webhook_url']) ? esc_url_raw(trim((string) $action_options['gohighlevel_webhook_url'])) : '';

    if ($webhook_url === '' || ! wp_http_validate_url($webhook_url)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] webhook canceled: invalid URL');
        }
        return;
    }

    $method = isset($action_options['gohighlevel_webhook_method']) ? strtoupper(trim((string) $action_options['gohighlevel_webhook_method'])) : 'POST';
    if (! in_array($method, array('POST', 'PUT', 'PATCH'), true)) {
        $method = 'POST';
    }

    $timeout = isset($action_options['gohighlevel_webhook_timeout']) ? (int) $action_options['gohighlevel_webhook_timeout'] : 15;
    if ($timeout < 3) {
        $timeout = 3;
    }
    if ($timeout > 60) {
        $timeout = 60;
    }

    $headers = array(
        'Content-Type' => 'application/json',
        'Accept'       => 'application/json',
        'User-Agent'   => 'AutomatorWP-GoHighLevel/' . (defined('AUTOMATORWP_GOHIGHLEVEL_VER') ? AUTOMATORWP_GOHIGHLEVEL_VER : '1.0.0'),
    );

    $secret = isset($action_options['gohighlevel_webhook_secret']) ? trim((string) $action_options['gohighlevel_webhook_secret']) : '';
    if ($secret !== '') {
        $headers['Authorization'] = $secret;
    }

    $custom_body = isset($action_options['gohighlevel_webhook_custom_body']) ? trim((string) $action_options['gohighlevel_webhook_custom_body']) : '';

    if ($custom_body !== '') {
        $decoded = json_decode($custom_body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $payload = $decoded;
        } else {
            $payload = array(
                'raw_body' => $custom_body,
            );
        }
    } else {
        $payload = array(
            'integration' => 'gohighlevel',
            'timestamp'   => current_time('mysql'),
            'site_url'    => home_url('/'),
            'action'      => array(
                'id'   => isset($action->id) ? (int) $action->id : 0,
                'type' => 'gohighlevel_send_webhook',
            ),
            'automation'  => array(
                'id' => (is_object($automation) && isset($automation->id)) ? (int) $automation->id : 0,
            ),
            'user'        => array(
                'id' => (int) $user_id,
            ),
            'event'       => array(
                'trigger'     => isset($event['trigger']) ? sanitize_text_field((string) $event['trigger']) : '',
                'gohighlevel' => isset($event['gohighlevel']) && is_array($event['gohighlevel']) ? $event['gohighlevel'] : array(),
            ),
        );
    }

    $args = array(
        'method'  => $method,
        'headers' => $headers,
        'timeout' => $timeout,
        'body'    => wp_json_encode($payload),
    );

    $response = wp_remote_request($webhook_url, $args);

    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] webhook error: ' . $response->get_error_message());
        }
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        $status = (int) wp_remote_retrieve_response_code($response);
        error_log('[AWP-GoHighLevel] webhook sent status=' . $status . ' url=' . $webhook_url);
    }
}
add_action('automatorwp_execute_action', 'awp_gohighlevel_execute_action_send_webhook', 10, 5);
