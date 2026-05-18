<?php
/**
 * Trigger: SendPulse - Spam Complaint
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AutomatorWP_Integration_Trigger' ) ) return;

class AutomatorWP_SendPulse_Spam_Complaint_Trigger extends AutomatorWP_Integration_Trigger {
    public $integration = 'sendpulse';
    public $trigger     = 'sendpulse_spam_complaint';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A SendPulse subscriber reports email as spam', 'automatorwp-sendpulse' ),
            'select_option' => __( 'A SendPulse subscriber <strong>reports email as spam</strong>', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'A SendPulse subscriber reports email as spam %1$s time(s)', 'automatorwp-sendpulse' ), '{times}' ),
            'log_label'     => __( 'A SendPulse subscriber reports email as spam', 'automatorwp-sendpulse' ),
            'action'        => 'automatorwp_sendpulse_spam', // EVENTO: spam
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1, 
            'options'       => array( 'times' => automatorwp_utilities_times_option() ),
            'tags'          => array_merge( array(
                'sendpulse_email' => array( 'label' => __( 'Subscriber Email', 'automatorwp-sendpulse' ), 'type' => 'text' ),
            ), automatorwp_utilities_times_tag() ),
        ) );
    }

    public function listener( $data ) {
        $email = isset( $data[0]['email'] ) ? sanitize_email( $data[0]['email'] ) : '';
        if ( empty( $email ) ) return;

        $user    = get_user_by( 'email', $email );
        $admins  = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
        $user_id = $user ? (int) $user->ID : ( ! empty( $admins ) ? (int) $admins[0]->ID : 0 );

        automatorwp_trigger_event( array(
            'trigger'             => $this->trigger,
            'user_id'             => $user_id,
            'sendpulse_email'     => $email,
        ) );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $trigger->type !== $this->trigger ) return $log_meta;
        $log_meta['sendpulse_email'] = isset( $event['sendpulse_email'] ) ? $event['sendpulse_email'] : '';
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) return $log_fields;
        $log_fields['sendpulse_email'] = array( 'name' => __( 'Subscriber Email:', 'automatorwp-sendpulse' ), 'type' => 'text' );
        return $log_fields;
    }
}
new AutomatorWP_SendPulse_Spam_Complaint_Trigger();