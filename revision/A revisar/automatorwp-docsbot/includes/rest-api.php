<?php
/**
 * Rest API
 *
 * @package     AutomatorWP\DocsBot\Rest_API
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the DocsBot webhook endpoint on the WordPress REST API
 *
 * @since 1.0.0
 */
function automatorwp_docsbot_rest_api_init() {

    register_rest_route( 'automatorwp-docsbot/v1', '/webhook', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'automatorwp_docsbot_rest_api_cb',
        'permission_callback' => '__return_true',
    ) );

}
add_action( 'rest_api_init', 'automatorwp_docsbot_rest_api_init' );

/**
 * Callback to handle incoming DocsBot webhook requests.
 * Validates the HMAC-SHA256 signature and dispatches a WordPress action per event type.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request
 *
 * @return WP_REST_Response
 */
function automatorwp_docsbot_rest_api_cb( WP_REST_Request $request ) {

    $webhook_secret = automatorwp_docsbot_get_option( 'webhook_secret', '' );

    // Validate signature only when a secret is configured
    if( ! empty( $webhook_secret ) ) {

        $signature = $request->get_header( 'x_docsbot_signature' );
        $raw_body  = $request->get_body();
        $expected  = hash_hmac( 'sha256', $raw_body, $webhook_secret );

        if( ! hash_equals( $expected, (string) $signature ) ) {
            return new WP_REST_Response( array( 'success' => false, 'message' => __( 'Invalid signature', 'automatorwp-docsbot' ) ), 401 );
        }

    }

    $event   = $request->get_header( 'x_docsbot_event' );
    $payload = $request->get_json_params();

    if( empty( $event ) || empty( $payload ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => __( 'Missing event or payload', 'automatorwp-docsbot' ) ), 400 );
    }

    switch( $event ) {

        case 'lead.created':
            do_action( 'automatorwp_docsbot_lead_created', $payload );
            break;

        case 'conversation.escalated':
            do_action( 'automatorwp_docsbot_conversation_escalated', $payload );
            break;

        case 'conversation.rated':
            do_action( 'automatorwp_docsbot_conversation_rated', $payload );
            break;

        case 'deep_research.done':
            do_action( 'automatorwp_docsbot_deep_research_done', $payload );
            break;

    }

    return new WP_REST_Response( array( 'success' => true ), 200 );

}
