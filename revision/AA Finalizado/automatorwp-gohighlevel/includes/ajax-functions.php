<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\GoHighLevel\Ajax_Functions
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! function_exists('automatorwp_ajax_parse_extra_options')) {
	/**
	 * Parse extra options for AJAX responses.
	 *
	 * @param array $results Results array.
	 * @return array
	 */
	function automatorwp_ajax_parse_extra_options($results)
	{
		return $results;
	}
}

/**
 * Verify AJAX request security.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_verify_ajax_request()
{
	if (! check_ajax_referer('automatorwp_admin', 'nonce', false)) {
		wp_send_json_error(array('message' => __('Security check failed', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
		wp_die();
	}
}

/**
 * Update plugin setting via AJAX.
 *
 * @since 1.0.0
 * @param string $setting_name Setting name (without prefix).
 * @param mixed  $value        Setting value.
 * @return bool
 */
function automatorwp_gohighlevel_update_setting($setting_name, $value)
{
	$prefix = 'automatorwp_gohighlevel_';
	$option_name = $prefix . $setting_name;

	return automatorwp_update_option($option_name, $value);
}

/**
 * Handler to save API credentials.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_ajax_save_oauth_credentials()
{
	automatorwp_gohighlevel_verify_ajax_request();

	$access_token = isset($_POST['access_token']) ? sanitize_text_field($_POST['access_token']) : '';
	if (empty($access_token)) {
		$access_token = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
	}
	$location_id = isset($_POST['location_id']) ? sanitize_text_field($_POST['location_id']) : '';
	$webhook_token = isset($_POST['webhook_token']) ? sanitize_text_field($_POST['webhook_token']) : '';

	if (empty($access_token)) {
		wp_send_json_error(array('message' => __('Access token is missing', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
		return;
	}

	automatorwp_gohighlevel_update_setting('access_token', $access_token);
	automatorwp_gohighlevel_update_setting('api_key', $access_token);
	automatorwp_gohighlevel_update_setting('location_id', $location_id);
	automatorwp_gohighlevel_update_setting('webhook_token', $webhook_token);
	automatorwp_gohighlevel_update_setting('access_valid', 1);
	automatorwp_gohighlevel_clear_validation_cache();

	wp_send_json_success(array('message' => __('Credentials saved successfully', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
}
add_action('wp_ajax_automatorwp_gohighlevel_save_oauth_credentials', 'automatorwp_gohighlevel_ajax_save_oauth_credentials');

/**
 * Handler to delete API credentials.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_ajax_delete_oauth_credentials()
{
	automatorwp_gohighlevel_verify_ajax_request();

	automatorwp_gohighlevel_update_setting('access_token', null);
	automatorwp_gohighlevel_update_setting('api_key', null);
	automatorwp_gohighlevel_update_setting('location_id', null);
	automatorwp_gohighlevel_update_setting('webhook_token', null);
	automatorwp_gohighlevel_update_setting('access_valid', 0);
	automatorwp_gohighlevel_clear_validation_cache();

	wp_send_json_success(array('message' => __('Credentials deleted successfully', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
}
add_action('wp_ajax_automatorwp_gohighlevel_delete_oauth_credentials', 'automatorwp_gohighlevel_ajax_delete_oauth_credentials');

/**
 * AJAX handler for authorize action.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_ajax_authorize()
{
	check_ajax_referer('automatorwp_admin', 'nonce');

	$prefix = 'automatorwp_gohighlevel_';
	$access_token = isset($_POST['access_token']) ? sanitize_text_field($_POST['access_token']) : '';
	if (empty($access_token)) {
		$access_token = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
	}
	$location_id = isset($_POST['location_id']) ? sanitize_text_field($_POST['location_id']) : '';
	$webhook_token = isset($_POST['webhook_token']) ? sanitize_text_field($_POST['webhook_token']) : '';

	if (empty($access_token)) {
		wp_send_json_error(array('message' => __('Access token is required to connect with GoHighLevel', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
		return;
	}

	$status = automatorwp_gohighlevel_check_settings_status(array(
		'access_token' => $access_token,
		'location_id' => $location_id,
	));
	if (empty($status)) {
		return;
	}

	$settings = get_option('automatorwp_settings');
	if (! is_array($settings)) {
		$settings = array();
	}

	$settings[$prefix . 'access_token'] = $access_token;
	$settings[$prefix . 'api_key'] = $access_token;
	$settings[$prefix . 'location_id'] = $location_id;
	$settings[$prefix . 'webhook_token'] = $webhook_token;
	$settings[$prefix . 'access_valid'] = 1;

	update_option('automatorwp_settings', $settings);

	$admin_url = get_admin_url() . 'admin.php?page=automatorwp_settings&tab=gohighlevel';

	wp_send_json_success(array(
		'message' => __('Correct data to connect with GoHighLevel', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
		'redirect_url' => $admin_url,
	));
}
add_action('wp_ajax_automatorwp_gohighlevel_authorize', 'automatorwp_gohighlevel_ajax_authorize');

/**
 * Handler to fire test trigger.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_ajax_test_trigger()
{
	check_ajax_referer('automatorwp-gohighlevel-test', 'nonce');

	if (! current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('You do not have permission to perform this action.', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
	}

	$user_id = get_current_user_id();
	$location_id = automatorwp_gohighlevel_get_option('location_id', '');

	$event = array(
		'event_type' => 'manual_test',
		'webhook_data' => array(
			'type' => 'manual_test',
			'locationId' => $location_id,
			'contactId' => 'test-contact-' . time(),
			'email' => wp_get_current_user()->user_email,
			'phone' => '',
			'message' => 'This is a GoHighLevel test trigger event',
			'timestamp' => current_time('mysql'),
		),
	);

	automatorwp_trigger_event(array(
		'trigger' => 'gohighlevel_manual_test_url',
		'user_id' => $user_id,
		'gohighlevel' => array(
			'event_type' => 'manual_test',
			'location_id' => $location_id,
			'contact_id' => 'test-contact-' . time(),
			'contact_email' => wp_get_current_user()->user_email,
			'contact_phone' => '',
			'appointment_id' => '',
			'calendar_id' => '',
			'opportunity_id' => '',
			'pipeline_id' => '',
			'pipeline_stage_id' => '',
			'message' => 'This is a GoHighLevel test trigger event',
			'timestamp' => current_time('mysql'),
			'test_url' => home_url('/?gohighlevel_test_trigger=1'),
		),
		'event' => $event,
	));

	wp_send_json_success(array('message' => __('Test trigger fired successfully!', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
}
add_action('wp_ajax_automatorwp_gohighlevel_test_trigger', 'automatorwp_gohighlevel_ajax_test_trigger');
