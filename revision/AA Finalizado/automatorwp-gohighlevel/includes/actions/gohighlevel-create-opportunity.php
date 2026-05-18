<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register action: Create opportunity on GoHighLevel.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_register_action_create_opportunity()
{
    if (! function_exists('automatorwp_register_action')) {
        return;
    }

    automatorwp_register_action('gohighlevel_create_opportunity', array(
        'integration'   => 'gohighlevel',
        'label'         => 'GoHighLevel: Create opportunity',
        'select_option' => 'GoHighLevel: Create opportunity',
        'edit_label'    => 'Create opportunity "{gohighlevel_opportunity_name}"',
        'options'       => array(
            'opportunity' => array(
                'from'    => '',
                'default' => '',
                'fields'  => array(
                    'gohighlevel_opportunity_name' => array(
                        'name'    => 'Opportunity name',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_contact_id' => array(
                        'name'    => 'Contact ID',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_location_id' => array(
                        'name'    => 'Location ID (optional)',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_pipeline_id' => array(
                        'name'    => 'Pipeline ID',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_pipeline_stage_id' => array(
                        'name'    => 'Pipeline Stage ID',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_value' => array(
                        'name'    => 'Monetary value',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_opportunity_status' => array(
                        'name'    => 'Status (open, won, lost, abandoned)',
                        'type'    => 'text',
                        'default' => 'open',
                    ),
                ),
            ),
        ),
    ));
}
add_action('automatorwp_init', 'awp_gohighlevel_register_action_create_opportunity', 27);

/**
 * Execute action: Create opportunity on GoHighLevel.
 *
 * @since 1.0.0
 *
 * @param stdClass $action Action object.
 * @param int      $user_id User ID.
 * @param array    $event Event payload.
 * @param array    $action_options Action options.
 * @param stdClass $automation Automation object.
 * @return void
 */
function awp_gohighlevel_execute_action_create_opportunity($action, $user_id, $event, $action_options, $automation)
{
    if (! is_object($action) || empty($action->type) || $action->type !== 'gohighlevel_create_opportunity') {
        return;
    }

    $name = isset($action_options['gohighlevel_opportunity_name']) ? sanitize_text_field((string) $action_options['gohighlevel_opportunity_name']) : '';
    $contact_id = isset($action_options['gohighlevel_opportunity_contact_id']) ? sanitize_text_field((string) $action_options['gohighlevel_opportunity_contact_id']) : '';
    $location_id = isset($action_options['gohighlevel_opportunity_location_id']) ? sanitize_text_field((string) $action_options['gohighlevel_opportunity_location_id']) : '';
    $pipeline_id = isset($action_options['gohighlevel_opportunity_pipeline_id']) ? sanitize_text_field((string) $action_options['gohighlevel_opportunity_pipeline_id']) : '';
    $pipeline_stage_id = isset($action_options['gohighlevel_opportunity_pipeline_stage_id']) ? sanitize_text_field((string) $action_options['gohighlevel_opportunity_pipeline_stage_id']) : '';
    $status = isset($action_options['gohighlevel_opportunity_status']) ? sanitize_key((string) $action_options['gohighlevel_opportunity_status']) : 'open';

    $value_raw = isset($action_options['gohighlevel_opportunity_value']) ? (string) $action_options['gohighlevel_opportunity_value'] : '';
    $value = is_numeric($value_raw) ? (float) $value_raw : 0;

    if ($contact_id === '' && isset($event['gohighlevel']) && is_array($event['gohighlevel']) && isset($event['gohighlevel']['contact_id'])) {
        $contact_id = sanitize_text_field((string) $event['gohighlevel']['contact_id']);
    }

    if ($location_id === '') {
        $location_id = automatorwp_gohighlevel_get_option('location_id', '');
    }

    if ($name === '') {
        $name = 'AutomatorWP Opportunity';
    }

    if ($contact_id === '' || $pipeline_id === '' || $pipeline_stage_id === '') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] create opportunity canceled: required fields missing (contact/pipeline/stage)');
        }
        return;
    }

    $allowed_statuses = array('open', 'won', 'lost', 'abandoned');
    if (! in_array($status, $allowed_statuses, true)) {
        $status = 'open';
    }

    $body = array(
        'name'            => $name,
        'contactId'       => $contact_id,
        'pipelineId'      => $pipeline_id,
        'pipelineStageId' => $pipeline_stage_id,
        'status'          => $status,
    );

    if ($location_id !== '') {
        $body['locationId'] = $location_id;
    }

    if ($value > 0) {
        $body['monetaryValue'] = $value;
    }

    $response = automatorwp_gohighlevel_api_request('POST', 'opportunities/', array(
        'body' => $body,
    ));

    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] create opportunity error: ' . $response->get_error_message());
        }
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[AWP-GoHighLevel] opportunity created for contact_id=' . $contact_id . ' pipeline=' . $pipeline_id);
    }
}
add_action('automatorwp_execute_action', 'awp_gohighlevel_execute_action_create_opportunity', 10, 5);
