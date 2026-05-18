<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\GoHighLevel\Tags
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register GoHighLevel trigger tags.
 *
 * @since 1.0.0
 * @return array
 */
function automatorwp_gohighlevel_get_trigger_tags()
{
	return array(
		'ghl_event_type' => array(
			'label'   => __('GoHighLevel: Event type', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'contact.created',
		),
		'ghl_location_id' => array(
			'label'   => __('GoHighLevel: Location ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'abc123location',
		),
		'ghl_contact_id' => array(
			'label'   => __('GoHighLevel: Contact ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'contact_123',
		),
		'ghl_contact_email' => array(
			'label'   => __('GoHighLevel: Contact email', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'john@example.com',
		),
		'ghl_contact_phone' => array(
			'label'   => __('GoHighLevel: Contact phone', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => '+15551234567',
		),
		'ghl_appointment_id' => array(
			'label'   => __('GoHighLevel: Appointment ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'apt_123',
		),
		'ghl_calendar_id' => array(
			'label'   => __('GoHighLevel: Calendar ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'cal_123',
		),
		'ghl_opportunity_id' => array(
			'label'   => __('GoHighLevel: Opportunity ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'opp_123',
		),
		'ghl_pipeline_id' => array(
			'label'   => __('GoHighLevel: Pipeline ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'pipeline_123',
		),
		'ghl_pipeline_stage_id' => array(
			'label'   => __('GoHighLevel: Pipeline stage ID', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'stage_123',
		),
		'ghl_message' => array(
			'label'   => __('GoHighLevel: Message', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'Sample webhook message',
		),
		'ghl_timestamp' => array(
			'label'   => __('GoHighLevel: Timestamp', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => '2026-03-03 12:00:00',
		),
		'ghl_test_url' => array(
			'label'   => __('GoHighLevel: Test URL', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
			'type'    => 'text',
			'preview' => 'https://example.com/?gohighlevel_test_trigger=1',
		),
	);
}

/**
 * Store GoHighLevel event data into trigger log meta.
 *
 * @since 1.0.0
 *
 * @param array    $log_meta   Log meta.
 * @param stdClass $trigger    Trigger object.
 * @param int      $user_id    User ID.
 * @param array    $event      Event payload.
 * @param stdClass $automation Automation object.
 * @return array
 */
function automatorwp_gohighlevel_trigger_log_meta($log_meta, $trigger, $user_id, $event, $automation)
{
	$trigger_args = automatorwp_get_trigger($trigger->type);

	if (! isset($trigger_args['integration']) || $trigger_args['integration'] !== 'gohighlevel') {
		return $log_meta;
	}

	if (! is_array($event)) {
		return $log_meta;
	}

	$meta = automatorwp_gohighlevel_extract_event_meta($event);

	if (isset($event['gohighlevel']) && is_array($event['gohighlevel']) && isset($event['gohighlevel']['test_url'])) {
		$meta['ghl_test_url'] = esc_url_raw((string) $event['gohighlevel']['test_url']);
	}

	foreach ($meta as $key => $value) {
		if (! isset($log_meta[$key])) {
			$log_meta[$key] = $value;
		}
	}

	return $log_meta;
}
add_filter('automatorwp_user_completed_trigger_log_meta', 'automatorwp_gohighlevel_trigger_log_meta', 10, 5);

/**
 * Custom trigger tag replacement.
 *
 * @since 1.0.0
 *
 * @param string    $replacement Replacement value.
 * @param string    $tag_name    Tag name.
 * @param stdClass  $trigger     Trigger object.
 * @param int       $user_id     User ID.
 * @param string    $content     Original content.
 * @param stdClass  $log         Trigger log object.
 * @return string
 */
function automatorwp_gohighlevel_get_trigger_tag_replacement($replacement, $tag_name, $trigger, $user_id, $content, $log)
{
	$trigger_args = automatorwp_get_trigger($trigger->type);

	if (! isset($trigger_args['integration']) || $trigger_args['integration'] !== 'gohighlevel') {
		return $replacement;
	}

	$allowed_tags = array(
		'ghl_event_type',
		'ghl_location_id',
		'ghl_contact_id',
		'ghl_contact_email',
		'ghl_contact_phone',
		'ghl_appointment_id',
		'ghl_calendar_id',
		'ghl_opportunity_id',
		'ghl_pipeline_id',
		'ghl_pipeline_stage_id',
		'ghl_message',
		'ghl_timestamp',
		'ghl_test_url',
	);

	if (! in_array($tag_name, $allowed_tags, true)) {
		return $replacement;
	}

	$log_meta = automatorwp_get_log_meta($log->id);

	return isset($log_meta[$tag_name][0]) ? $log_meta[$tag_name][0] : '';
}
add_filter('automatorwp_get_trigger_tag_replacement', 'automatorwp_gohighlevel_get_trigger_tag_replacement', 10, 6);
