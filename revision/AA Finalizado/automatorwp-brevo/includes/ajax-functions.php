<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Brevo\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_authorize()
{
    // Security check
    check_ajax_referer('automatorwp_admin', 'nonce');

    $prefix = 'automatorwp_brevo_';

    $url = automatorwp_brevo_get_url();
    $token = sanitize_text_field($_POST['token']);

    // Check parameters given
    if (empty($token)) {
        wp_send_json_error(array('message' => __('API Token is required to connect with Brevo', 'automatorwp-brevo')));
        return;
    }

    // To get first answer and check the connection
    $response = wp_remote_get(
        $url . '/contacts',
        array(
            'headers' => array(
                'api-key' => $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            )
        )
    );

    // Incorrect API token
    if (isset($response['response']['code']) && $response['response']['code'] !== 200) {
        wp_send_json_error(array('message' => __('Please, check your credentials', 'automatorwp-brevo')));
        return;
    }

    $settings = get_option('automatorwp_settings');

    // Save client url and API key
    $settings[$prefix . 'token'] = $token;

    // Update settings
    update_option('automatorwp_settings', $settings);
    $admin_url = str_replace('http://', 'http://', get_admin_url()) . 'admin.php?page=automatorwp_settings&tab=opt-tab-brevo';

    wp_send_json_success(
        array(
            'message' => __('Correct data to connect with Brevo', 'automatorwp-brevo'),
            'redirect_url' => $admin_url
        )
    );

}
add_action('wp_ajax_automatorwp_brevo_authorize', 'automatorwp_brevo_ajax_authorize');

/**
 * Ajax function for selecting folders
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_get_folders()
{
    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Pull back the search string
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like(sanitize_text_field($_REQUEST['q'])) : '';
    $folders = automatorwp_brevo_get_folders();

    $results = array();

    // Parse teams results to match select2 results
    foreach ($folders as $folder) {

        if (!empty($search)) {
            if (strpos(strtolower($folder['name']), strtolower($search)) === false) {
                continue;
            }
        }

        $results[] = array(
            'id' => $folder['id'],
            'text' => $folder['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return our results
    wp_send_json_success($results);
    die;
}
add_action('wp_ajax_automatorwp_brevo_get_folders', 'automatorwp_brevo_ajax_get_folders');

/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_get_lists()
{
    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Pull back the search string
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like(sanitize_text_field($_REQUEST['q'])) : '';
    // Get folder ID (from 'table' request parameter)
    $folder_id = isset($_REQUEST['table']) ? sanitize_text_field($_REQUEST['table']) : '';
    $lists = automatorwp_brevo_get_lists($folder_id);

    $results = array();

    // Parse tasks results to match select2 results
    foreach ($lists as $list) {

        if (!empty($search)) {
            if (strpos(strtolower($list['name']), strtolower($search)) === false) {
                continue;
            }
        }

        $results[] = array(
            'id' => strval($list['id']),
            'text' => $list['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return our results
    wp_send_json_success($results);
    die;

}
add_action('wp_ajax_automatorwp_brevo_get_lists', 'automatorwp_brevo_ajax_get_lists');

/**
 * Ajax function for selecting contacts
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_get_contacts_in_list()
{
    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Pull back the search string
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like(sanitize_text_field($_REQUEST['q'])) : '';
    // Get contact/list ID (from 'table' request parameter)
    $contact_id = isset($_REQUEST['table']) ? sanitize_text_field($_REQUEST['table']) : '';
    $contacts = automatorwp_brevo_get_contacts_in_list($contact_id);

    $results = array();

    // Parse tasks results to match select2 results
    foreach ($contacts as $contact) {

        if (!empty($search)) {
            if (strpos(strtolower($contact['email']), strtolower($search)) === false) {
                continue;
            }
        }

        $results[] = array(
            'id' => strval($contact['id']),
            'text' => $contact['email']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return our results
    wp_send_json_success($results);
    die;

}
add_action('wp_ajax_automatorwp_brevo_get_contacts_in_list', 'automatorwp_brevo_ajax_get_contacts_in_list');

/**
 * Ajax function for selecting pipelines
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_get_pipelines()
{
    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Pull back the search string
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like(sanitize_text_field($_REQUEST['q'])) : '';
    $pipelines = automatorwp_brevo_get_pipelines();

    $results = array();

    // Parse teams results to match select2 results
    foreach ($pipelines as $pipeline) {

        if (!empty($search)) {
            if (strpos(strtolower($pipeline['name']), strtolower($search)) === false) {
                continue;
            }
        }

        $results[] = array(
            'id' => $pipeline['id'],
            'text' => $pipeline['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return our results
    wp_send_json_success($results);
    die;
}
add_action('wp_ajax_automatorwp_brevo_get_pipelines', 'automatorwp_brevo_ajax_get_pipelines');

/**
 * Ajax function for selecting stages
 *
 * @since 1.0.0
 */
function automatorwp_brevo_ajax_get_stages()
{
    // Security check, forces to die if not security passed
    check_ajax_referer('automatorwp_admin', 'nonce');

    global $wpdb;

    // Pull back the search string
    $search = isset($_REQUEST['q']) ? $wpdb->esc_like(sanitize_text_field($_REQUEST['q'])) : '';
    // Get pipeline ID (from 'table' request parameter)
    $pipeline_id = isset($_REQUEST['table']) ? sanitize_text_field($_REQUEST['table']) : '';
    $stages = automatorwp_brevo_get_stages($pipeline_id);

    $results = array();

    // Parse tasks results to match select2 results
    foreach ($stages as $stage) {

        if (!empty($search)) {
            if (strpos(strtolower($stage['name']), strtolower($search)) === false) {
                continue;
            }
        }

        $results[] = array(
            'id' => $stage['id'],
            'text' => $stage['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options($results);

    // Return our results
    wp_send_json_success($results);
    die;

}
add_action('wp_ajax_automatorwp_brevo_get_stages', 'automatorwp_brevo_ajax_get_stages');