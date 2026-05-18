<?php
/**
 * Pending Appointment
 *
 * @package     AutomatorWP\Integrations\BookingPress\Actions\Pending_Appointment
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BookingPress_Pending_Appointment extends AutomatorWP_Integration_Action {

    public $integration = 'bookingpress';
    public $action = 'bookingpress_pending_appointment';

    /**
     * Register the action
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Set appointment to pending', 'automatorwp-bookingpress' ),
            'select_option' => __( 'Set an <strong>appointment</strong> to pending', 'automatorwp-bookingpress' ),
            /* translators: %1$s: Appointment ID. */
            'edit_label'    => sprintf( __( 'Set appointment %1$s to pending', 'automatorwp-bookingpress' ), '{appointment_id}' ),
            /* translators: %1$s: Appointment ID. */
            'log_label'     => sprintf( __( 'Set appointment %1$s to pending', 'automatorwp-bookingpress' ), '{appointment_id}' ),
            'options'       => array(
                'appointment_id' => array(
                    'from'    => 'appointment_id',
                    'default' => __( 'appointment ID', 'automatorwp-bookingpress' ),
                    'fields'  => array(
                        'appointment_id' => array(
                            'name'    => __( 'Appointment ID:', 'automatorwp-bookingpress' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        global $wpdb;

        $appointment_id = absint( $action_options['appointment_id'] );

        $this->result = '';

        if( empty( $appointment_id ) ) {
            return;
        }

        $table_name = $wpdb->prefix . 'bookingpress_appointment_bookings';

        $updated = $wpdb->update(
            $table_name,
            array( 'bookingpress_appointment_status' => '2' ),
            array( 'bookingpress_appointment_booking_id' => $appointment_id )
        );

        if( $updated !== false ) {
            $this->result = __( 'Appointment set to pending successfully.', 'automatorwp-bookingpress' );
        } else {
            $this->result = __( 'The appointment could not be set to pending.', 'automatorwp-bookingpress' );
        }

    }

    /**
     * Register required hooks
     */
    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-bookingpress' ),
            'type' => 'text',
        );
        return $log_fields;
    }

}

new AutomatorWP_BookingPress_Pending_Appointment();