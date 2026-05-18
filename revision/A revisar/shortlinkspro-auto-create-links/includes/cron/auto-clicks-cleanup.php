<?php
/**
 * Auto Clicks Cleanup
 *
 * @package     ShortLinksPro\Cron\Auto_Clicks_Cleanup
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register schedule events
 *
 * @since 1.0.0
 */
function shortlinkspro_auto_clicks_cleanup_schedule_events() {

    if ( function_exists( 'as_schedule_recurring_action' ) ) {

        // Action scheduler support
        if ( ! as_next_scheduled_action( 'shortlinkspro_auto_clicks_cleanup_event' ) ) {
            as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'shortlinkspro_auto_clicks_cleanup_event' );
        }

    } else {

        // WP Cron
        if ( ! wp_next_scheduled( 'shortlinkspro_auto_clicks_cleanup_event' ) ) {
            wp_schedule_event( time(), 'daily', 'shortlinkspro_auto_clicks_cleanup_event' );
        }

    }

}
add_action( 'shortlinkspro_schedule_events', 'shortlinkspro_auto_clicks_cleanup_schedule_events' );

/**
 * Clear scheduled events
 *
 * @since 1.0.0
 */
function shortlinkspro_auto_clicks_cleanup_clear_scheduled_events() {
    wp_clear_scheduled_hook( 'shortlinkspro_auto_clicks_cleanup_event' );
}
add_action( 'shortlinkspro_clear_scheduled_events', 'shortlinkspro_auto_clicks_cleanup_clear_scheduled_events' );

/**
 * Process the auto clicks cleanup
 *
 * @since 1.0.0
 */
function shortlinkspro_auto_clicks_cleanup() {

    global $wpdb;

    $enabled = shortlinkspro_get_option( 'auto_clicks_cleanup', false );

    if( ! $enabled ) {
        return;
    }

    $days = absint( shortlinkspro_get_option( 'auto_clicks_cleanup_days', '' ) );

    // Bail if no days configured
    if( $days === 0 ) {
        return;
    }

    // Setup vars
    $date = gmdate( 'Y-m-d', strtotime( "-{$days} day", current_time( 'timestamp' ) ) );

    $clicks = ShortLinksPro()->db->clicks;
    $clicks_meta = ShortLinksPro()->db->clicks_meta;

    // Delete old clicks
    $wpdb->query( "DELETE FROM {$clicks} WHERE created_at < '{$date}'" );

    // Delete orphaned clicks metas
    $wpdb->query( "DELETE cm FROM {$clicks_meta} cm INNER JOIN {$clicks} c ON c.id = cm.id WHERE c.id IS NULL" );

    /**
     * Available action to let other plugins process anything after the clicks cleanup
     *
     * @since 1.0.0
     *
     * @param string $date Date from clicks has been removed
     */
    do_action( 'shortlinkspro_auto_clicks_cleanup_finished', $date );

}
add_action( 'shortlinkspro_auto_clicks_cleanup_event', 'shortlinkspro_auto_clicks_cleanup' );