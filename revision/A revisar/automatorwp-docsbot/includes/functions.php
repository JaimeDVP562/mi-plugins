<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\DocsBot\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// -------------------------------------------------------
// API Helpers
// -------------------------------------------------------
//
// DocsBot exposes two separate base URLs:
//   - https://api.docsbot.ai  → Chat Agent and Semantic Search (public-facing)
//   - https://docsbot.ai/api  → Admin operations: sources, conversations, leads
//
// Use automatorwp_docsbot_api_request()   for Chat/Search endpoints.
// Use automatorwp_docsbot_admin_request() for Admin endpoints.

/**
 * Get the DocsBot API credentials from settings.
 * Returns false if any required field is missing.
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_docsbot_get_api() {

    $api_key = automatorwp_docsbot_get_option( 'api_key', '' );
    $team_id = automatorwp_docsbot_get_option( 'team_id', '' );
    $bot_id  = automatorwp_docsbot_get_option( 'bot_id', '' );

    if( empty( $api_key ) || empty( $team_id ) || empty( $bot_id ) ) {
        return false;
    }

    return array(
        'api_key' => $api_key,
        'team_id' => $team_id,
        'bot_id'  => $bot_id,
    );

}

/**
 * Make an authenticated request to the DocsBot Chat/Search API.
 *
 * @since 1.0.0
 *
 * @param string $endpoint  Endpoint path, e.g. 'chat-agent' or 'search'
 * @param array  $body      JSON body as PHP array
 *
 * @return array|WP_Error Decoded response body or WP_Error on failure
 */
function automatorwp_docsbot_api_request( $endpoint, $body ) {

    $api = automatorwp_docsbot_get_api();

    if( ! $api ) {
        return new WP_Error( 'docsbot_not_configured', __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' ) );
    }

    $url = sprintf(
        'https://api.docsbot.ai/teams/%s/bots/%s/%s',
        rawurlencode( $api['team_id'] ),
        rawurlencode( $api['bot_id'] ),
        ltrim( $endpoint, '/' )
    );

    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api['api_key'],
        ),
        'body'    => wp_json_encode( $body ),
        'timeout' => 30,
    );

    $response = wp_remote_post( $url, $args );

    if( is_wp_error( $response ) ) {
        return $response;
    }

    $status = wp_remote_retrieve_response_code( $response );
    $data   = json_decode( wp_remote_retrieve_body( $response ), true );

    if( $status < 200 || $status >= 300 ) {
        $message = isset( $data['message'] ) ? $data['message'] : wp_remote_retrieve_body( $response );
        if( empty( $message ) ) {
            $message = __( 'Unknown API error.', 'automatorwp-docsbot' );
        }
        return new WP_Error( 'docsbot_api_error', sprintf( '[HTTP %d] %s', $status, $message ), array( 'status' => $status ) );
    }

    return $data;

}

/**
 * Make an authenticated request to the DocsBot Admin API.
 * Base URL: https://docsbot.ai/api/ (different from Chat/Search API)
 *
 * @since 1.0.0
 *
 * @param string      $endpoint  Endpoint path, e.g. 'sources' or 'sources/abc123'
 * @param string      $method    HTTP method: GET, POST, PUT, DELETE
 * @param array|null  $body      JSON body as PHP array (optional)
 *
 * @return array|WP_Error Decoded response body or WP_Error on failure
 */
function automatorwp_docsbot_admin_request( $endpoint, $method = 'GET', $body = null ) {

    $api = automatorwp_docsbot_get_api();

    if( ! $api ) {
        return new WP_Error( 'docsbot_not_configured', __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' ) );
    }

    $url = sprintf(
        'https://docsbot.ai/api/teams/%s/bots/%s/%s',
        rawurlencode( $api['team_id'] ),
        rawurlencode( $api['bot_id'] ),
        ltrim( $endpoint, '/' )
    );

    $args = array(
        'method'  => $method,
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api['api_key'],
        ),
        'timeout' => 30,
    );

    if( ! is_null( $body ) ) {
        $args['body'] = wp_json_encode( $body );
    }

    $response = wp_remote_request( $url, $args );

    if( is_wp_error( $response ) ) {
        return $response;
    }

    $status = wp_remote_retrieve_response_code( $response );
    $data   = json_decode( wp_remote_retrieve_body( $response ), true );

    if( $status < 200 || $status >= 300 ) {
        $message = isset( $data['message'] ) ? $data['message'] : wp_remote_retrieve_body( $response );
        if( empty( $message ) ) {
            $message = __( 'Unknown API error.', 'automatorwp-docsbot' );
        }
        return new WP_Error( 'docsbot_api_error', sprintf( '[HTTP %d] %s', $status, $message ), array( 'status' => $status ) );
    }

    return $data;

}

// -------------------------------------------------------
// Utility
// -------------------------------------------------------

/**
 * Attempt to find a WordPress user by email from a DocsBot webhook payload.
 * Returns 0 if no matching user is found.
 *
 * @since 1.0.0
 *
 * @param array $payload Decoded webhook payload
 *
 * @return int WordPress user ID or 0
 */
function automatorwp_docsbot_get_user_id_from_payload( $payload ) {

    $email = '';

    // lead.created: payload has a top-level 'metadata' key
    if( isset( $payload['metadata']['email'] ) ) {
        $email = sanitize_email( $payload['metadata']['email'] );
    }

    // conversation.escalated / conversation.rated: nested under 'conversation'
    if( empty( $email ) && isset( $payload['conversation']['metadata']['email'] ) ) {
        $email = sanitize_email( $payload['conversation']['metadata']['email'] );
    }

    if( ! empty( $email ) ) {
        $user = get_user_by( 'email', $email );
        if( $user ) {
            return (int) $user->ID;
        }
    }

    // No matching WP user found — fall back to the first administrator
    $admins = get_users( array(
        'role'    => 'administrator',
        'number'  => 1,
        'orderby' => 'ID',
        'order'   => 'ASC',
    ) );

    return ! empty( $admins ) ? (int) $admins[0]->ID : 0;

}
