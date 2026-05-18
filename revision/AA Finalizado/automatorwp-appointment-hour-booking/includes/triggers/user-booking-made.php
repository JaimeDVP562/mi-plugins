<?php
/**
 * User Booking Made
 *
 * @package     AutomatorWP\Integrations\Appointment_Hour_Booking\Triggers\User_Booking_Made
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Appointment_Hour_Booking_User_Booking_Made extends AutomatorWP_Integration_Trigger {

    public $integration = 'appointment_hour_booking';
    public $trigger = 'appointment_hour_booking_user_booking_made';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User books an appointment', 'automatorwp-appointment_hour_booking' ),
            'select_option'     => __( 'User books an <strong>appointment</strong>', 'automatorwp-appointment_hour_booking' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User books an appointment %1$s time(s)', 'automatorwp-appointment_hour_booking' ), '{times}' ),
            'log_label'         => __( 'User books an appointment', 'automatorwp-appointment_hour_booking' ),
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

    // Bail if no data
    if( empty( $args ) || !is_array( $args ) ) {
        return;
    }

    $user_id = get_current_user_id();

    // Bail if user is not logged
    if( $user_id === 0 ) {
        return; 
    }

    $app = isset( $args['apps'][0] ) ? $args['apps'][0] : array();
    $id = isset( $app['id'] ) ? $app['id'] : 1; // Default to 1 if not set

    // Appointment info
    $service    = isset( $args["app_service_{$id}"] )    ? $args["app_service_{$id}"]    : '';
    $duration   = isset( $args["app_duration_{$id}"] )   ? $args["app_duration_{$id}"]   : '';
    $price      = isset( $args["app_price_{$id}"] )      ? $args["app_price_{$id}"]      : '';
    $date       = isset( $args["app_date_{$id}"] )       ? $args["app_date_{$id}"]       : '';
    $starttime  = isset( $args["app_starttime_{$id}"] )  ? $args["app_starttime_{$id}"]  : '';
    $endtime    = isset( $args["app_endtime_{$id}"] )    ? $args["app_endtime_{$id}"]    : '';
    $quantity   = isset( $args["app_quantity_{$id}"] )   ? $args["app_quantity_{$id}"]   : '';
    $request_timestamp  = isset( $args['request_timestamp'] )   ? $args['request_timestamp']   : '';
    $final_price        = isset( $args['final_price'] )         ? $args['final_price']         : '';
    $final_price_short  = isset( $args['final_price_short'] )   ? $args['final_price_short']   : '';


    // Trigger
    automatorwp_trigger_event( array(
        'trigger'            => $this->trigger,
        'user_id'            => $user_id,

        // Appointment info
        'app_service'        => $service,
        'app_duration'       => $duration,
        'app_price'          => $price, // If there is more than one service, this value will be 0 because of how Appointment Hour Booking works
        'final_price'        => $final_price, // Use this value for the final price if there is more than one service
        'final_price_short'  => $final_price_short,
        'app_date'           => $date,
        'app_starttime'      => $starttime,
        'app_endtime'        => $endtime,
        'app_quantity'       => $quantity,
        'request_timestamp'  => $request_timestamp,

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

        // Don't deserve if user is not logged
        if ( $user_id === 0 ) {
            return false;
        }

        // Bail if post doesn't have key data
        if ( empty( $event['app_service'] ) || empty( $event['app_date'] ) ) {
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

        $log_meta['app_service']   = isset( $event['app_service'] )   ? $event['app_service']   : '';
        $log_meta['app_duration']  = isset( $event['app_duration'] )  ? $event['app_duration']  : '';
        $log_meta['app_price']     = isset( $event['app_price'] )     ? $event['app_price']     : '';
        $log_meta['final_price']  = isset( $event['final_price'] )  ? $event['final_price']  : '';
        $log_meta['final_price_short']  = isset( $event['final_price_short'] )  ? $event['final_price_short']  : '';
        $log_meta['app_date']      = isset( $event['app_date'] )      ? $event['app_date']      : '';
        $log_meta['app_starttime'] = isset( $event['app_starttime'] ) ? $event['app_starttime'] : '';
        $log_meta['app_endtime']   = isset( $event['app_endtime'] )   ? $event['app_endtime']   : '';
        $log_meta['app_quantity']  = isset( $event['app_quantity'] )  ? $event['app_quantity']  : '';
        $log_meta['request_timestamp']  = isset( $event['request_timestamp'] )  ? $event['request_timestamp']  : '';

        return $log_meta;

    }

}

new AutomatorWP_Appointment_Hour_Booking_User_Booking_Made();