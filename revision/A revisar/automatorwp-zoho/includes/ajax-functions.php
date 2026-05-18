<?php

/**
 * Ajax Functions for Zoho CRM
 *
 * @package     AutomatorWP\Integrations\Zoho\Ajax_Functions
 * @author      AutomatorWP
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Handler to delete Zoho credentials
 *
 * @since 1.0.0
 */
function automatorwp_zoho_ajax_delete_credentials()
{
    // Security check using the Zoho specific nonce
    check_ajax_referer('automatorwp_zoho_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('You are not allowed to do this.', 'automatorwp-zoho'),
        ));
        return;
    }

    $prefix = "automatorwp_zoho_";
    $settings = get_option('automatorwp_settings', array());

    // Clean up Zoho settings
    unset($settings[$prefix . 'access_token']);
    unset($settings[$prefix . 'region']);

    update_option('automatorwp_settings', $settings);

    wp_send_json_success(array(
        'message' => __('Zoho credentials deleted.', 'automatorwp-zoho')
    ));
}
add_action('wp_ajax_automatorwp_zoho_delete_credentials', 'automatorwp_zoho_ajax_delete_credentials');

/**
 * AJAX handler for the Zoho authorize action
 *
 * @since 1.0.0
 */
function automatorwp_zoho_ajax_authorize()
{
    // Security check
    check_ajax_referer('automatorwp_zoho_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('You are not allowed to do this.', 'automatorwp-zoho'),
        ));
        return;
    }

    $prefix = 'automatorwp_zoho_';

    // Get data from the AJAX request (sent via JS)
    $access_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';
    $region       = isset($_POST['region']) ? sanitize_text_field(wp_unslash($_POST['region'])) : 'com';

    if ($access_token === '') {
        wp_send_json_error(array(
            'message' => __('Access Token is required to connect with Zoho CRM', 'automatorwp-zoho')
        ));
        return;
    }

    // Temporary save to settings to let the check_status function work
    $settings = get_option('automatorwp_settings', array());
    $settings[$prefix . 'access_token'] = $access_token;
    $settings[$prefix . 'region']       = $region;
    update_option('automatorwp_settings', $settings);

    // Validate connection using the motor in functions.php
    if (function_exists('automatorwp_zoho_check_settings_status')) {
        $status = automatorwp_zoho_check_settings_status();

        if (empty($status)) {
            // If failed, remove the token
            unset($settings[$prefix . 'access_token']);
            update_option('automatorwp_settings', $settings);

            wp_send_json_error(array(
                'message' => __('Unable to validate Zoho CRM credentials. Check your token and region.', 'automatorwp-zoho'),
            ));
            return;
        }
    }

    $admin_url = add_query_arg(
        array('page' => 'automatorwp_settings', 'tab' => 'zoho'),
        admin_url('admin.php')
    );

    wp_send_json_success(array(
        'message'      => __('Successfully connected to Zoho CRM.', 'automatorwp-zoho'),
        'redirect_url' => $admin_url,
    ));
}
add_action('wp_ajax_automatorwp_zoho_authorize',  'automatorwp_zoho_ajax_authorize');
