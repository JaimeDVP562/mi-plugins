<?php
/**
 * Trigger: Easy Appointments - Appointment Created
 *
 * @package     AutomatorWP\Integrations\easyappointments\Triggers\Appointment_Created
 * @author      AutomatorWP
 * @since       1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_EasyAppointments_Appointment_Created extends AutomatorWP_Integration_Trigger {

    public $integration = 'easyappointments';
    public $trigger = 'easyappointments_appointment_created';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User books an appointment', 'automatorwp-easyappointments' ),
            'select_option'     => __( 'User books <strong>an appointment</strong>', 'automatorwp-easyappointments' ),
            'edit_label'        => __( 'User books an appointment', 'automatorwp-easyappointments' ),
            'log_label'         => __( 'User books an appointment', 'automatorwp-easyappointments' ),
            'action'            => 'easy_ea_new_app_from_customer',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
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
                        'any'       => __( 'any status', 'automatorwp-easyappointments' ),
                        'pending'   => __( 'Pending', 'automatorwp-easyappointments' ),
                        'confirmed' => __( 'Confirmed', 'automatorwp-easyappointments' ),
                        'canceled'  => __( 'Canceled', 'automatorwp-easyappointments' ),
                        'reservation' => __( 'Reservation', 'automatorwp-easyappointments' ),
                    ),
                    'default'   => 'any',
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags'              => array_merge(
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
     * @param int $appointment_id
     * @param array $appointment_data
     * @param bool $from_frontend
     */
   public function listener( $appointment_id, $appointment_data, $from_frontend ) {
    $user_id = isset( $appointment_data['user'] ) ? intval( $appointment_data['user'] ) : 0;
    if ( $user_id <= 0 ) {
        return;
    }
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
     *
     * @param bool      $deserves_trigger
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return bool
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( ! isset( $event['appointment_id'] ) ) {
            return false;
        }
        $appointment = isset( $event['appointment'] ) ? $event['appointment'] : array();

        // Service filter
        if ( isset( $trigger_options['service'] ) && $trigger_options['service'] !== 'any' ) {
            if ( ! isset( $appointment['service'] ) || $appointment['service'] != $trigger_options['service'] ) {
                return false;
            }
        }
        // Worker filter
        if ( isset( $trigger_options['worker'] ) && $trigger_options['worker'] !== 'any' ) {
            if ( ! isset( $appointment['worker'] ) || $appointment['worker'] != $trigger_options['worker'] ) {
                return false;
            }
        }
        // Status filter
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
        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

    // Bail if action type don't match this trigger
    if( $trigger->type !== $this->trigger ) {
        return $log_meta;
    }

    $log_meta['appointment'] = isset( $event['appointment'] ) ? $event['appointment'] : array();

    return $log_meta;

    }

    public function log_fields( $log_fields, $log, $object ) {

    // Bail if log is not assigned to an trigger
    if( $log->type !== 'trigger' ) {
        return $log_fields;
    }

    // Bail if trigger type don't match this trigger
    if( $object->type !== $this->trigger ) {
        return $log_fields;
    }

    $log_fields['appointment'] = array(
        'name' => __( 'Appointment Data', 'automatorwp-easyappointments' ),
        'desc' => __( 'Information about the appointment created.', 'automatorwp-easyappointments' ),
        'type' => 'text',
    );

    return $log_fields;
        }

    }

new AutomatorWP_EasyAppointments_Appointment_Created();

/**
 * Helper: Get Easy Appointments services for selector
 */
function easyappointments_get_services() {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ea_services" );
    $options = array( 'any' => __( 'any service', 'automatorwp-easyappointments' ) );
    foreach ( $results as $row ) {
        $options[ $row->id ] = $row->name;
    }
    return $options;
}

/**
 * Helper: Get Easy Appointments workers for selector
 */
function easyappointments_get_workers() {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ea_staff" );
    $options = array( 'any' => __( 'any worker', 'automatorwp-easyappointments' ) );
    foreach ( $results as $row ) {
        $options[ $row->id ] = $row->name;
    }
    return $options;
}