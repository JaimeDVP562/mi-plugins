<?php

/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Mailjet\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Handler to save OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_mailjet_ajax_save_oauth_credentials()
{

    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    $prefix = "automatorwp_mailjet_";

    /* sanitize incoming data */
    $api_key = sanitize_text_field($_POST["api_key"]);
    $secret_key = sanitize_text_field($_POST["secret_key"]);


    if ($secret_key == '' && $api_key == '') {
        // return error one of the field missing
        wp_send_json_error();
    } else {
        $credentials = get_option('automatorwp_settings');

        $credentials[$prefix . 'api_key'] = $api_key;
        $credentials[$prefix . 'secret_key'] = $secret_key;
        $credentials = array_filter($credentials);

        update_option('automatorwp_settings', $credentials);

        wp_send_json_success();
    }
}
add_action('wp_ajax_automatorwp_mailjet_save_oauth_credentials', 'automatorwp_mailjet_ajax_save_oauth_credentials');

/**
 * Handler to delete OAuth credentials
 *
 * @since 1.0.0
 */
function automatorwp_mailjet_ajax_delete_oauth_credentials()
{

    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    $prefix = "automatorwp_mailjet_";
    $credentials = get_option('automatorwp_settings');
    $credentials[$prefix . 'api_key'] = null;
    $credentials[$prefix . 'secret_key'] = null;
    $credentials = array_filter($credentials);

    update_option('automatorwp_settings', $credentials);

    wp_send_json_success();
}
add_action('wp_ajax_automatorwp_mailjet_delete_oauth_credentials', 'automatorwp_mailjet_ajax_delete_oauth_credentials');

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_mailjet_ajax_authorize()
{
    // Security check
    check_ajax_referer('automatorwp_admin', 'nonce');

    $prefix = 'automatorwp_mailjet_';

    $secret_key = sanitize_text_field($_POST['secret_key']);
    $api_key = sanitize_text_field($_POST['api_key']);

    if (empty($secret_key) || empty($api_key)) {
        wp_send_json_error(array('message' => __('All fields are required to connect with Mailjet', 'automatorwp-mailjet')));
        return;
    }

    $status = automatorwp_mailjet_check_settings_status(['secret_key' => $secret_key, 'api_key' => $api_key]);

    if (empty($status)) {
        return;
    }

    $settings = get_option('automatorwp_settings');

    // Save API Api_key and API App_id
    $settings[$prefix . 'api_key'] = $api_key;
    $settings[$prefix . 'secret_key'] = $secret_key;


    // Update settings
    update_option('automatorwp_settings', $settings);
    $admin_url = str_replace('http://', 'http://', get_admin_url())  . 'admin.php?page=automatorwp_settings&tab=opt-tab-mailjet';

    wp_send_json_success(array(
        'message' => __('Correct data to connect with Mailjet', 'automatorwp-mailjet'),
        'redirect_url' => $admin_url
    ));
}
add_action('wp_ajax_automatorwp_mailjet_authorize',  'automatorwp_mailjet_ajax_authorize');
/**
 * Ajax function for selecting contacts
 *
 * @since 1.0.0
 */
function automatorwp_mailjet_ajax_get_contacts()
{
    // Security check
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Get search query if any
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like($_REQUEST['q']) : '';

    $contacts = automatorwp_mailjet_get_contacts();

    $results = array();

    if (empty($contacts) || !is_array($contacts)) {
        wp_send_json_success([]);
        wp_die();
    }

    foreach ($contacts as $contact) {
        $results[] = array(
            'id'   => $contact['Email'],
            'text' => $contact['Name'] . ' (' . $contact['Email'] . ')'
        );
    }

    // Prepend 'None' option
    $results = automatorwp_ajax_parse_extra_options($results);

    wp_send_json_success($results);
    die;
}
add_action('wp_ajax_automatorwp_mailjet_get_contacts', 'automatorwp_mailjet_ajax_get_contacts');

/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_mailjet_ajax_get_lists()
{
    // Security check
    check_ajax_referer('automatorwp_admin', 'nonce');

    // Get search query if any
    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';

    $lists = automatorwp_mailjet_get_lists($search);

    if ($lists === false) {
        wp_send_json_error(__('Failed to retrieve lists from Mailjet API', 'automatorwp-mailjet'));
    }

    $results = array();

    if (empty($contacts) || !is_array($contacts)) {
        wp_send_json_success([]);
        wp_die();
    }

    foreach ($lists as $list) {
        $results[] = array(
            'id'   => $list['ID'],
            'text' => $list['Name']
        );
    }

    // Prepend option none if needed
    $results = automatorwp_ajax_parse_extra_options($results);

    wp_send_json_success($results);
}
add_action('wp_ajax_automatorwp_mailjet_get_lists', 'automatorwp_mailjet_ajax_get_lists');
