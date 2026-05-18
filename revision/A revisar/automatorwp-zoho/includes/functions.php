<?php

/**
 * Zoho Integration Functions
 *
 * @package     AutomatorWP\Integrations\Zoho\Functions
 * @since       1.0.0
 */

if (! defined('ABSPATH')) exit;

/**
 * Get the Zoho API Base URL based on region
 * * @since 1.0.0
 */
function automatorwp_zoho_get_api_url()
{
    $region = get_option('automatorwp_zoho_region', 'com');
    return "https://www.zohoapis.$region/crm/v2/";
}

/**
 * Request a new Access Token using the Refresh Token
 * * @since 1.0.0
 */
function automatorwp_zoho_refresh_access_token()
{
    $client_id     = get_option('automatorwp_zoho_client_id');
    $client_secret = get_option('automatorwp_zoho_client_secret');
    $refresh_token = get_option('automatorwp_zoho_refresh_token');
    $region        = get_option('automatorwp_zoho_region', 'com');

    if (empty($refresh_token)) return false;

    $url = "https://accounts.zoho.$region/oauth/v2/token";

    $response = wp_remote_post($url, array(
        'body' => array(
            'refresh_token' => $refresh_token,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'refresh_token',
        ),
    ));

    if (is_wp_error($response)) return false;

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        update_option('automatorwp_zoho_access_token', $body['access_token']);
        return $body['access_token'];
    }

    return false;
}

/**
 * Generic API Request for Zoho
 * * @since 1.0.0
 */
function automatorwp_zoho_api_request($endpoint, $body = array(), $method = 'POST')
{
    $token = get_option('automatorwp_zoho_access_token');
    $url   = automatorwp_zoho_get_api_url() . $endpoint;

    $args = array(
        'method'  => $method,
        'headers' => array(
            'Authorization' => 'Zoho-oauthtoken ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body'    => ! empty($body) ? json_encode($body) : null,
        'timeout' => 15,
    );

    $response = wp_remote_request($url, $args);

    // If token expired (401), refresh and try once more
    if (wp_remote_retrieve_response_code($response) == 401) {
        $new_token = automatorwp_zoho_refresh_access_token();
        if ($new_token) {
            $args['headers']['Authorization'] = 'Zoho-oauthtoken ' . $new_token;
            $response = wp_remote_request($url, $args);
        }
    }

    return $response;
}
