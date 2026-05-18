<?php
/**
 * Reject Appointment
 *
 * @package     AutomatorWP\Integrations\BookingPress\Actions\Reject_Appointment
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BookingPress_Reject_Appointment extends AutomatorWP_Integration_Action {

    public $integration = 'bookingpress';
    public $action = 'bookingpress_reject_appointment';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Reject an appointment', 'automatorwp-bookingpress' ),
            'select_option' => __( 'Reject an <strong>appointment</strong>', 'automatorwp-bookingpress' ),
            /* translators: %1$s: Appointment ID. */
            'edit_label'    => sprintf( __( 'Reject appointment %1$s', 'automatorwp-bookingpress' ), '{appointment_id}' ),
            /* translators: %1$s: Appointment ID. */
            'log_label'     => sprintf( __( 'Reject appointment %1$s', 'automatorwp-bookingpress' ), '{appointment_id}' ),
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
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
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
            array( 'bookingpress_appointment_status' => '4' ),
            array( 'bookingpress_appointment_booking_id' => $appointment_id )
        );

        if( $updated !== false) {
            $this->result = __( 'Appointment rejected successfully.', 'automatorwp-bookingpress' );
        } else {
            $this->result = __( 'The appointment could not be rejected.', 'automatorwp-bookingpress' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-bookingpress' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_BookingPress_Reject_Appointment();
