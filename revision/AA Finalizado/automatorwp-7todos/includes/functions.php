<?php
/**
 * Helper functions for 7todos integration
 *
 * @package     AutomatorWP\7todos
 * @since       1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function automatorwp_7todos_api_request( $endpoint, $body ) {
    $api_key = automatorwp_get_option( '7todos_api_key', '' );

    if ( empty( $api_key ) ) {
        return false;
    }

    $json_body = json_encode( $body, JSON_UNESCAPED_SLASHES );

    $response = wp_remote_post( $endpoint, array(
        'method'    => 'POST',
        'sslverify' => true,
        'headers'   => array(
            'Authorization' => $api_key,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ),
        'body'      => $json_body,
        'timeout'   => 45,
    ) );

    if ( is_wp_error( $response ) ) {
        return false;
    }

    return array(
        'status_code' => wp_remote_retrieve_response_code( $response ),
        'body'        => wp_remote_retrieve_body( $response ),
    );
}