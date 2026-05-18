<?php
/**
 * Trigger: Invitation Created
 *
 * @package  AutomatorWP\Integrations\Dailybot\Triggers\Invitation_Created
 * @author   AutomatorWP
 * @since    1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Dailybot_Invitation_Created extends AutomatorWP_Integration_Trigger {

    public $integration = 'dailybot';
    public $trigger     = 'dailybot_invitation_created';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'anonymous'     => true,
            'label'         => __( 'User is invited to Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'select_option' => __( '<strong>User</strong> is invited to Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'edit_label'    => __( 'User is invited to Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'log_label'     => __( 'User was invited to Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'action'        => 'automatorwp_dailybot_invitation_created',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(),
            'tags'          => automatorwp_dailybot_get_webhook_tags(),
        ) );
    }

    public function listener( $params, $user_id ) {

        automatorwp_trigger_event( array(
            'trigger'    => $this->trigger,
            'user_id'    => $user_id,
            'event_type' => isset( $params['event_type'] ) ? $params['event_type'] : '',
            'status'     => isset( $params['results']['status_display'] ) ? $params['results']['status_display'] : '',
            'created_at' => isset( $params['results']['created_at'] ) ? $params['results']['created_at'] : '',
            'email'      => isset( $params['results']['identifier'] ) ? $params['results']['identifier'] : '',
        ) );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $automation ) {
        if ( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        $log_meta['email']  = isset( $event['email'] ) ? $event['email'] : '';
        $log_meta['status'] = isset( $event['status'] ) ? $event['status'] : '';
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) {
            return $log_fields;
        }
        $log_fields['email'] = array(
            'name' => __( 'Email:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'type' => 'text',
        );
        $log_fields['status'] = array(
            'name' => __( 'Status:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'type' => 'text',
        );
        return $log_fields;
    }
}

new AutomatorWP_Dailybot_Invitation_Created();