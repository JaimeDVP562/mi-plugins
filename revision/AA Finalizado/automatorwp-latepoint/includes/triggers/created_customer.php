<?php
/**
 * Created Customer
 *
 * @package     AutomatorWP\Integrations\LatePoint\Triggers\Created_Customer
 * @author      Sergio Garcia
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_LatePoint_Created_Customer extends AutomatorWP_LatePoint_Trigger_Base {

    public $trigger = 'latepoint_created_customer';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A customer is <strong>created</strong>', 'automatorwp-latepoint' ),
            'select_option' => __( 'A customer is <strong>created</strong>', 'automatorwp-latepoint' ),
            'edit_label'    => sprintf( __( 'A customer is created %1$s time(s)', 'automatorwp-latepoint' ), '{times}' ),
            'log_label'     => __( 'A customer was created', 'automatorwp-latepoint' ),
            'action'        => 'latepoint_customer_created', 
        ) );
    }

    public function listener( $customer_id ) {
        if ( ! class_exists( 'OsCustomerModel' ) ) {
            return;
        }

        $customer = new OsCustomerModel( $customer_id );

        if ( ! $this->validate_object( $customer ) ) {
            return;
        }

        $user_id = $customer->wp_user_id;

        automatorwp_trigger_event( array(
            'trigger'      => $this->trigger,
            'user_id'      => $user_id,
            'event_id'     => $customer_id, 
        ) );
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $this->trigger !== $trigger->type ) {
            return $log_meta;
        }
        
        $log_meta['customer_id'] = $event['event_id'];
        
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( ! $this->is_trigger_match( $log, $object ) ) {
            return $log_fields;
        }

        $log_fields['customer_id'] = array(
            'name'  => __( 'Customer ID', 'automatorwp-latepoint' ),
            'value' => ( isset( $log->meta['customer_id'] ) ? $log->meta['customer_id'] : '' ),
        );

        return $log_fields;
    }
}

// Instanciar la clase
new AutomatorWP_LatePoint_Created_Customer();