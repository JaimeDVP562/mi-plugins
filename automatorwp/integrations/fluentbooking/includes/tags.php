<?php
/**
 * Tags
 *
 * @package     AutomatorWP\FluentBooking\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Appointment tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentbooking_get_event_tags() {

    return array(
        'event_id' => array(
            'label'     => __( 'Event ID', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The event ID',
        ),
        'event_title' => array(
            'label'     => __( 'Event title', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The event title',
        ),
        'calendar_id' => array(
            'label'     => __( 'Calendar ID', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The calendar ID',
        ),
        'booking_title' => array(
            'label'     => __( 'Booking title', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The booking title',
        ),
        'person_time_zone' => array(
            'label'     => __( 'Attendee timezone', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The attendee timezone',
        ),
        'start_time' => array(
            'label'     => __( 'Meeting start time', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The Meeting start time',
        ),
        'end_time' => array(
            'label'     => __( 'Meeting end time', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The Meeting end time',
        ),
        'slot_minutes' => array(
            'label'     => __( 'Meeting duration', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The Meeting duration',
        ),
        'email' => array(
            'label'     => __( 'Attendee email', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The meeting attendee email',
        ),
        'first_name' => array(
            'label'     => __( 'Attendee first name', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The meeting attendee first name',
        ),
        'last_name' => array(
            'label'     => __( 'Attendee last name', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The meeting attendee last name',
        ),
        'message' => array(
            'label'     => __( 'Message', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'Message when the meeting was scheduled',
        ),
        'phone' => array(
            'label'     => __( 'Attendee phone', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The attendee phone',
        ),
        'source' => array(
            'label'     => __( 'Meeting location', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The meeting location',
        ),
        'status' => array(
            'label'     => __( 'Meeting status', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The meeting status',
        ),
        'host_name' => array(
            'label'     => __( 'Host name', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The host name',
        ),
        'host_email' => array(
            'label'     => __( 'Host email', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The host email',
        ),
        'cancel_reason' => array(
            'label'     => __( 'Cancel reason', 'automatorwp-fluentbooking' ),
            'type'      => 'text',
            'preview'   => 'The cancel reason',
        ),
    );

}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_fluentbooking_get_trigger_event_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'fluentbooking' ) {
        return $replacement;
    }

    $booking_id = automatorwp_get_log_meta( $log->id, 'booking_id', false );
    
    $result = FluentBooking\App\Models\Booking::where( 'id', $booking_id )->get();

    foreach ( $result as $booking ) {
        $booking_data = $booking;
    }

    // Host user data
    $user_host = get_userdata( $booking_data->host_user_id );

    switch ( $tag_name ) {
        case 'start_time':
            $start_timestamp = strtotime( $booking_data->start_time );
            $replacement = wp_date( 'Y-m-d H:i', $start_timestamp );
            break;
        case 'end_time':
            $end_timestamp = strtotime( $booking_data->end_time );
            $replacement = wp_date( 'Y-m-d H:i', $end_timestamp );
            break;
        case 'event_title':
            $replacement = $booking->calendar_event->title;
            break;
        case 'booking_title':
            $replacement = $booking->getBookingTitle();
            break;
        case 'host_name':
            $replacement = $user_host->first_name . ' ' . $user_host->last_name;
            break;
        case 'host_email':
            $replacement = $user_host->user_email;
            break;
        case 'cancel_reason':
            $replacement = $booking->getCancelReason( true );
            break;
        default:
            $replacement = $booking_data->$tag_name;
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_fluentbooking_get_trigger_event_tag_replacement', 10, 6 );