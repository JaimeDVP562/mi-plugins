<?php
/**
 * Functions
 *
 * @package     AutomatorWP\FluentBooking\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to events
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_fluentbooking_options_cb_single_event( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any event', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $event_id ) {

            // Skip option none
            if( $event_id === $none_value ) {
                continue;
            }

            $options[$event_id] = automatorwp_fluentbooking_get_single_event_title( $event_id );
        }
    }

    return $options;

}

/**
 * Get one-to-one events
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_fluentbooking_get_single_events() {

    $calendars = array( );

    $results = FluentBooking\App\Models\CalendarSlot::where( 'event_type', 'single' )->get();
    
    foreach ( $results as $calendar ){   

        $calendars[] = array(
            'id'    => $calendar->id,
            'name'  => $calendar->title,
        );         

    }

    return $calendars;

}

/**
 * Get the form title
 *
 * @since 1.0.0
 *
 * @param int $event_id
 *
 * @return string|null
 */
function automatorwp_fluentbooking_get_single_event_title( $event_id ) {


    // Empty title if no ID provided
    if( absint( $event_id ) === 0 ) {
        return '';
    }

    $result = FluentBooking\App\Models\CalendarSlot::where( 'id', $event_id )->get();
    
    foreach ( $result as $event ){   
        $event_title = $event->title;
    }

    return $event_title;

}


/**
 * Options callback for select2 fields assigned to team events
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_fluentbooking_options_cb_team_event( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any meeting', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $event_id ) {

            // Skip option none
            if( $event_id === $none_value ) {
                continue;
            }

            $options[$event_id] = automatorwp_fluentbooking_get_team_event_title( $event_id );
        }
    }

    return $options;

}

/**
 * Get team events
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_fluentbooking_get_team_events() {

    $calendars = array( );

    $results = FluentBooking\App\Models\CalendarSlot::whereIn( 'event_type', ['collective', 'round_robin'] )->get();
    
    foreach ( $results as $calendar ){   

        $calendars[] = array(
            'id'    => $calendar->id,
            'name'  => $calendar->title,
        );         

    }

    return $calendars;

}

/**
 * Get the team event title
 *
 * @since 1.0.0
 *
 * @param int $event_id
 *
 * @return string|null
 */
function automatorwp_fluentbooking_get_team_event_title( $event_id ) {


    // Empty title if no ID provided
    if( absint( $event_id ) === 0 ) {
        return '';
    }

    $result = FluentBooking\App\Models\CalendarSlot::where( 'id', $event_id )->get();
    
    foreach ( $result as $event ){   
        $event_title = $event->title;
    }

    return $event_title;

}
