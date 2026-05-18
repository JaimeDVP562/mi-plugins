<?php
/**
 * Updated Booking
 *
 * @package     AutomatorWP\Integrations\LatePoint\Triggers\Updated_Booking
 * @author      Sergio Garcia
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_LatePoint_Updated_Booking extends AutomatorWP_LatePoint_Trigger_Base {

    public $trigger = 'latepoint_updated_booking';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User updated a booking for <strong>{service}</strong>', 'automatorwp-latepoint' ),
            'select_option' => __( 'User updated a booking for <strong>{service}</strong>', 'automatorwp-latepoint' ),
            'edit_label'    => sprintf( __( 'User updated a booking for %1$s %2$s time(s)', 'automatorwp-latepoint' ), '{service}', '{times}' ),
            'log_label'     => sprintf( __( 'User updated a booking for %1$s', 'automatorwp-latepoint' ), '{service}' ),
            'action'        => 'latepoint_booking_updated',
            'options'       => array(
                'service' => array(
                    'from'    => 'service_id',
                    'default' => __( 'any service', 'automatorwp-latepoint' ),
                    'fields'  => array(
                        'service_id' => array(
                            'name'       => __( 'Service:', 'automatorwp-latepoint' ),
                            'type'       => 'select',
                            'options_cb' => array( $this, 'get_services_options' ),
                            'default'    => 'any',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function listener( $booking_id ) {
        if ( ! class_exists( 'OsBookingModel' ) ) {
            return;
        }

        $booking = new OsBookingModel( $booking_id );
        
        if ( ! $this->validate_object( $booking ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'      => $this->trigger,
            'user_id'      => $booking->customer->wp_user_id,
            'event_id'     => $booking_id,
            'service_id'   => $booking->service_id,
        ) );
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $this->trigger !== $trigger->type ) {
            return $log_meta;
        }
        
        $log_meta['booking_id'] = $event['event_id'];
        
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( ! $this->is_trigger_match( $log, $object ) ) {
            return $log_fields;
        }
        
        $log_fields['booking_id'] = array(
            'name'  => __( 'Booking ID', 'automatorwp-latepoint' ),
            'value' => ( isset( $log->meta['booking_id'] ) ? $log->meta['booking_id'] : '' ),
        );
        
        return $log_fields;
    }
}

// Instanciar la clase
new AutomatorWP_LatePoint_Updated_Booking();