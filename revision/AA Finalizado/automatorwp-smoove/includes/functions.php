<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Helper function to get the Smoove API parameters
 *
 * @since 1.0.0
 *
 * @return array|false API parameters or false if not configured
 */
function automatorwp_smoove_get_api() {
    $api_key = automatorwp_smoove_get_option('api_key', '');
    $url = 'https://rest.smoove.io/v1/';

    if (empty($api_key)) {
        return false;
    }

    return array(
        'api_key' => $api_key,
        'url' => $url
    );
}

/**
 * Check if the API credentials are correct
 *
 * @since 1.0.0
 */
function automatorwp_smoove_check_settings_status($credentials) {
    $response = wp_remote_get('https://rest.smoove.io/v1/Account/ContactFields', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $credentials['api_key']
        )
    ));

    $status_code = wp_remote_retrieve_response_code($response);

    if (200 !== $status_code) {
        wp_send_json_error(array('message' => __('Please, check your API credentials', 'automatorwp-smoove')));
        return false;
    }

    return true;
}

/**
 * Add or update a contact in Smoove
 *
 * @since 1.0.0
 */
function automatorwp_smoove_create_contact($contact_data) {
    $api = automatorwp_smoove_get_api();
    if (!$api) return false;

    $url = $api['url'] . 'Contacts?updateIfExists=true';

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type' => 'application/json'
        ),
        'body' => wp_json_encode($contact_data),
        'method' => 'POST'
    ));

    return $response;
}

/**
 * Add a contact to specific lists in Smoove
 *
 * @since 1.0.0
 */
function automatorwp_smoove_add_contact_to_lists($email, $list_ids) {
    $api = automatorwp_smoove_get_api();
    if (!$api) return false;

    $url = $api['url'] . 'Contacts?updateIfExists=true';

    $body = array(
        'email' => sanitize_email($email),
        'lists_Linked' => array_map('intval', $list_ids)
    );

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type' => 'application/json'
        ),
        'body' => wp_json_encode($body),
        'method' => 'POST'
    ));

    return wp_remote_retrieve_response_code($response);
}

/**
 * Get Landing Pages from Smoove
 *
 * @since 1.0.0
 */
function automatorwp_smoove_get_landing_pages() {
    $api = automatorwp_smoove_get_api();
    
    if ( ! $api ) {
        return new WP_Error( 'no_api_key', __( 'API Key is missing', 'automatorwp-smoove' ) );
    }

    $url = $api['url'] . 'LandingPages';

    $response = wp_remote_get( $url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type'  => 'application/json'
        ),
        'timeout' => 30
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = wp_remote_retrieve_body( $response );

    if ( 200 !== $status_code ) {
        $error_msg = 'HTTP Code: ' . $status_code . ' | Res: ' . ( empty($body) ? 'Empty' : $body );
        return new WP_Error( 'api_error', $error_msg );
    }

    return json_decode( $body, true );
}

/**
 * Get Lists from Smoove
 *
 * @since 1.0.0
 */
function automatorwp_smoove_get_lists() {
    $api = automatorwp_smoove_get_api();
    if (!$api) return new WP_Error('missing_api_key', __('API key missing', 'automatorwp-smoove'));

    $url = $api['url'] . 'Lists';

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) return $response;

    $status_code = wp_remote_retrieve_response_code($response);
    if (200 !== $status_code) {
        return new WP_Error('api_error', __('Failed to retrieve lists', 'automatorwp-smoove'));
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

/**
 * Get Async Contact Status
 *
 * @since 1.0.0
 */
function automatorwp_smoove_get_async_contact_status($operation_id) {
    $api = automatorwp_smoove_get_api();
    if (!$api) return false;

    $url = $api['url'] . "async/contacts/{$operation_id}/status";

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type' => 'application/json',
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) return false;

    return json_decode(wp_remote_retrieve_body($response), true);
}