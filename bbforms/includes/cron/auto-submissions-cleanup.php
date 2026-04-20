<?php
/**
 * Auto Submissions Cleanup
 *
 * @package     BBForms\Cron\Auto_Submissons_Cleanup
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register schedule events
 *
 * @since 1.0.0
 */
function bbforms_auto_submissions_cleanup_schedule_events() {

    if ( function_exists( 'as_schedule_recurring_action' ) ) {

        // Action scheduler support
        if ( ! as_next_scheduled_action( 'bbforms_auto_submissions_cleanup_event' ) ) {
            as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'bbforms_auto_submissions_cleanup_event' );
        }

    } else {

        // WP Cron
        if ( ! wp_next_scheduled( 'bbforms_auto_submissions_cleanup_event' ) ) {
            wp_schedule_event( time(), 'daily', 'bbforms_auto_submissions_cleanup_event' );
        }

    }

}
add_action( 'bbforms_schedule_events', 'bbforms_auto_submissions_cleanup_schedule_events' );

/**
 * Clear scheduled events
 *
 * @since 1.0.0
 */
function bbforms_auto_submissions_cleanup_clear_scheduled_events() {
    wp_clear_scheduled_hook( 'bbforms_auto_submissions_cleanup_event' );
}
add_action( 'bbforms_clear_scheduled_events', 'bbforms_auto_submissions_cleanup_clear_scheduled_events' );

/**
 * Process the auto submissions cleanup
 *
 * @since 1.0.0
 */
function bbforms_auto_submissions_cleanup() {

    global $wpdb;

    $enabled = bbforms_get_option( 'auto_submissions_cleanup', false );

    if( ! $enabled ) {
        return;
    }

    $days = absint( bbforms_get_option( 'auto_submissions_cleanup_days', '' ) );

    // Bail if no days configured
    if( $days === 0 ) {
        return;
    }

    // Setup vars
    $date = gmdate( 'Y-m-d', strtotime( "-{$days} day", current_time( 'timestamp' ) ) );

    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Get old submissions
    $submissions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT s.id FROM {$ct_table->db->table_name} AS s WHERE s.created_at < %s", $date ) );

    // Delete submissions (parsing hooks for extra removals)
    bbforms_submissions_delete( $submissions_ids );

    /**
     * Available action to let other plugins process anything after the submissions cleanup
     *
     * @since 1.0.0
     *
     * @param string $date Date from submissions has been removed
     */
    do_action( 'bbforms_auto_submissions_cleanup_finished', $date );

}
add_action( 'bbforms_auto_submissions_cleanup_event', 'bbforms_auto_submissions_cleanup' );