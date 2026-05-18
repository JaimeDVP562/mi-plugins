<?php
/**
 * Cron
 *
 * @package     ShortLinksPro\Cron
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once SHORTLINKSPRO_DIR . 'includes/cron/auto-clicks-cleanup.php';

/**
 * Register custom cron schedules
 *
 * @since 1.0.0
 *
 * @param array $schedules
 *
 * @return array
 */
function shortlinkspro_cron_schedules( $schedules ) {

    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display'  => __( 'Every five minutes', 'shortlinkspro' ),
    );

    return $schedules;

}
add_filter( 'cron_schedules', 'shortlinkspro_cron_schedules' );

/**
 * Register schedule events
 *
 * @since 1.0.0
 */
function shortlinkspro_schedule_events() {

    /**
     * Action triggered on activation to schedule events
     *
     * @since 1.0.0
     */
    do_action( 'shortlinkspro_schedule_events' );

}

/**
 * Clear scheduled events
 *
 * @since 1.0.0
 */
function shortlinkspro_clear_scheduled_events() {

    /**
     * Action triggered on deactivation to clear scheduled events
     *
     * @since 1.0.0
     */
    do_action( 'shortlinkspro_clear_scheduled_events' );

}