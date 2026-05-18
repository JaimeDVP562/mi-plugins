<?php
/**
 * Functions
 *
 * Core helpers for the Cloudflare integration:
 * - Centralized API request handler
 * - Credential getters
 * - Cache purge utilities
 *
 * @package     AutomatorWP\Cloudflare\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------
// Credential helpers
// -----------------------------------------------------------------

/**
 * Returns the Cloudflare API Token stored in AutomatorWP settings.
 *
 * Supports an optional AUTOMATORWP_CLOUDFLARE_API_TOKEN constant
 * to allow server-level overrides (e.g. via wp-config.php).
 *
 * @since  1.0.0
 * @return string API token or empty string.
 */
function automatorwp_cloudflare_get_api_token() {
    if ( defined( 'AUTOMATORWP_CLOUDFLARE_API_TOKEN' ) ) {
        return AUTOMATORWP_CLOUDFLARE_API_TOKEN;
    }
    return (string) automatorwp_get_option( 'cloudflare_api_token', '' );
}

/**
 * Returns the Cloudflare Zone ID stored in AutomatorWP settings.
 *
 * Supports an optional AUTOMATORWP_CLOUDFLARE_ZONE_ID constant.
 *
 * @since  1.0.0
 * @return string Zone ID or empty string.
 */
function automatorwp_cloudflare_get_zone_id() {
    if ( defined( 'AUTOMATORWP_CLOUDFLARE_ZONE_ID' ) ) {
        return AUTOMATORWP_CLOUDFLARE_ZONE_ID;
    }
    return (string) automatorwp_get_option( 'cloudflare_zone_id', '' );
}

/**
 * Checks that both required credentials are present and non-empty.
 *
 * @since  1.0.0
 * @return bool
 */
function automatorwp_cloudflare_has_credentials() {
    return ! empty( automatorwp_cloudflare_get_api_token() )
        && ! empty( automatorwp_cloudflare_get_zone_id() );
}

// -----------------------------------------------------------------
// API request handler
// -----------------------------------------------------------------

/**
 * Makes an authenticated request to the Cloudflare REST API.
 *
 * @since  1.0.0
 *
 * @param string $endpoint   Relative endpoint, e.g. 'zones/{id}/purge_cache'.
 * @param string $method     HTTP method: GET, POST, PATCH, DELETE.
 * @param array  $body       Request body (will be JSON-encoded).
 *
 * @return array {
 *     @type bool        $success  Whether the API call succeeded.
 *     @type int         $status   HTTP status code.
 *     @type array|null  $data     Decoded response body.
 *     @type string      $error    Human-readable error message (only on failure).
 * }
 */
function automatorwp_cloudflare_api_request( $endpoint, $method = 'POST', $body = array() ) {

    $api_token = automatorwp_cloudflare_get_api_token();

    if ( empty( $api_token ) ) {
        return array(
            'success' => false,
            'status'  => 0,
            'data'    => null,
            'error'   => __( 'Cloudflare API Token is not configured.', 'automatorwp-cloudflare' ),
        );
    }

    $url = 'https://api.cloudflare.com/client/v4/' . ltrim( $endpoint, '/' );

    $args = array(
        'method'    => strtoupper( $method ),
        'timeout'   => 45,
        'sslverify' => false, // Required for localhost/XAMPP environments
        'headers'   => array(
            'Authorization' => 'Bearer ' . $api_token,
            'Content-Type'  => 'application/json',
        ),
    );

    // Only attach body for non-GET requests
    if ( ! empty( $body ) && strtoupper( $method ) !== 'GET' ) {
        $args['body'] = wp_json_encode( $body );
    }

    $response = wp_remote_request( $url, $args );

    // Handle WP-level transport errors (never return WP_Error directly)
    if ( is_wp_error( $response ) ) {
        return array(
            'success' => false,
            'status'  => 0,
            'data'    => null,
            'error'   => $response->get_error_message(),
        );
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $raw    = wp_remote_retrieve_body( $response );
    $data   = json_decode( $raw, true );

    // Cloudflare always returns { "success": true|false, "errors": [...] }
    $cf_success = isset( $data['success'] ) && $data['success'] === true;

    if ( ! $cf_success ) {
        // Extract first error message from Cloudflare response
        $cf_error = isset( $data['errors'][0]['message'] )
            ? $data['errors'][0]['message']
            : __( 'Unknown Cloudflare API error.', 'automatorwp-cloudflare' );

        return array(
            'success' => false,
            'status'  => $status,
            'data'    => $data,
            'error'   => $cf_error,
        );
    }

    return array(
        'success' => true,
        'status'  => $status,
        'data'    => $data,
        'error'   => '',
    );
}

// -----------------------------------------------------------------
// Cache purge helpers
// -----------------------------------------------------------------

/**
 * Purges the entire Cloudflare cache for the configured zone.
 *
 * @since  1.0.0
 * @return array See automatorwp_cloudflare_api_request() return format.
 */
function automatorwp_cloudflare_purge_everything() {

    $zone_id = automatorwp_cloudflare_get_zone_id();

    if ( empty( $zone_id ) ) {
        return array(
            'success' => false,
            'status'  => 0,
            'data'    => null,
            'error'   => __( 'Cloudflare Zone ID is not configured.', 'automatorwp-cloudflare' ),
        );
    }

    return automatorwp_cloudflare_api_request(
        'zones/' . $zone_id . '/purge_cache',
        'POST',
        array( 'purge_everything' => true )
    );
}

/**
 * Purges the Cloudflare cache for one or more specific URLs.
 *
 * @since  1.0.0
 *
 * @param array $urls  Array of fully-qualified URLs to purge.
 *
 * @return array See automatorwp_cloudflare_api_request() return format.
 */
function automatorwp_cloudflare_purge_urls( $urls ) {

    $zone_id = automatorwp_cloudflare_get_zone_id();

    if ( empty( $zone_id ) ) {
        return array(
            'success' => false,
            'status'  => 0,
            'data'    => null,
            'error'   => __( 'Cloudflare Zone ID is not configured.', 'automatorwp-cloudflare' ),
        );
    }

    // Ensure we have a clean, non-empty array of URLs
    $urls = array_values( array_filter( array_map( 'esc_url_raw', (array) $urls ) ) );

    if ( empty( $urls ) ) {
        return array(
            'success' => false,
            'status'  => 0,
            'data'    => null,
            'error'   => __( 'No valid URLs provided for cache purge.', 'automatorwp-cloudflare' ),
        );
    }

    // Cloudflare allows a maximum of 30 URLs per request
    $urls = array_slice( $urls, 0, 30 );

    return automatorwp_cloudflare_api_request(
        'zones/' . $zone_id . '/purge_cache',
        'POST',
        array( 'files' => $urls )
    );
}

/**
 * Logs the result of a Cloudflare API call to the AutomatorWP log.
 *
 * @since  1.0.0
 *
 * @param array     $result     Return value from any purge helper.
 * @param stdClass  $action     The AutomatorWP action object.
 * @param int       $user_id    Current user ID.
 * @param stdClass  $automation The parent automation object.
 */
function automatorwp_cloudflare_log_result( $result, $action, $user_id, $automation ) {
    
    if ( ! function_exists( 'automatorwp_add_log' ) ) {
        return;
    }

    if ( $result['success'] ) {
        automatorwp_add_log( array(
            'type'        => $action->type,
            'object_id'   => $action->id,
            'object_type' => 'action',
            'user_id'     => $user_id,
            'title'       => __( 'Cloudflare cache purged successfully.', 'automatorwp-cloudflare' ),
            'meta'        => array(
                'result' => $result,
            ),
        ) );
    } else {
        automatorwp_add_log( array(
            'type'        => $action->type,
            'object_id'   => $action->id,
            'object_type' => 'action',
            'user_id'     => $user_id,
            'title'       => sprintf(
                /* translators: %s: error message */
                __( 'Cloudflare cache purge failed: %s', 'automatorwp-cloudflare' ),
                $result['error']
            ),
            'meta'        => array(
                'result' => $result,
            ),
        ) );
    }
}
