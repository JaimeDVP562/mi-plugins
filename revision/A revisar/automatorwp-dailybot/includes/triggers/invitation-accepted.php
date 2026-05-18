<?php
/**
 * Trigger: Invitation Accepted
 * 
 * @package   AutomatorWP\Integrations\Dailybot\triggers\Invitation_Accepted
 * @author    AutomatorWP
 * @since     1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Dailybot_Invitation_Accepted extends AutomatorWP_Integration_Trigger {
    public $integration = 'dailybot';
    public $trigger = 'dailybot_invitation_accepted';

    /**
     * Register the trigger
     * 
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'      => true,
            'label'             => __( 'User accept the invitation to dailybot chat', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
            'select_option'     => __('<strong>User accept</strong> the invitation to dailybot chat',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
            'edit_label'        => __('User accept the invitation to dailybot chat',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
            'log_label'         => __('User has accept the invitation to dailybot chat'),
            'action'            => 'automatorwp_dailybot_invitation_accepted',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                // No options
            ),
            /*'tags' => array_merge(
                automatorwp_dailybot_get_webhook_tags()
            )*/
        ));
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
                   automatorwp_trigger_event( array(
                    'trigger'     => $this->trigger,
                    'user_id'     => $user_id,
                    'action_type' => $params['event_type'],
                    'date_time'   => date("Y-m-d H:i:s"),
                    'status'      => $params['results']['status_display'],
                    'created_at'  => $params['results']['created_at'],
                    'email'       => $params['results']['identifier']
                   ));
    }

    public function hooks()
    {
         // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
      
        // Log fields
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

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
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

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}
new AutomatorWP_Dailybot_Invitation_Accepted();