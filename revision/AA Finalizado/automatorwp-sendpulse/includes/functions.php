<?php
/**
 * Core Functions & API Wrapper
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Access Token silently (Background Auth)
 */
function automatorwp_sendpulse_get_token() {
    $token = get_transient( 'automatorwp_sendpulse_api_token' );
    if ( false !== $token ) {
        return $token;
    }

    // ¡MAGIA! Leer las credenciales directamente del plugin oficial de SendPulse
    $sp_api_settings = get_option( 'sp_api_setting', array() );
    $client_id       = isset( $sp_api_settings['client_id'] ) ? sanitize_text_field( $sp_api_settings['client_id'] ) : '';
    $client_secret   = isset( $sp_api_settings['client_secret'] ) ? sanitize_text_field( $sp_api_settings['client_secret'] ) : '';

    if ( empty( $client_id ) || empty( $client_secret ) ) {
        return new WP_Error( 'no_credentials', __( 'SendPulse credentials not found in the official plugin.', 'automatorwp-sendpulse' ) );
    }

    $response = wp_remote_post( 'https://api.sendpulse.com/oauth/access_token', array(
        'body' => array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ),
    ) );

    if ( is_wp_error( $response ) ) return $response;

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $body['access_token'] ) ) {
        $expires = isset( $body['expires_in'] ) ? (int) $body['expires_in'] - 60 : 3500;
        set_transient( 'automatorwp_sendpulse_api_token', $body['access_token'], $expires );
        return $body['access_token'];
    }

    return new WP_Error( 'auth_error', __( 'Failed to authenticate with SendPulse.', 'automatorwp-sendpulse' ) );
}

/**
 * Master API Request Wrapper
 */
function automatorwp_sendpulse_request( $method, $endpoint, $args = array() ) {
    $token = automatorwp_sendpulse_get_token();
    
    if ( is_wp_error( $token ) ) return $token;

    $url = 'https://api.sendpulse.com/' . ltrim( $endpoint, '/' );
    $defaults = array(
        'method'  => strtoupper( $method ),
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ),
    );
    $args = wp_parse_args( $args, $defaults );

    if ( isset( $args['body'] ) && is_array( $args['body'] ) ) {
        $args['body'] = wp_json_encode( $args['body'] );
    }

    $response = wp_remote_request( $url, $args );
    if ( is_wp_error( $response ) ) return $response;

    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body, true );
}

/**
 * Action Callback: Add/Update Subscriber
 */
function automatorwp_sendpulse_add_subscriber( $email, $first_name = '', $last_name = '', $addressbook_id = null, $extra_variables = array() ) {
    if ( empty( $email ) || empty( $addressbook_id ) ) {
        return new WP_Error( 'missing_data', __( 'Email and Addressbook are required.', 'automatorwp-sendpulse' ) );
    }

    $variables = array();
    if ( ! empty( $first_name ) ) $variables['first_name'] = $first_name;
    if ( ! empty( $last_name ) ) $variables['last_name'] = $last_name;
    
    if ( ! empty( $extra_variables ) ) $variables = array_merge( $variables, $extra_variables );

    // Envolver los datos en la clave 'emails' exigida por la API de SendPulse
    $payload = array(
        'emails' => array(
            array(
                'email'     => $email,
                'variables' => $variables
            )
        )
    );
    
    return automatorwp_sendpulse_request( 'POST', "/addressbooks/{$addressbook_id}/emails", array( 'body' => $payload ) );
}

/**
 * Action Callback: Remove Subscriber
 */
function automatorwp_sendpulse_remove_subscriber( $email, $addressbook_id = null ) {
    if ( empty( $email ) || empty( $addressbook_id ) ) return new WP_Error( 'missing_data', __( 'Email and Addressbook are required.', 'automatorwp-sendpulse' ) );
    
    $endpoint = "/addressbooks/{$addressbook_id}/emails";
    $body = array( 'emails' => array( $email ) );
    return automatorwp_sendpulse_request( 'DELETE', $endpoint, array( 'body' => $body ) );
}

/**
 * REST Route for Webhooks
 */
add_action( 'rest_api_init', function() {
    register_rest_route( 'automatorwp-sendpulse/v1', '/webhook', array(
        'methods'             => 'POST',
        'callback'            => 'automatorwp_sendpulse_webhook_handler',
        'permission_callback' => '__return_true',
    ) );
});

function automatorwp_sendpulse_webhook_handler( $request ) {
    $data = $request->get_json_params();
    if ( empty( $data ) ) $data = $request->get_params();

    $event_name = isset( $data[0]['event'] ) ? sanitize_text_field( $data[0]['event'] ) : 'unknown';
    do_action( 'automatorwp_sendpulse_event', $event_name, $data );
    do_action( 'automatorwp_sendpulse_' . $event_name, $data );

    return new WP_REST_Response( array( 'status' => 'success' ), 200 );
}

/**
 * Helper function to fetch and cache SendPulse Addressbooks
 * @return array
 */
function automatorwp_sendpulse_get_addressbooks_options() {
    $options = array( '' => __( 'Select an addressbook...', 'automatorwp-sendpulse' ) );
    
    // ¡MAGIA! Leer las credenciales directamente del plugin oficial
    $sp_api_settings = get_option( 'sp_api_setting', array() );
    $client_id       = isset( $sp_api_settings['client_id'] ) ? sanitize_text_field( $sp_api_settings['client_id'] ) : '';
    
    if ( empty( $client_id ) ) {
        $options[''] = __( 'Error: API credentials missing in SendPulse plugin settings', 'automatorwp-sendpulse' );
        return $options;
    }

    $cached_books = get_transient( 'automatorwp_sendpulse_addressbooks_cache' );
    
    if ( false === $cached_books ) {
        
        $result = automatorwp_sendpulse_request( 'GET', '/addressbooks' );

        if ( ! is_wp_error( $result ) ) {
            $items = array();
            
            if ( isset( $result['data'] ) && is_array( $result['data'] ) ) {
                $items = $result['data'];
            } elseif ( is_array( $result ) ) {
                $items = isset( $result[0] ) ? $result : array();
            }

            if ( ! empty( $items ) ) {
                $cached_books = array();
                foreach ( $items as $item ) {
                    if ( isset( $item['id'] ) ) {
                        $name = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : $item['id'];
                        $cached_books[ $item['id'] ] = $name;
                    }
                }
                
                set_transient( 'automatorwp_sendpulse_addressbooks_cache', $cached_books, 300 );
            }
        } else {
            $options[''] = __( 'Error connecting to SendPulse', 'automatorwp-sendpulse' );
            return $options;
        }
    }

    if ( is_array( $cached_books ) && ! empty( $cached_books ) ) {
        $options = $options + $cached_books;
    } else {
        $options = array( '' => __( 'No addressbooks found', 'automatorwp-sendpulse' ) );
    }

    return $options;
}