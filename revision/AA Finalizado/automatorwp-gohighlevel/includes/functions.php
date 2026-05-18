<?php

/**
 * Debug logging utility
 *
 * @since 1.0.0
 *
 * @param string $message Debug message.
 * @return void
 */
function automatorwp_gohighlevel_debug_log($message)
{
	if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
		error_log('[AutomatorWP GoHighLevel] ' . $message);
	}
}

/**
 * Get GoHighLevel API parameters.
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_gohighlevel_get_api()
{
	$access_token = automatorwp_gohighlevel_get_option('access_token', false);

	if (empty($access_token)) {
		$access_token = automatorwp_gohighlevel_get_option('api_key', false);
	}

	if (empty($access_token)) {
		automatorwp_gohighlevel_debug_log('Access token not configured');
		return false;
	}

	$location_id = automatorwp_gohighlevel_get_option('location_id', '');

	return array(
		'access_token' => $access_token,
		'location_id' => $location_id,
		'url' => apply_filters('automatorwp_gohighlevel_api_url', 'https://services.leadconnectorhq.com'),
		'version' => apply_filters('automatorwp_gohighlevel_api_version', '2021-07-28'),
	);
}

/**
 * Quick validation check without full API call.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function automatorwp_gohighlevel_is_api_configured()
{
	$access_token = automatorwp_gohighlevel_get_option('access_token', false);

	if (empty($access_token)) {
		$access_token = automatorwp_gohighlevel_get_option('api_key', false);
	}

	return ! empty($access_token);
}

/**
 * Clear validation cache when credentials change.
 *
 * @since 1.0.0
 * @return void
 */
function automatorwp_gohighlevel_clear_validation_cache()
{
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%automatorwp_gohighlevel_creds_valid_%'");
}

/**
 * Wrapper for GoHighLevel API requests.
 *
 * @since 1.0.0
 *
 * @param string $method   HTTP method.
 * @param string $endpoint Endpoint relative to base URL.
 * @param array  $args     Optional wp_remote args.
 *
 * @return array|WP_Error
 */
function automatorwp_gohighlevel_api_request($method, $endpoint, $args = array())
{
	$api = automatorwp_gohighlevel_get_api();

	if (! $api) {
		return new WP_Error('no_api', __('GoHighLevel API not configured', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN));
	}

	$url = rtrim($api['url'], '/') . '/' . ltrim($endpoint, '/');
	$method = strtoupper($method);

	$defaults = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $api['access_token'],
			'Version' => $api['version'],
		),
		'timeout' => AUTOMATORWP_GOHIGHLEVEL_API_TIMEOUT,
	);

	if (! empty($api['location_id'])) {
		$defaults['headers']['Location-Id'] = $api['location_id'];
	}

	$args = wp_parse_args($args, $defaults);

	if (isset($args['headers']) && is_array($args['headers'])) {
		$args['headers'] = array_merge($defaults['headers'], $args['headers']);
	}

	if (isset($args['body']) && is_array($args['body'])) {
		$args['body'] = wp_json_encode($args['body']);
		if (empty($args['headers']['Content-Type'])) {
			$args['headers']['Content-Type'] = 'application/json';
		}
	}

	if (in_array($method, array('GET', 'HEAD'), true)) {
		$response = wp_remote_get($url, $args);
	} else {
		$args['method'] = $method;
		$response = wp_remote_request($url, $args);
	}

	if (is_wp_error($response)) {
		return $response;
	}

	$status = wp_remote_retrieve_response_code($response);
	$raw_body = wp_remote_retrieve_body($response);
	$body = null;

	if (! empty($raw_body)) {
		$body = json_decode($raw_body, true);
	}

	if ($status < 200 || $status >= 300) {
		return new WP_Error(
			'gohighlevel_http_error',
			sprintf(__('GoHighLevel API returned HTTP %d', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN), $status),
			array('status' => $status, 'body' => $body, 'raw' => $raw_body)
		);
	}

	return array('status' => $status, 'body' => $body, 'raw' => $raw_body);
}

/**
 * Validate settings status from credentials payload.
 *
 * @since 1.0.0
 *
 * @param array $credentials Credentials to validate.
 * @return bool
 */
function automatorwp_gohighlevel_check_settings_status($credentials)
{
	$return = false;
	$access_token = isset($credentials['access_token']) ? (string) $credentials['access_token'] : '';

	if (empty($access_token) && isset($credentials['api_key'])) {
		$access_token = (string) $credentials['api_key'];
	}

	$location_id = isset($credentials['location_id']) ? (string) $credentials['location_id'] : '';

	if (empty($access_token)) {
		automatorwp_gohighlevel_debug_log('Access token not provided');
		wp_send_json_error(array('message' => __('Access token is missing', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
		return $return;
	}

	if (! automatorwp_gohighlevel_validate_api_credentials($access_token, $location_id)) {
		automatorwp_gohighlevel_debug_log('API credentials validation failed');
		wp_send_json_error(array('message' => __('Please check your token and location ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN)));
		return $return;
	}

	return true;
}

/**
 * Validate API credentials against GoHighLevel with caching.
 *
 * @since 1.0.0
 *
 * @param string $access_token Access token.
 * @param string $location_id  Optional location ID.
 * @return bool
 */
function automatorwp_gohighlevel_validate_api_credentials($access_token, $location_id = '')
{
	$cache_key = 'automatorwp_gohighlevel_creds_valid_' . md5($access_token . '|' . $location_id);
	$cached = get_transient($cache_key);

	if ($cached !== false) {
		return (bool) $cached;
	}

	$base_url = apply_filters('automatorwp_gohighlevel_api_url', 'https://services.leadconnectorhq.com');
	$version = apply_filters('automatorwp_gohighlevel_api_version', '2021-07-28');

	$endpoint = empty($location_id) ? 'locations/' : ('locations/' . rawurlencode($location_id));
	$url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');

	$request_args = array(
		'timeout' => AUTOMATORWP_GOHIGHLEVEL_API_TIMEOUT,
		'headers' => array(
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
			'Version' => $version,
		),
	);

	if (! empty($location_id)) {
		$request_args['headers']['Location-Id'] = $location_id;
	}

	$response = wp_remote_get($url, $request_args);

	if (is_wp_error($response)) {
		set_transient($cache_key, 0, 5 * MINUTE_IN_SECONDS);
		return false;
	}

	$status = (int) wp_remote_retrieve_response_code($response);
	if ($status < 200 || $status >= 300) {
		set_transient($cache_key, 0, 5 * MINUTE_IN_SECONDS);
		return false;
	}

	set_transient($cache_key, 1, 24 * HOUR_IN_SECONDS);
	return true;
}

/**
 * Extract normalized meta from a GoHighLevel webhook event.
 *
 * @since 1.0.0
 *
 * @param array $event Event data.
 * @return array
 */
function automatorwp_gohighlevel_extract_event_meta($event)
{
	$webhook = (isset($event['webhook_data']) && is_array($event['webhook_data'])) ? $event['webhook_data'] : array();

	$event_type = '';
	if (isset($event['event_type']) && is_scalar($event['event_type'])) {
		$event_type = (string) $event['event_type'];
	} elseif (isset($webhook['type']) && is_scalar($webhook['type'])) {
		$event_type = (string) $webhook['type'];
	}

	$location_id = isset($webhook['locationId']) ? (string) $webhook['locationId'] : '';
	$contact_id = isset($webhook['contactId']) ? (string) $webhook['contactId'] : '';
	$contact_email = isset($webhook['email']) ? (string) $webhook['email'] : '';
	$contact_phone = isset($webhook['phone']) ? (string) $webhook['phone'] : '';
	$appointment_id = isset($webhook['appointmentId']) ? (string) $webhook['appointmentId'] : '';
	$calendar_id = isset($webhook['calendarId']) ? (string) $webhook['calendarId'] : '';
	$opportunity_id = isset($webhook['opportunityId']) ? (string) $webhook['opportunityId'] : '';
	$pipeline_id = isset($webhook['pipelineId']) ? (string) $webhook['pipelineId'] : '';
	$pipeline_stage_id = isset($webhook['pipelineStageId']) ? (string) $webhook['pipelineStageId'] : '';
	$message = isset($webhook['message']) ? (string) $webhook['message'] : '';
	$timestamp = isset($webhook['timestamp']) ? (string) $webhook['timestamp'] : '';

	if (empty($contact_email) && isset($webhook['contact']) && is_array($webhook['contact']) && isset($webhook['contact']['email'])) {
		$contact_email = (string) $webhook['contact']['email'];
	}

	if (empty($contact_phone) && isset($webhook['contact']) && is_array($webhook['contact']) && isset($webhook['contact']['phone'])) {
		$contact_phone = (string) $webhook['contact']['phone'];
	}

	return array(
		'ghl_event_type' => $event_type,
		'ghl_location_id' => $location_id,
		'ghl_contact_id' => $contact_id,
		'ghl_contact_email' => $contact_email,
		'ghl_contact_phone' => $contact_phone,
		'ghl_appointment_id' => $appointment_id,
		'ghl_calendar_id' => $calendar_id,
		'ghl_opportunity_id' => $opportunity_id,
		'ghl_pipeline_id' => $pipeline_id,
		'ghl_pipeline_stage_id' => $pipeline_stage_id,
		'ghl_message' => $message,
		'ghl_timestamp' => $timestamp,
	);
}
