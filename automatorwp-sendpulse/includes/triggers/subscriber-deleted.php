<?php
/**
 * Trigger: SendPulse - Subscriber Deleted
 *
 * @package     AutomatorWP_SendPulse
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AutomatorWP_Integration_Trigger' ) ) return;

class AutomatorWP_SendPulse_Subscriber_Deleted_Trigger extends AutomatorWP_Integration_Trigger {
    public $integration = 'sendpulse';
    public $trigger     = 'sendpulse_subscriber_deleted';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A SendPulse subscriber is deleted', 'automatorwp-sendpulse' ),
            'select_option' => __( 'A SendPulse subscriber <strong>is deleted</strong>', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'A SendPulse subscriber is deleted %1$s time(s)', 'automatorwp-sendpulse' ), '{times}' ),
            'log_label'     => __( 'A SendPulse subscriber is deleted', 'automatorwp-sendpulse' ),
            'action'        => 'automatorwp_sendpulse_subscriber_deleted',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2, // email, name
            'options'       => array( 'times' => automatorwp_utilities_times_option() ),
            'tags'          => array_merge( array(
                'sendpulse_email' => array( 'label' => __( 'Subscriber Email', 'automatorwp-sendpulse' ), 'type' => 'text', 'preview' => __( 'The email of the subscriber', 'automatorwp-sendpulse' ) ),
                'sendpulse_name'  => array( 'label' => __( 'Subscriber Name', 'automatorwp-sendpulse' ), 'type' => 'text', 'preview' => __( 'The name of the subscriber', 'automatorwp-sendpulse' ) ),
            ), automatorwp_utilities_times_tag() ),
        ) );
    }

    public function listener( $subscriber_email, $subscriber_name ) {
        $email = sanitize_email( $subscriber_email );
        if ( empty( $email ) ) return;
        $user    = get_user_by( 'email', $email );
        $admins  = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
        $user_id = $user ? (int) $user->ID : ( ! empty( $admins ) ? (int) $admins[0]->ID : 0 );
        automatorwp_trigger_event( array(
            'trigger'             => $this->trigger,
            'user_id'             => $user_id,
            'sendpulse_email'     => $email,
            'sendpulse_name'      => sanitize_text_field( $subscriber_name ),
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
        $log_meta['sendpulse_name']  = isset( $event['sendpulse_name'] ) ? $event['sendpulse_name'] : '';
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) return $log_fields;
        $log_fields['sendpulse_email'] = array( 'name' => __( 'Subscriber Email:', 'automatorwp-sendpulse' ), 'type' => 'text' );
        $log_fields['sendpulse_name']  = array( 'name' => __( 'Subscriber Name:', 'automatorwp-sendpulse' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_SendPulse_Subscriber_Deleted_Trigger();
