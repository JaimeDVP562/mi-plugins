<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Klaviyo\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_klaviyo_ajax_authorize() {
    // Security check
    check_ajax_referer('automatorwp_admin', 'nonce');

    $prefix = 'automatorwp_klaviyo_';

    if (isset($_POST['key']) && isset($_POST['secret'])) {
        $key = sanitize_text_field($_POST['key']);
        $secret = sanitize_text_field($_POST['secret']);

        // Check parameters given
        if (empty($key) || empty($secret)) {
            wp_send_json_error(array('message' => __('API Key and Secret are required to connect with Klaviyo', 'automatorwp-klaviyo')));
            return;
        }

        // Check Klaviyo API secret
        $status_secret = automatorwp_klaviyo_check_api_secret($secret);

        if (!$status_secret) {
            error_log('Incorrect API secret.');
            wp_send_json_error(array('message' => __('Invalid API secret. Please check your credentials.', 'automatorwp-klaviyo')));
            return;
        }

        // Update settings with Klaviyo API key and secret
        $settings = get_option('automatorwp_settings', array());
        $settings[$prefix . 'key'] = $key;
        $settings[$prefix . 'secret'] = $secret;
        update_option('automatorwp_settings', $settings);

        // Generate admin URL for redirect
        $admin_url = str_replace( 'http://', 'http://', get_admin_url() ) . 'admin.php?page=automatorwp_settings&tab=opt-tab-klaviyo'; 
        
        // Send success response with redirect URL
        wp_send_json_success(array(
            'message' => __('Correct data to connect with Klaviyo', 'automatorwp-klaviyo'),
            'redirect_url' => $admin_url
        ));
    } else {
        wp_send_json_error(array('message' => __('Invalid parameters received', 'automatorwp-klaviyo')));
    }
}

add_action('wp_ajax_automatorwp_klaviyo_authorize', 'automatorwp_klaviyo_ajax_authorize');

/**
 * Ajax function for retrieving folders (profiles) from Klaviyo
 *
 * @since 1.0.0
 */
function automatorwp_klaviyo_ajax_get_folders() {
    // Security check, die if nonce is not valid
    check_ajax_referer('automatorwp_admin', 'nonce');

    // Retrieve search string
    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';

    // Get profiles (folders) from Klaviyo
    $api = automatorwp_klaviyo_get_api();

    if (!$api) {
        wp_send_json_error(array('message' => __('Klaviyo integration not configured', 'automatorwp-klaviyo')));
        return;
    }

    $profiles = automatorwp_klaviyo_get_profiles($api['secret']);

    if (is_null($profiles)) {
        wp_send_json_error(array('message' => __('Error retrieving profiles from Klaviyo', 'automatorwp-klaviyo')));
        return;
    }

    $results = array();

    // Parse profiles to match select2 results
    foreach ($profiles['data'] as $profile) {
        if (!empty($search) && stripos($profile['attributes']['email'], $search) === false) {
            continue;
        }
        $results[] = array(
            'id' => $profile['id'],
            'text' => $profile['attributes']['email']
        );
    }

    // Prepend option 'none'
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return results
    wp_send_json_success($results);
}
add_action('wp_ajax_automatorwp_klaviyo_get_folders', 'automatorwp_klaviyo_ajax_get_folders');

/**
 * Ajax function for retrieving lists from Klaviyo
 *
 * @since 1.0.0
 */
function automatorwp_klaviyo_ajax_get_lists() {
    // Security check, die if nonce is not valid
    check_ajax_referer('automatorwp_admin', 'nonce');

    // Retrieve search string
    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';

    // Get API details
    $api = automatorwp_klaviyo_get_api();

    if (!$api) {
        wp_send_json_error(array('message' => __('Klaviyo integration not configured', 'automatorwp-klaviyo')));
        return;
    }

    // Get lists from Klaviyo
    $lists = automatorwp_klaviyo_get_lists($api['secret']);

    if (is_null($lists)) {
        wp_send_json_error(array('message' => __('Error retrieving lists from Klaviyo', 'automatorwp-klaviyo')));
        return;
    }

    $results = array();

    // Parse lists to match select2 results
    foreach ($lists['data'] as $list) {
        if (!empty($search) && stripos($list['attributes']['name'], $search) === false) {
            continue;
        }
        $results[] = array(
            'id' => $list['id'],
            'text' => $list['attributes']['name']
        );
    }

    // Prepend option 'none'
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return results
    wp_send_json_success($results);
}
add_action('wp_ajax_automatorwp_klaviyo_get_lists', 'automatorwp_klaviyo_ajax_get_lists');

/**
 * Ajax function for retrieving profiles from Klaviyo based on a list ID
 *
 * @since 1.0.0
 */
function automatorwp_klaviyo_ajax_get_profiles_in_list() {
    // Security check, die if nonce is not valid
    check_ajax_referer('automatorwp_admin', 'nonce');

    // Retrieve search string and list ID
    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';
    $list_id = isset($_REQUEST['table']) ? sanitize_text_field($_REQUEST['table']) : '';

    // Get API details
    $api = automatorwp_klaviyo_get_api();

    if (!$api) {
        wp_send_json_error(array('message' => __('Klaviyo integration not configured', 'automatorwp-klaviyo')));
        return;
    }

    // Get profiles from Klaviyo based on list ID
    $profiles = automatorwp_klaviyo_get_list_profiles($list_id, $api['secret']);

    if (is_null($profiles)) {
        wp_send_json_error(array('message' => __('Error retrieving profiles from Klaviyo', 'automatorwp-klaviyo')));
        return;
    }

    $results = array();

    // Parse profiles to match select2 results
    foreach ($profiles['data'] as $profile) {
        if (!empty($search) && stripos($profile['attributes']['email'], $search) === false) {
            continue;
        }
        $results[] = array(
            'id' => strval($profile['id']),
            'text' => $profile['attributes']['email']
        );
    }

    // Prepend option 'none'
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return results
    wp_send_json_success($results);
}
add_action('wp_ajax_automatorwp_klaviyo_get_profiles_in_list', 'automatorwp_klaviyo_ajax_get_profiles_in_list');
