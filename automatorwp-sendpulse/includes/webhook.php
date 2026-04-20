<?php
/**
 * REST webhook receiver and HMAC/token verification for SendPulse
 *
 * @package AutomatorWP\Integrations\Sendpulse\Webhook
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register REST route to receive webhooks
 */
function automatorwp_sendpulse_register_webhook_route() {
    register_rest_route( 'automatorwp-sendpulse/v1', '/webhook', array(
        'methods'  => 'POST',
        'callback' => 'automatorwp_sendpulse_handle_webhook',
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'automatorwp_sendpulse_register_webhook_route' );


/**
 * Determine event name from payload
 */
function automatorwp_sendpulse_determine_event_name( $payload ) {
    if ( is_array( $payload ) ) {
        $candidates = array( 'event', 'type', 'action', 'eventName', 'name' );
        foreach ( $candidates as $c ) {
            if ( isset( $payload[ $c ] ) && ! empty( $payload[ $c ] ) ) {
                return sanitize_text_field( (string) $payload[ $c ] );
            }
        }
    }
    return 'unknown';
}


/**
 * Verify HMAC signature or token fallback
 */
function automatorwp_sendpulse_verify_webhook( $raw_body ) {
    $secret = automatorwp_sendpulse_get_option( 'webhook_secret', '' );
    $token  = automatorwp_sendpulse_get_option( 'webhook_token', '' );

    // Try HMAC verification if secret provided
    if ( ! empty( $secret ) ) {
        $header_sig = '';
        if ( isset( $_SERVER['HTTP_X_SENDPULSE_SIGNATURE'] ) ) {
            $header_sig = $_SERVER['HTTP_X_SENDPULSE_SIGNATURE'];
        } elseif ( isset( $_SERVER['HTTP_X_SIGNATURE'] ) ) {
            $header_sig = $_SERVER['HTTP_X_SIGNATURE'];
        }

        if ( $header_sig ) {
            // Try hex
            $calc_hex = hash_hmac( 'sha256', $raw_body, $secret );
            if ( hash_equals( $calc_hex, $header_sig ) ) {
                return true;
            }
            // Try base64
            $calc_bin = hash_hmac( 'sha256', $raw_body, $secret, true );
            if ( hash_equals( base64_encode( $calc_bin ), $header_sig ) ) {
                return true;
            }
        }
    }

    // Fallback to token header or ?token= query
    if ( ! empty( $token ) ) {
        $header_token = isset( $_SERVER['HTTP_X_SENDPULSE_TOKEN'] ) ? $_SERVER['HTTP_X_SENDPULSE_TOKEN'] : ( isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '' );
        if ( $header_token && hash_equals( $token, $header_token ) ) {
            return true;
        }
    }

    return false;
}


/**
 * REST callback to handle webhook
 */
function automatorwp_sendpulse_handle_webhook( WP_REST_Request $request ) {
    $raw_body = $request->get_body();

    if ( ! automatorwp_sendpulse_verify_webhook( $raw_body ) ) {
        return new WP_REST_Response( array( 'error' => 'Invalid signature or token' ), 403 );
    }

    $data = json_decode( $raw_body, true );
    if ( is_null( $data ) ) {
        // Not valid JSON, try parsing as form data
        $data = $request->get_params();
    }

    $event_name = automatorwp_sendpulse_determine_event_name( $data );

    // Fire a generic event and a specific event
    do_action( 'automatorwp_sendpulse_event', $event_name, $data );
    do_action( 'automatorwp_sendpulse_' . $event_name, $data );

    // Store last webhook payload for debugging (no autoload)
    automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_last_webhook', $data );

    return new WP_REST_Response( array( 'success' => true ), 200 );
}
