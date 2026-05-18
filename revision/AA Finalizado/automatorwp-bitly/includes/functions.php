<?php

/**
 * Functions
 *
 * @package     AutomatorWP\Bitly\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper function to get the Bitly API v4 base URL
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_bitly_get_api_url() {

    return 'https://api-ssl.bitly.com/v4';
}

/**
 * Helper function to get the Bitly API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_bitly_get_api() {

    $api_key = automatorwp_bitly_get_option( 'api_key', '' );

    if ( empty( $api_key ) ) {
        return false;
    }

    return array(
        'url'     => automatorwp_bitly_get_api_url(),
        'api_key' => $api_key,
    );
}

/**
 * Helper function to build the authorization headers for Bitly API requests
 *
 * @since 1.0.0
 *
 * @param string $api_key
 *
 * @return array
 */
function automatorwp_bitly_get_headers( $api_key ) {

    return array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
    );
}

/**
 * Check validation of API settings
 *
 * @since 1.0.0
 *
 * @param array $credentials
 *
 * @return bool
 */
function automatorwp_bitly_check_settings_status( $credentials ) {

    $response = wp_remote_get( 'https://api-ssl.bitly.com/v4/user', array(
        'headers' => automatorwp_bitly_get_headers( $credentials['api_key'] ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( 'Bitly API Error: ' . $response->get_error_message() );
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API credentials', 'automatorwp-bitly' ) ) );
        return false;
    }

    return true;
}

/**
 * Check if an API key is valid
 *
 * @since 1.0.0
 *
 * @param string $api_key
 *
 * @return bool
 */
function automatorwp_bitly_check_api_key( $api_key ) {

    $response = wp_remote_get( 'https://api-ssl.bitly.com/v4/user', array(
        'headers' => automatorwp_bitly_get_headers( $api_key ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( 'Bitly Check API Key Error: ' . $response->get_error_message() );
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );

    return ( 200 === $status_code );
}

/**
 * Create a new Bitly short link (free tier)
 *
 * On the free plan, only the long URL is required.
 * The API will return a shortened bit.ly link.
 *
 * @since 1.0.0
 *
 * @param string $long_url The URL to shorten
 *
 * @return array|false Response array with short link data, or false on error
 */
function automatorwp_bitly_create_short_link( $long_url ) {

    $api = automatorwp_bitly_get_api();

    if ( ! $api ) {
        return false;
    }

    if ( empty( $long_url ) ) {
        error_log( 'Bitly Create Short Link: No URL provided.' );
        return false;
    }

    $body = array(
        'long_url' => esc_url_raw( $long_url ),
    );

    $response = wp_remote_post( $api['url'] . '/shorten', array(
        'headers' => automatorwp_bitly_get_headers( $api['api_key'] ),
        'body'    => wp_json_encode( $body ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( 'Bitly Create Short Link Error: ' . $response->get_error_message() );
        return false;
    }

    $status_code   = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    if ( 200 === $status_code || 201 === $status_code ) {
        $data = json_decode( $response_body, true );

        if ( isset( $data['link'] ) ) {
            error_log( 'Bitly Create Short Link: Success! Short URL: ' . $data['link'] );
            return array(
                'success'     => true,
                'short_url'   => $data['link'],
                'long_url'    => $long_url,
                'status_code' => $status_code,
            );
        }
    }

    error_log( 'Bitly Create Short Link: Unexpected response - ' . $response_body );
    return false;
}