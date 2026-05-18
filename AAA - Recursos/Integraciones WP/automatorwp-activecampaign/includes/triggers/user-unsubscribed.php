<?php
/**
 * User Unsubscribed
 *
 * @package     AutomatorWP\Integrations\ActiveCampaign\Triggers\User_Unsubscribed
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ActiveCampaign_User_Unsubscribed extends AutomatorWP_Integration_Trigger {

    public $integration = 'activecampaign';
    public $trigger = 'activecampaign_user_unsubscribed';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User unsubscribed from ActiveCampaign', 'automatorwp-activecampaign' ),
            'select_option'     => __( '<strong>User</strong> unsubscribed from ActiveCampaign', 'automatorwp-activecampaign' ),
            'edit_label'        => __( 'User unsubscribed from ActiveCampaign', 'automatorwp-activecampaign' ),
            'log_label'         => __( 'User unsubscribed from ActiveCampaign', 'automatorwp-activecampaign' ),
            'action'            => 'automatorwp_activecampaign_user_unsubscribed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                // No options
            ),
            'tags' => array_merge(
                automatorwp_activecampaign_get_webhook_tags()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     * 
     * @param array     $params     Data received
     * @param int       $user_id    User ID
     */
    public function listener( $params, $user_id ) {
  
        // Bail if no user
        if ( $user_id === 0) {
            return;
        }

        $user = get_user_by( 'id', $user_id);
        $email = $user->user_email;
        
        /* translators: %1$s: Email. */
		$this->result = sprintf( __( '%1$s was unsubscribed from ActiveCampaign', 'automatorwp-activecampaign' ), $email );
		
        // Trigger the user is unsubscribed
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'webhook_url'   => get_site_url() . $params['q'],
            'action_type'   => $params['type'],
            'date_time'     => $params['date_time'],
            'email'         => $params['contact']['email'],
            'first_name'    => $params['contact']['first_name'],
            'last_name'     => $params['contact']['last_name'],
        ) );   
        
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
         add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Bail if action type don't match this action
        if( $item_type !== 'trigger' || $object->type !== $this->trigger ) {
            return;
        }

        automatorwp_activecampaign_trigger_notice();

    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if trigger type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;
        $log_meta['webhook_url'] = ( isset( $event['webhook_url'] ) ? $event['webhook_url'] : '' );
        $log_meta['action_type'] = ( isset( $event['action_type'] ) ? $event['action_type'] : '' );
        $log_meta['date_time'] = ( isset( $event['date_time'] ) ? $event['date_time'] : '' );
        $log_meta['email'] = ( isset( $event['email'] ) ? $event['email'] : '' );
        $log_meta['first_name'] = ( isset( $event['first_name'] ) ? $event['first_name'] : '' );
        $log_meta['last_name'] = ( isset( $event['last_name'] ) ? $event['last_name'] : '' );

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

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this action
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        $log_fields['webhook_url'] = array(
            'name' => __( 'Webhook URL:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );
        $log_fields['action_type'] = array(
            'name' => __( 'Action type:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        $log_fields['date_time'] = array(
            'name' => __( 'Date:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        $log_fields['email'] = array(
            'name' => __( 'Email contact:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        $log_fields['first_name'] = array(
            'name' => __( 'First Name:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        $log_fields['last_name'] = array(
            'name' => __( 'Last_name:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        return $log_fields;
    }

    
}

new AutomatorWP_ActiveCampaign_User_Unsubscribed();