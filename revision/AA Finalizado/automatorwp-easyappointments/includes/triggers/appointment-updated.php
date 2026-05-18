<?php
/**
 * Trigger: Easy Appointments - Appointment Updated
 *
 * @package     AutomatorWP\Integrations\easyappointments\Triggers\Appointment_Updated
 * @author      AutomatorWP
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_EasyAppointments_Appointment_Updated extends AutomatorWP_Integration_Trigger {

    public $integration = 'easyappointments';
    public $trigger = 'easyappointments_appointment_updated';

      /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User updates an appointment', 'automatorwp-easyappointments' ),
            'select_option'     => __( 'User updates <strong>an appointment</strong>', 'automatorwp-easyappointments' ),
            'edit_label'        => __( 'User updates an appointment', 'automatorwp-easyappointments' ),
            'log_label'         => __( 'User updates an appointment', 'automatorwp-easyappointments' ),
            'action'            => 'ea_appointment_updated',
            'function'          => array( $this, 'listener' ),
            'priority'          => 20,
            'accepted_args'     => 3,
            'options'           => array(
                'service' => array(
                    'from'      => 'easyappointments_get_services',
                    'field'     => 'service',
                    'label'     => __( 'Service:', 'automatorwp-easyappointments' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any service', 'automatorwp-easyappointments' ),
                    'default'   => 'any',
                ),
                'worker' => array(
                    'from'      => 'easyappointments_get_workers',
                    'field'     => 'worker',
                    'label'     => __( 'Worker:', 'automatorwp-easyappointments' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any worker', 'automatorwp-easyappointments' ),
                    'default'   => 'any',
                ),
                'status' => array(
                    'field'     => 'status',
                    'label'     => __( 'Status:', 'automatorwp-easyappointments' ),
                    'options'   => array(
                        'any'         => __( 'any status', 'automatorwp-easyappointments' ),
                        'pending'     => __( 'Pending', 'automatorwp-easyappointments' ),
                        'confirmed'   => __( 'Confirmed', 'automatorwp-easyappointments' ),
                        'canceled'    => __( 'Canceled', 'automatorwp-easyappointments' ),
                        'reservation' => __( 'Reservation', 'automatorwp-easyappointments' ),
                    ),
                    'default'   => 'any',
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'appointment_id' => array(
                        'label' => __( 'Appointment ID', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'service' => array(
                        'label' => __( 'Service ID', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'worker' => array(
                        'label' => __( 'Worker ID', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'status' => array(
                        'label' => __( 'Status', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'date' => array(
                        'label' => __( 'Date', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'start' => array(
                        'label' => __( 'Start Time', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                    'end' => array(
                        'label' => __( 'End Time', 'automatorwp-easyappointments' ),
                        'type'  => 'text',
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

  /**
    * Trigger listener
    *
    * @since 1.0.0
    *
    * @param int   $appointment_id    The appointment ID
    * @param array $appointment_data  The appointment data
    * @param int   $user_id           The user ID
    */
 public function listener( $appointment_id, $appointment_data, $user_id ) {
    if ( $user_id <= 0 ) return;
    automatorwp_trigger_event( array(
        'trigger'        => $this->trigger,
        'user_id'        => $user_id,
        'appointment_id' => $appointment_id,
        'appointment'    => $appointment_data,
    ) );
}

    /**
     * User deserves check
     *
     * @since 1.0.0
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( ! isset( $event['appointment_id'] ) ) {
            return false;
        }
        $appointment = isset( $event['appointment'] ) ? $event['appointment'] : array();

        if ( isset( $trigger_options['service'] ) && $trigger_options['service'] !== 'any' ) {
            if ( ! isset( $appointment['service'] ) || $appointment['service'] != $trigger_options['service'] ) {
                return false;
            }
        }
        if ( isset( $trigger_options['worker'] ) && $trigger_options['worker'] !== 'any' ) {
            if ( ! isset( $appointment['worker'] ) || $appointment['worker'] != $trigger_options['worker'] ) {
                return false;
            }
        }
        if ( isset( $trigger_options['status'] ) && $trigger_options['status'] !== 'any' ) {
            if ( ! isset( $appointment['status'] ) || $appointment['status'] != $trigger_options['status'] ) {
                return false;
            }
        }
        return $deserves_trigger;
    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        parent::hooks();
    }

     /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        $log_meta['appointment'] = isset( $event['appointment'] ) ? $event['appointment'] : array();
        return $log_meta;
    }
}

new AutomatorWP_EasyAppointments_Appointment_Updated();