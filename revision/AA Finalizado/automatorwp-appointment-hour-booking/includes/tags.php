<?php
/**
 * Tags
 *
 * @package     AutomatorWP\appointment_hour_booking\Tags
 * @since       1.0.0
 */
// Exit if accesed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Appointment tags
 * 
 * @since 1.0.0
 * 
 * @return array
 */
function automatorwp_appointment_hour_booking_get_appointment_tags() {
    return array(
        'app_service' => array(
            'label'   => __( 'Service', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Service name',
        ),
        'app_duration' => array(
            'label'   => __( 'Duration', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Duration',
        ),
        'app_price' => array(
            'label'   => __( 'Price', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Price (use this in case of booking a single service)',
        ),
        'final_price' => array(
            'label'   => __( 'Final price', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Final price (use this in case of booking more than one service)',
        ),
        'final_price_short' => array(
            'label'   => __( 'Final price (short)', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Final price (short)',
        ),
        'app_date' => array(
            'label'   => __( 'Date', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Date',
        ),
        'app_starttime' => array(
            'label'   => __( 'Start time', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Start time',
        ),
        'app_endtime' => array(
            'label'   => __( 'End time', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'End time',
        ),
        'app_quantity' => array(
            'label'   => __( 'Quantity', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Quantity',
        ),
        'request_timestamp' => array(
            'label'   => __( 'Request timestamp', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Timestamp',
        ),
    );
}
function automatorwp_appointment_hour_booking_get_cancel_tags() {
    return array(
        'cancelreason' => array(
            'label'   => __( 'Cancel reason', 'automatorwp' ),
            'type'    => 'text',
            'preview' => 'Cancel reason',
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
function automatorwp_appointment_hour_booking_get_trigger_appointment_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'appointment_hour_booking' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'app_service':
        case 'app_duration':
        case 'app_price':
        case 'app_date':
        case 'app_starttime':
        case 'app_endtime':
        case 'app_quantity':
        case 'final_price':
        case 'final_price_short':
        case 'cancelreason':
        case 'request_timestamp':
            $replacement = automatorwp_get_log_meta( $log->id, $tag_name, true );
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_appointment_hour_booking_get_trigger_appointment_tag_replacement', 10, 6 );
