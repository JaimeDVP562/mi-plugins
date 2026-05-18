<?php
/**
 * Webhooks
 *
 * @package     AutomatorWP\Integrations\GoHighLevel\Webhooks
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Build GoHighLevel webhook endpoint URL.
 *
 * @since 1.0.0
 * @return string
 */
function automatorwp_gohighlevel_get_webhook_url()
{
    return rest_url('automatorwp-gohighlevel/v1/webhook');
}

/**
 * Register REST routes for webhooks.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_register_webhook_routes()
{
    register_rest_route('automatorwp-gohighlevel/v1', '/webhook', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'automatorwp_gohighlevel_handle_webhook',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'automatorwp_gohighlevel_register_webhook_routes');

/**
 * Validate incoming webhook token.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request Request object.
 * @return bool
 */
function automatorwp_gohighlevel_validate_webhook_token($request)
{
    $configured = (string) automatorwp_gohighlevel_get_option('webhook_token', '');

    if ($configured === '') {
        return true;
    }

    $candidates = array(
        (string) $request->get_header('x-webhook-token'),
        (string) $request->get_header('x-ghl-webhook-token'),
        (string) $request->get_header('authorization'),
        (string) $request->get_param('token'),
        (string) $request->get_param('webhook_token'),
    );

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }

        if (stripos($candidate, 'Bearer ') === 0) {
            $candidate = substr($candidate, 7);
        }

        if (hash_equals($configured, trim($candidate))) {
            return true;
        }
    }

    return false;
}

/**
 * Handle incoming GoHighLevel webhook requests.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function automatorwp_gohighlevel_handle_webhook($request)
{
    if (! automatorwp_gohighlevel_validate_webhook_token($request)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => __('Invalid webhook token', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
        ), 401);
    }

    $payload = $request->get_json_params();

    if (! is_array($payload)) {
        $payload = $request->get_params();
    }

    if (! is_array($payload)) {
        $payload = array();
    }

    $user_id = 0;
    if (function_exists('awp_gohighlevel_resolve_user_id')) {
        $user_id = (int) awp_gohighlevel_resolve_user_id(0);
    }

    do_action('awp_gohighlevel_webhook_event', $payload, $user_id);

    return new WP_REST_Response(array(
        'success' => true,
        'message' => __('Webhook processed', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
    ), 200);
}
