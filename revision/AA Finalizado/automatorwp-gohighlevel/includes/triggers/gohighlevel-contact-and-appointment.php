<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register GoHighLevel contact and appointment triggers.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_register_contact_and_appointment_triggers()
{
    if (! function_exists('automatorwp_register_trigger')) {
        return;
    }

    automatorwp_register_trigger('gohighlevel_contact_created', array(
        'integration'   => 'gohighlevel',
            'label'         => 'GoHighLevel: Contact created',
            'select_option' => 'GoHighLevel: Contact created',
            'edit_label'    => '{user} receives a contact created event from GoHighLevel',
            'log_label'     => '{user} receives a contact created event from GoHighLevel',
        'action'        => 'awp_gohighlevel_contact_created',
        'function'      => 'awp_gohighlevel_contact_created_listener',
        'priority'      => 10,
        'accepted_args' => 2,
        'options'       => array(),
    ));

    automatorwp_register_trigger('gohighlevel_appointment_booked', array(
        'integration'   => 'gohighlevel',
            'label'         => 'GoHighLevel: Appointment booked',
            'select_option' => 'GoHighLevel: Appointment booked',
            'edit_label'    => '{user} receives an appointment booked event from GoHighLevel',
            'log_label'     => '{user} receives an appointment booked event from GoHighLevel',
        'action'        => 'awp_gohighlevel_appointment_booked',
        'function'      => 'awp_gohighlevel_appointment_booked_listener',
        'priority'      => 10,
        'accepted_args' => 2,
        'options'       => array(),
    ));
}
add_action('automatorwp_init', 'awp_gohighlevel_register_contact_and_appointment_triggers', 21);

add_action('awp_gohighlevel_contact_created', 'awp_gohighlevel_contact_created_listener', 10, 2);
add_action('awp_gohighlevel_appointment_booked', 'awp_gohighlevel_appointment_booked_listener', 10, 2);
add_action('awp_gohighlevel_webhook_event', 'awp_gohighlevel_route_webhook_event', 10, 2);

/**
 * Route generic webhook events to trigger-specific hooks.
 *
 * @since 1.0.0
 *
 * @param array $payload Webhook payload.
 * @param int   $user_id Optional user id.
 * @return void
 */
function awp_gohighlevel_route_webhook_event($payload, $user_id = 0)
{
    if (! is_array($payload)) {
        return;
    }

    $type = '';

    if (isset($payload['type']) && is_scalar($payload['type'])) {
        $type = strtolower((string) $payload['type']);
    } elseif (isset($payload['event']) && is_scalar($payload['event'])) {
        $type = strtolower((string) $payload['event']);
    }

    if ($type === '') {
        return;
    }

    $contact_created_types = array(
        'contact.create',
        'contact.created',
        'contact_create',
        'contact_created',
        'contactcreation',
    );

    $appointment_booked_types = array(
        'appointment.create',
        'appointment.created',
        'appointment.booked',
        'appointment_create',
        'appointment_created',
        'appointment_booked',
        'calendar.appointment.booked',
    );

    if (in_array($type, $contact_created_types, true)) {
        do_action('awp_gohighlevel_contact_created', $payload, (int) $user_id);
        return;
    }

    if (in_array($type, $appointment_booked_types, true)) {
        do_action('awp_gohighlevel_appointment_booked', $payload, (int) $user_id);
    }
}

/**
 * Listener for GoHighLevel contact created events.
 *
 * @since 1.0.0
 *
 * @param array $payload Webhook payload.
 * @param int   $user_id User id.
 * @return void
 */
function awp_gohighlevel_contact_created_listener($payload = array(), $user_id = 0)
{
    $event = awp_gohighlevel_build_event_payload('gohighlevel_contact_created', 'contact.created', $payload, (int) $user_id);

    if (! empty($event)) {
        automatorwp_trigger_event($event);
    }
}

/**
 * Listener for GoHighLevel appointment booked events.
 *
 * @since 1.0.0
 *
 * @param array $payload Webhook payload.
 * @param int   $user_id User id.
 * @return void
 */
function awp_gohighlevel_appointment_booked_listener($payload = array(), $user_id = 0)
{
    $event = awp_gohighlevel_build_event_payload('gohighlevel_appointment_booked', 'appointment.booked', $payload, (int) $user_id);

    if (! empty($event)) {
        automatorwp_trigger_event($event);
    }
}

/**
 * Build normalized AutomatorWP event payload from webhook data.
 *
 * @since 1.0.0
 *
 * @param string $trigger Trigger slug.
 * @param string $event_type Event type.
 * @param array  $payload Webhook payload.
 * @param int    $user_id User id.
 * @return array
 */
function awp_gohighlevel_build_event_payload($trigger, $event_type, $payload, $user_id = 0)
{
    if (! function_exists('automatorwp_trigger_event')) {
        return array();
    }

    $payload = is_array($payload) ? $payload : array();

    $resolved_user_id = awp_gohighlevel_resolve_user_id($user_id);
    if (! $resolved_user_id) {
        return array();
    }

    $location_id = '';
    if (isset($payload['locationId'])) {
        $location_id = (string) $payload['locationId'];
    } elseif (isset($payload['location_id'])) {
        $location_id = (string) $payload['location_id'];
    }

    $contact_id = '';
    if (isset($payload['contactId'])) {
        $contact_id = (string) $payload['contactId'];
    } elseif (isset($payload['contact_id'])) {
        $contact_id = (string) $payload['contact_id'];
    } elseif (isset($payload['contact']) && is_array($payload['contact']) && isset($payload['contact']['id'])) {
        $contact_id = (string) $payload['contact']['id'];
    }

    $contact_email = '';
    if (isset($payload['email'])) {
        $contact_email = (string) $payload['email'];
    } elseif (isset($payload['contact']) && is_array($payload['contact']) && isset($payload['contact']['email'])) {
        $contact_email = (string) $payload['contact']['email'];
    }

    $contact_phone = '';
    if (isset($payload['phone'])) {
        $contact_phone = (string) $payload['phone'];
    } elseif (isset($payload['contact']) && is_array($payload['contact']) && isset($payload['contact']['phone'])) {
        $contact_phone = (string) $payload['contact']['phone'];
    }

    $appointment_id = '';
    if (isset($payload['appointmentId'])) {
        $appointment_id = (string) $payload['appointmentId'];
    } elseif (isset($payload['appointment_id'])) {
        $appointment_id = (string) $payload['appointment_id'];
    }

    $calendar_id = '';
    if (isset($payload['calendarId'])) {
        $calendar_id = (string) $payload['calendarId'];
    } elseif (isset($payload['calendar_id'])) {
        $calendar_id = (string) $payload['calendar_id'];
    }

    $opportunity_id = isset($payload['opportunityId']) ? (string) $payload['opportunityId'] : '';
    $pipeline_id = isset($payload['pipelineId']) ? (string) $payload['pipelineId'] : '';
    $pipeline_stage_id = isset($payload['pipelineStageId']) ? (string) $payload['pipelineStageId'] : '';
    $message = isset($payload['message']) ? (string) $payload['message'] : '';
    $timestamp = isset($payload['timestamp']) ? (string) $payload['timestamp'] : current_time('mysql');

    return array(
        'trigger' => $trigger,
        'user_id' => $resolved_user_id,
        'gohighlevel' => array(
            'event_type'        => $event_type,
            'location_id'       => $location_id,
            'contact_id'        => $contact_id,
            'contact_email'     => $contact_email,
            'contact_phone'     => $contact_phone,
            'appointment_id'    => $appointment_id,
            'calendar_id'       => $calendar_id,
            'opportunity_id'    => $opportunity_id,
            'pipeline_id'       => $pipeline_id,
            'pipeline_stage_id' => $pipeline_stage_id,
            'message'           => $message,
            'timestamp'         => $timestamp,
            'test_url'          => '',
        ),
        'event_type' => $event_type,
        'webhook_data' => $payload,
    );
}

/**
 * Resolve target user id for AutomatorWP event execution.
 *
 * @since 1.0.0
 *
 * @param int $user_id Preferred user id.
 * @return int
 */
function awp_gohighlevel_resolve_user_id($user_id = 0)
{
    $user_id = (int) $user_id;

    if ($user_id > 0) {
        return $user_id;
    }

    $current_user_id = get_current_user_id();
    if ($current_user_id > 0) {
        return (int) $current_user_id;
    }

    $admins = get_users(array(
        'role' => 'administrator',
        'number' => 1,
        'fields' => 'ID',
    ));

    if (! empty($admins)) {
        return (int) $admins[0];
    }

    return 0;
}
