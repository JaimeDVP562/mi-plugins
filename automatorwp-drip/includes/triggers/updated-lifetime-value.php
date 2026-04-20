<?php
/**
 * Subscriber Updated Lifetime Value
 *
 * @package     AutomatorWP\Integrations\Drip\Triggers\Updated_Lifetime_Value
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Updated_Lifetime_Value extends AutomatorWP_Integration_Trigger {

    public $integration = 'drip';
    public $trigger     = 'drip_updated_lifetime_value';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( "A Drip subscriber's lifetime value is updated", 'automatorwp-drip' ),
            'select_option' => __( "A Drip subscriber's <strong>lifetime value</strong> is updated", 'automatorwp-drip' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( "A Drip subscriber's lifetime value is updated %1\$s time(s)", 'automatorwp-drip' ), '{times}' ),
            'log_label'     => __( "A Drip subscriber's lifetime value is updated", 'automatorwp-drip' ),
            'action'        => 'automatorwp_drip_updated_lifetime_value',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array( 'times' => automatorwp_utilities_times_option() ),
            'tags'          => array_merge( array(
                'drip_subscriber_email' => array( 'label' => __( 'Subscriber Email', 'automatorwp-drip' ), 'type' => 'text', 'preview' => __( 'The email of the subscriber', 'automatorwp-drip' ) ),
                'drip_lifetime_value'   => array( 'label' => __( 'Lifetime Value', 'automatorwp-drip' ), 'type' => 'text', 'preview' => __( 'The updated lifetime value in cents', 'automatorwp-drip' ) ),
            ), automatorwp_utilities_times_tag() ),
        ) );

    }

    public function listener( $subscriber, $lifetime_value ) {

        $email = isset( $subscriber['email'] ) ? sanitize_email( $subscriber['email'] ) : '';
        if ( empty( $email ) ) return;

        $user    = get_user_by( 'email', $email );
        $admins  = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
        $user_id = $user ? (int) $user->ID : ( ! empty( $admins ) ? (int) $admins[0]->ID : 0 );

        automatorwp_trigger_event( array(
            'trigger'               => $this->trigger,
            'user_id'               => $user_id,
            'drip_subscriber_email' => $email,
            'drip_lifetime_value'   => sanitize_text_field( $lifetime_value ),
        ) );

    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $trigger->type !== $this->trigger ) return $deserves_trigger;
        return $deserves_trigger;
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if ( $trigger->type !== $this->trigger ) return $log_meta;
        $log_meta['drip_subscriber_email'] = isset( $event['drip_subscriber_email'] ) ? $event['drip_subscriber_email'] : '';
        $log_meta['drip_lifetime_value']   = isset( $event['drip_lifetime_value'] ) ? $event['drip_lifetime_value'] : '';
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) return $log_fields;
        $log_fields['drip_subscriber_email'] = array( 'name' => __( 'Subscriber Email:', 'automatorwp-drip' ), 'type' => 'text' );
        $log_fields['drip_lifetime_value']   = array( 'name' => __( 'Lifetime Value:', 'automatorwp-drip' ), 'type' => 'text' );
        return $log_fields;
    }

}

new AutomatorWP_Drip_Updated_Lifetime_Value();
