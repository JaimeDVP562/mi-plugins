```php
<?php
/**
 * GitHub Webhook REST API Endpoint
 * URL: /wp-json/automatorwp/v1/github
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register GitHub webhook endpoint
 */
function automatorwp_github_rest_api_init() {

    register_rest_route(
        'automatorwp/v1',
        '/github',
        array(
            'methods'  => 'POST',
            'callback' => 'automatorwp_github_rest_api_cb',
            'permission_callback' => '__return_true', 
        )
    );

}
add_action( 'rest_api_init', 'automatorwp_github_rest_api_init' );

/**
 * Webhook callback (SECURE)
 */
function automatorwp_github_rest_api_cb( $request ){

    //  Obtener settings
    $settings = get_option('automatorwp_github_settings');
    $secret   = $settings['webhook_secret'] ?? '';

    if ( empty($secret) ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Webhook secret not configured'
        ], 500);
    }

    // Obtener payload RAW
    $raw = $request->get_body();

    if ( empty($raw) ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Empty payload'
        ], 400);
    }

    //  Limitar tamaño (anti abuso)
    if ( strlen($raw) > 1000000 ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Payload too large'
        ], 413);
    }

    // Verificar firma GitHub
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

    if ( empty($signature) ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Missing signature'
        ], 403);
    }

    $expected = 'sha256=' . hash_hmac('sha256', $raw, $secret);

    if ( ! hash_equals($expected, $signature) ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid signature'
        ], 403);
    }

    // Detectar evento
    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

    // Parsear JSON
    $params = json_decode($raw, true);

    // Compatibilidad (payload=...)
    if ( empty($params) && strpos($raw, 'payload=') === 0 ) {

        $encoded = substr($raw, 8);
        $decoded = urldecode($encoded);
        $params  = json_decode($decoded, true);
    }

    if ( ! is_array($params) ) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Invalid JSON'
        ], 400);
    }

   

    $github_action       = $params['action'] ?? '';
    $github_sender_login = $params['sender']['login'] ?? '';

    // Issue events
    if ( $event === 'issues' ) {

        if ( $github_action === 'opened' && isset($params['issue']) ) {
            do_action('automatorwp_github_new_issue', $params);
        }

        if ( $github_action === 'deleted' && isset($params['issue']) ) {
            do_action('automatorwp_github_deleted_issue', $params);
        }
    }

    // Push events
    if ( $event === 'push' && isset($params['commits']) ) {
        do_action('automatorwp_github_push_repo', $params);
    } 

    // =========================

    return new WP_REST_Response([
        'success' => true,
        'event'   => $event,
        'action'  => $github_action,
        'sender'  => $github_sender_login
    ], 200);
}

