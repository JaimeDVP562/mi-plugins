<?php
/**
 * Guest Booking Made
 *
 * @package     AutomatorWP\Integrations\Appointment_Hour_Booking\Triggers\Guest_Booking_Made
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Appointment_Hour_Booking_Guest_Booking_Made extends AutomatorWP_Integration_Trigger {

    public $integration = 'appointment_hour_booking';
    public $trigger = 'appointment_hour_booking_guest_booking_made';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest books an appointment', 'automatorwp-appointment_hour_booking' ),
            'select_option'     => __( 'Guest books an <strong>appointment</strong>', 'automatorwp-appointment_hour_booking' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'Guest books an appointment %1$s time(s)', 'automatorwp-appointment_hour_booking' ), '{times}' ),
            'log_label'         => __( 'Guest books an appointment', 'automatorwp-appointment_hour_booking' ),
            'action'            => 'cpappb_process_data',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_appointment_hour_booking_get_appointment_tags(),
                automatorwp_utilities_times_tag() 
            )
        ) );
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array       $args    Appointment data
     */
    public function listener( $args ) {

        // Bail if can not find any data
        if( empty( $args ) || !is_array( $args ) ) {
            return;
        }
        
        $user_id = get_current_user_id();

        // Bail if Guest is not logged
        if( $user_id !== 0 ) {
            return; 
        }


        $app = isset( $args['apps'][0] ) ? $args['apps'][0] : array();
        $id = isset( $app['id'] ) ? $app['id'] : 1; // Default to 1 if not set

        // Appointment tags
        $service    = isset( $args["app_service_{$id}"] )    ? $args["app_service_{$id}"]    : '';
        $duration   = isset( $args["app_duration_{$id}"] )   ? $args["app_duration_{$id}"]   : '';
        $price      = isset( $args["app_price_{$id}"] )      ? $args["app_price_{$id}"]      : '';
        $date       = isset( $args["app_date_{$id}"] )       ? $args["app_date_{$id}"]       : '';
        $starttime  = isset( $args["app_starttime_{$id}"] )  ? $args["app_starttime_{$id}"]  : '';
        $endtime    = isset( $args["app_endtime_{$id}"] )    ? $args["app_endtime_{$id}"]    : '';
        $quantity   = isset( $args["app_quantity_{$id}"] )   ? $args["app_quantity_{$id}"]   : '';



        automatorwp_trigger_event( array(
            'trigger'           => $this->trigger,
            'guest_id'           => get_current_user_id(),
            'app_service'       => $service,
            'app_duration'      => $duration,
            'app_price'         => $price,
            'app_date'          => $date,
            'app_starttime'     => $starttime,
            'app_endtime'       => $endtime,
            'app_quantity'      => $quantity,
        ) );
    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_guest_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $guest_id           The guest ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $guest_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['app_service']   = isset( $event['app_service'] )   ? $event['app_service']   : '';
        $log_meta['app_duration']  = isset( $event['app_duration'] )  ? $event['app_duration']  : '';
        $log_meta['app_price']     = isset( $event['app_price'] )     ? $event['app_price']     : '';
        $log_meta['app_date']      = isset( $event['app_date'] )      ? $event['app_date']      : '';
        $log_meta['app_starttime'] = isset( $event['app_starttime'] ) ? $event['app_starttime'] : '';
        $log_meta['app_endtime']   = isset( $event['app_endtime'] )   ? $event['app_endtime']   : '';
        $log_meta['app_quantity']  = isset( $event['app_quantity'] )  ? $event['app_quantity']  : '';

        return $log_meta;

    }

}

new AutomatorWP_Appointment_Hour_Booking_Guest_Booking_Made();