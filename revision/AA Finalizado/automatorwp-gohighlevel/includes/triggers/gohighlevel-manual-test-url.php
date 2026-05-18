<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register GoHighLevel manual URL trigger.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_register_trigger_manual_test_url()
{
    if (! function_exists('automatorwp_register_trigger')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] register manual trigger: automatorwp_register_trigger not found');
        }
        return;
    }

    automatorwp_register_trigger('gohighlevel_manual_test_url', array(
        'integration'   => 'gohighlevel',
        'label'         => 'GoHighLevel: Manual test by URL',
        'select_option' => 'GoHighLevel: Manual test by URL',
        'edit_label'    => '{user} triggers a manual GoHighLevel test by URL',
        'log_label'     => '{user} triggers a manual GoHighLevel test by URL',
        'action'        => 'init',
        'function'      => 'awp_gohighlevel_manual_test_listener',
        'priority'      => 10,
        'accepted_args' => 0,
        'options'       => array(),
    ));

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] trigger registered: gohighlevel_manual_test_url');
    }
}
add_action('automatorwp_init', 'awp_gohighlevel_register_trigger_manual_test_url', 20);
add_action('init', 'awp_gohighlevel_manual_test_listener', 10, 0);

/**
 * Listener for manual URL test trigger.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_manual_test_listener()
{
    if (! is_user_logged_in()) {
        if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['gohighlevel_test_trigger'])) {
            error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] manual test canceled: user not logged in');
        }
        return;
    }

    if (! isset($_GET['gohighlevel_test_trigger'])) {
        return;
    }

    $user_id = get_current_user_id();
    if (! $user_id) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] manual test canceled: user_id=0');
        }
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $request_uri = is_string($request_uri) ? $request_uri : '';
    $current_url = home_url($request_uri);

    $location_id = automatorwp_gohighlevel_get_option('location_id', '');

    $event = array(
        'trigger' => 'gohighlevel_manual_test_url',
        'user_id' => $user_id,
        'gohighlevel' => array(
            'event_type'        => 'manual_test',
            'location_id'       => $location_id,
            'contact_id'        => 'manual-test-' . time(),
            'contact_email'     => wp_get_current_user()->user_email,
            'contact_phone'     => '',
            'appointment_id'    => '',
            'calendar_id'       => '',
            'opportunity_id'    => '',
            'pipeline_id'       => '',
            'pipeline_stage_id' => '',
            'message'           => 'Manual URL trigger test',
            'timestamp'         => current_time('mysql'),
            'test_url'          => esc_url_raw($current_url),
        ),
        'event_type' => 'manual_test',
        'webhook_data' => array(
            'type'       => 'manual_test',
            'locationId' => $location_id,
            'contactId'  => 'manual-test-' . time(),
            'email'      => wp_get_current_user()->user_email,
            'timestamp'  => current_time('mysql'),
            'message'    => 'Manual URL trigger test',
        ),
    );

    if (function_exists('automatorwp_trigger_event')) {
        $result = automatorwp_trigger_event($event);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $result_count = is_array($result) ? count($result) : 0;
            error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] manual trigger_event executed, completed_triggers=' . (int) $result_count);
        }
    } elseif (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[AWP-GoHighLevel ' . AUTOMATORWP_GOHIGHLEVEL_VER . '] manual test error: automatorwp_trigger_event not found');
    }
}
