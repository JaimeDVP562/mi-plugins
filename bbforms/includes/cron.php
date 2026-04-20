<?php
/**
 * Cron
 *
 * @package     BBForms\Cron
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once BBFORMS_DIR . 'includes/cron/auto-submissions-cleanup.php';

/**
 * Register custom cron schedules
 *
 * @since 1.0.0
 *
 * @param array $schedules
 *
 * @return array
 */
function bbforms_cron_schedules( $schedules ) {

    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display'  => __( 'Every five minutes', 'bbforms' ),
    );

    return $schedules;

}
add_filter( 'cron_schedules', 'bbforms_cron_schedules' );

/**
 * Register schedule events
 *
 * @since 1.0.0
 */
function bbforms_schedule_events() {

    /**
     * Action triggered on activation to schedule events
     *
     * @since 1.0.0
     */
    do_action( 'bbforms_schedule_events' );

}

/**
 * Clear scheduled events
 *
 * @since 1.0.0
 */
function bbforms_clear_scheduled_events() {

    /**
     * Action triggered on deactivation to clear scheduled events
     *
     * @since 1.0.0
     */
    do_action( 'bbforms_clear_scheduled_events' );

}