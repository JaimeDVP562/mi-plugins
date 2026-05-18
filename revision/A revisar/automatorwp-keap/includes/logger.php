<?php
/**
 * Logger
 * Improved error logging and debugging utilities
 *
 * @package     AutomatorWP\Integrations\Keap\Logger
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Log Keap integration activity
 *
 * @since 1.0.0
 *
 * @param string $message Log message
 * @param string $level   Log level (info, warning, error)
 * @param array  $context Additional context data
 *
 * @return void
 */
function automatorwp_keap_log( $message, $level = 'info', $context = array() ) {

    if ( ! defined( 'AUTOMATORWP_KEAP_DEBUG' ) || ! AUTOMATORWP_KEAP_DEBUG ) {
        return;
    }

    $log_file = AUTOMATORWP_KEAP_DIR . 'logs/keap.log';
    $log_dir  = dirname( $log_file );

    // Create logs directory if it does not exist
    if ( ! is_dir( $log_dir ) ) {
        wp_mkdir_p( $log_dir );
    }

    $timestamp   = current_time( 'mysql' );
    $log_level   = strtoupper( $level );
    $log_message = sprintf( '[%s] [%s] %s', $timestamp, $log_level, $message );

    if ( ! empty( $context ) ) {
        $log_message .= ' | Context: ' . wp_json_encode( $context );
    }

    error_log( $log_message . "\n", 3, $log_file );
}

/**
 * Handle API response errors gracefully
 * Never passes WP_Error directly to callers
 *
 * @since 1.0.0
 *
 * @param array|WP_Error $response The API response
 * @param string         $context  Operation being performed
 *
 * @return array|false Decoded JSON array or false on error
 */
function automatorwp_keap_handle_api_response( $response, $context = '' ) {

    if ( is_wp_error( $response ) ) {
        automatorwp_keap_log(
            'API WP_Error: ' . $response->get_error_message(),
            'error',
            array( 'context' => $context )
        );
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = wp_remote_retrieve_body( $response );

    if ( $status_code >= 400 ) {
        automatorwp_keap_log(
            'API HTTP Error: ' . $status_code,
            'error',
            array( 'context' => $context, 'body' => $body )
        );
        return false;
    }

    // 204 No Content is a valid success with no body
    if ( $status_code === 204 ) {
        return array();
    }

    $data = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        automatorwp_keap_log(
            'Invalid JSON response from API',
            'error',
            array( 'body' => $body, 'context' => $context )
        );
        return false;
    }

    return $data;
}

/**
 * Validate API credentials against Keap
 *
 * @since 1.0.0
 *
 * @param string $access_token Token to validate (optional, uses stored if empty)
 *
 * @return bool True if credentials are valid
 */
function automatorwp_keap_validate_credentials( $access_token = '' ) {

    if ( empty( $access_token ) ) {
        $api = automatorwp_keap_get_api();
        if ( ! $api ) {
            return false;
        }
        $access_token = $api['access_token'];
    }

    $response = wp_remote_get(
        'https://api.infusionsoft.com/crm/rest/v2/tags?limit=1',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
            'timeout'   => 10,
            'sslverify' => false,
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( is_wp_error( $response ) || $status_code >= 400 ) {
        automatorwp_keap_log( 'API credentials validation failed. Status: ' . $status_code, 'error' );
        return false;
    }

    automatorwp_keap_log( 'API credentials validated successfully', 'info' );
    return true;
}