<?php
/**
 * Tags
 *
 * @package     AutomatorWP\LatePoint\Tags
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function automatorwp_latepoint_get_booking_tags() {
    return array(
        'booking_id' => array(
            'label'     => __( 'Booking ID', 'automatorwp-latepoint' ),
            'tag'       => '{booking_id}',
            'type'      => 'text',
            'preview'   => '101',
        ),
        'service_name' => array(
            'label'     => __( 'Service Name', 'automatorwp-latepoint' ),
            'tag'       => '{service_name}',
            'type'      => 'text',
            'preview'   => 'Consultation',
        ),
        'agent_name' => array(
            'label'     => __( 'Agent Name', 'automatorwp-latepoint' ),
            'tag'       => '{agent_name}',
            'type'      => 'text',
            'preview'   => 'John Doe',
        ),
        'booking_start_date' => array(
            'label'     => __( 'Booking Start Date', 'automatorwp-latepoint' ),
            'tag'       => '{booking_start_date}',
            'type'      => 'text',
            'preview'   => '2026-03-02',
        ),
    );
}

function automatorwp_latepoint_get_trigger_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {
    $trigger_args = automatorwp_get_trigger( $trigger->type );

    if ( 'latepoint' !== $trigger_args['integration'] ) {
        return $replacement;
    }

    $booking_id = ( isset( $log->meta['booking_id'] ) ? $log->meta['booking_id'] : 0 );
    
    if ( ! $booking_id || ! class_exists( 'OsBookingModel' ) ) {
        return $replacement;
    }

    $booking = new OsBookingModel( $booking_id );

    switch ( $tag_name ) {
        case 'booking_id':
            $replacement = $booking_id;
            break;
        case 'service_name':
            $replacement = ( isset( $booking->service->name ) ? $booking->service->name : '' );
            break;
        case 'agent_name':
            $replacement = ( isset( $booking->agent->full_name ) ? $booking->agent->full_name : '' );
            break;
        case 'booking_start_date':
            $replacement = ( isset( $booking->start_date ) ? $booking->start_date : '' );
            break;
    }

    return $replacement;
}

add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_latepoint_get_trigger_tag_replacement', 10, 6 );