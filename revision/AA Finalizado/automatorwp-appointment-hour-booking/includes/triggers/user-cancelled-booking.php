<?php
/**
 * User Cancelled Appointment
 *
 * @package     AutomatorWP\Integrations\Appointment_Hour_Booking\Triggers\User_Cancelled_Appointment
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Appointment_Hour_Booking_User_Cancelled_Booking extends AutomatorWP_Integration_Trigger {

    public $integration = 'appointment_hour_booking';
    public $trigger = 'appointment_hour_booking_user_cancelled_appointment';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User cancels an appointment', 'automatorwp-appointment_hour_booking' ),
            'select_option'     => __( 'User cancels an <strong>appointment</strong>', 'automatorwp-appointment_hour_booking' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User cancels an appointment %1$s time(s)', 'automatorwp-appointment_hour_booking' ), '{times}' ),
            'log_label'         => __( 'User cancels an appointment', 'automatorwp-appointment_hour_booking' ),
            'action'            => 'cpappb_update_status',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_appointment_hour_booking_get_appointment_tags(),
                automatorwp_appointment_hour_booking_get_cancel_tags(),
                automatorwp_utilities_times_tag() 
            )
        ) );
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int  $id       User ID
     * @param int  $status   Booking Status
     */
public function listener( $id, $status ) {
    $user_id = get_current_user_id();

    // Bail if user is not logged
    if( $user_id === 0 ) {
        return; 
    }

    global $wpdb;

    $status_normalized = strtolower($status);
    $cancel_statuses = array('cancelled', 'cancelled by customer', 'rejected');

    if ( !in_array($status_normalized, $cancel_statuses) ) return;

    $table = $wpdb->prefix . 'cpappbk_messages'; 
    $event = $wpdb->get_row( $wpdb->prepare(
        "SELECT posted_data FROM `$table` WHERE id = %d", $id
    ) );

    if (!$event) return;

    $posted_data = maybe_unserialize($event->posted_data);
    if (!is_array($posted_data) || empty($posted_data['apps'])) return;

    foreach ($posted_data['apps'] as $app) {
        // Solo si la cita está marcada como cancelada
        if (isset($app['cancelled']) && in_array(strtolower($app['cancelled']), $cancel_statuses)) {
            $app_id = isset($app['id']) ? $app['id'] : null;
            if(!$app_id) continue;

            automatorwp_trigger_event( array(
                'trigger'            => $this->trigger,
                'user_id'            => $user_id,
                'app_service'        => isset($posted_data["app_service_{$app_id}"]) ? $posted_data["app_service_{$app_id}"] : '',
                'app_duration'       => isset($posted_data["app_duration_{$app_id}"]) ? $posted_data["app_duration_{$app_id}"] : '',
                'app_price'          => isset($posted_data["app_price_{$app_id}"]) ? $posted_data["app_price_{$app_id}"] : '',
                'app_date'           => isset($posted_data["app_date_{$app_id}"]) ? $posted_data["app_date_{$app_id}"] : '',
                'app_starttime'      => isset($posted_data["app_starttime_{$app_id}"]) ? $posted_data["app_starttime_{$app_id}"] : '',
                'app_endtime'        => isset($posted_data["app_endtime_{$app_id}"]) ? $posted_data["app_endtime_{$app_id}"] : '',
                'app_quantity'       => isset($posted_data["app_quantity_{$app_id}"]) ? $posted_data["app_quantity_{$app_id}"] : '',
                'cancelreason'       => isset($posted_data["app_cancelreason_{$app_id}"]) ? $posted_data["app_cancelreason_{$app_id}"] : '',
                'final_price'        => isset($posted_data['final_price']) ? $posted_data['final_price'] : '',
                'final_price_short'  => isset($posted_data['final_price_short']) ? $posted_data['final_price_short'] : '',
                'request_timestamp'  => isset($posted_data['request_timestamp']) ? $posted_data['request_timestamp'] : '',
            ));
        }
    }
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
        $id = isset($event['app_id']) ? intval($event['app_id']) : 1;

        $log_meta['app_service']   = isset( $event['app_service'] )   ? $event['app_service']   : ( isset($event["app_service_{$id}"]) ? $event["app_service_{$id}"] : '' );
        $log_meta['app_duration']  = isset( $event['app_duration'] )  ? $event['app_duration']  : ( isset($event["app_duration_{$id}"]) ? $event["app_duration_{$id}"] : '' );
        $log_meta['app_price']     = isset( $event['app_price'] )     ? $event['app_price']     : ( isset($event["app_price_{$id}"]) ? $event["app_price_{$id}"] : '' );
        $log_meta['final_price']   = isset( $event['final_price'] )   ? $event['final_price']   : '';
        $log_meta['final_price_short'] = isset( $event['final_price_short'] ) ? $event['final_price_short'] : '';
        $log_meta['app_date']      = isset( $event['app_date'] )      ? $event['app_date']      : ( isset($event["app_date_{$id}"]) ? $event["app_date_{$id}"] : '' );
        $log_meta['app_starttime'] = isset( $event['app_starttime'] ) ? $event['app_starttime'] : ( isset($event["app_starttime_{$id}"]) ? $event["app_starttime_{$id}"] : '' );
        $log_meta['app_endtime']   = isset( $event['app_endtime'] )   ? $event['app_endtime']   : ( isset($event["app_endtime_{$id}"]) ? $event["app_endtime_{$id}"] : '' );
        $log_meta['app_quantity']  = isset( $event['app_quantity'] )  ? $event['app_quantity']  : ( isset($event["app_quantity_{$id}"]) ? $event["app_quantity_{$id}"] : '' );
        $log_meta['cancelreason']  = isset( $event['cancelreason'] )  ? $event['cancelreason']  : ( isset($event["app_cancelreason_{$id}"]) ? $event["app_cancelreason_{$id}"] : '' );
        $log_meta['request_timestamp'] = isset( $event['request_timestamp'] ) ? $event['request_timestamp'] : '';

        return $log_meta;
    }

}

new AutomatorWP_Appointment_Hour_Booking_User_Cancelled_Booking();