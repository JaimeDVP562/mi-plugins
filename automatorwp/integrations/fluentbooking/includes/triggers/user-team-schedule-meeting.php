<?php
/**
 * Schedule Team Meeting
 *
 * @package     AutomatorWP\Integrations\FluentBooking\Triggers\Schedule_Team_Meeting
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentBooking_Schedule_Team_Meeting extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentbooking';
    public $trigger = 'fluentbooking_schedule_team_meeting';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User schedules a team meeting', 'automatorwp' ),
            'select_option'     => __( 'User schedules a team <strong>meeting</strong>', 'automatorwp' ),
            /* translators: %1$s: Meeting title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User schedules team %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Meeting title. */
            'log_label'         => sprintf( __( 'User schedules team %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'fluent_booking/after_booking_scheduled',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Meeting:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any meeting', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_fluentbooking_get_team_events',
                    'options_cb'        => 'automatorwp_fluentbooking_options_cb_team_event',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_fluentbooking_get_event_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param FluentBooking\App\Models\Booking $booking
     * @param FluentBooking\App\Models\CalendarSlot $calendarSlot
     * @param array $bookingData
     */
    public function listener( $booking, $calendarSlot, $bookingData ) {
    
        $user_id = get_current_user_id();

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        $event_type = $booking->getAttribute( 'event_type' );

        // Bail if not team event
        if ( $event_type !== 'collective' && $event_type !== 'round_robin' ){
            return;
        }
        
        $booking_id = $booking->getAttribute( 'id' );
        $event_id = $booking->getAttribute( 'event_id' );
 
        // Trigger team schedule meeting
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'event_id'      => $event_id,
            'booking_id'    => $booking_id,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['event_id'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['event_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        
        $log_meta['booking_id'] = ( isset( $event['booking_id'] ) ? $event['booking_id'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_FluentBooking_Schedule_Team_Meeting();