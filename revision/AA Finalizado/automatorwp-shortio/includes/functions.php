<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Shortio\Functions
 * @since       1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the Short.io base API URL.
 *
 * @since 1.0.0
 * @return string
 */
function automatorwp_shortio_get_url() {
    return 'https://api.short.io';
}

/**
 * Get API credentials and base URL.
 *
 * @since 1.0.0
 * @return array|false Returns array with credentials or false if not configured.
 */
function automatorwp_shortio_get_api() {
    $url = automatorwp_shortio_get_url();
    $api_key = automatorwp_shortio_get_option( 'api_key' );

    if( empty( $api_key ) ) {
        return false;
    }

    return array(
        'url'     => $url,
        'api_key' => $api_key,
    );
}

/**
 * Validate the API key by making a test request.
 *
 * @since 1.0.0
 * @param string $api_key The Short.io API key to validate.
 * @return bool
 */
function automatorwp_shortio_check_api_key( $api_key ) {
    $headers = array( 'Authorization' => $api_key );

    $response = wp_remote_get( 'https://api.short.io/api/domains', array(
        'headers' => $headers
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 401 === $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API key', 'automatorwp-shortio' ) ) );
        return false;
    }

    return true;
}

/**
 * Create a new domain in Short.io.
 *
 * @since 1.0.0
 * @param string $domain_name The name of the domain to add.
 * @return int The HTTP response code.
 */
function automatorwp_shortio_create_domain( $domain_name ) {
    $api = automatorwp_shortio_get_api();
    if( ! $api ) return 0;

    $response = wp_remote_post( $api['url'] . '/domains/', array(
        'headers' => array(
            'Authorization' => $api['api_key'],
            'Content-Type'  => 'application/json'
        ),
        'body'    => json_encode( array( 'hostname' => sanitize_text_field( $domain_name ) ) )
    ) );

    return (int) wp_remote_retrieve_response_code( $response );
}

/**
 * Get all domains associated with the account.
 *
 * @since 1.0.0
 * @return array List of domains with id and hostname.
 */
function automatorwp_shortio_get_domains() {
    $api = automatorwp_shortio_get_api();
    if( ! $api ) return array();

    $response = wp_remote_get( $api['url'] . '/api/domains/', array(
        'headers' => array(
            'Authorization' => $api['api_key'],
            'accept'        => 'application/json'
        )
    ) );

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $domains = array();

    if( is_array( $body ) ) {
        foreach ( $body as $domain ) {
            $domains[] = array(
                'id'       => $domain['id'],
                'hostname' => $domain['hostname'],
            );
        }
    }

    return $domains;
}