<?php
/**
 * Send Message
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Send-Message
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Keap_Send_Message extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action = 'keap_send-message';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register(){
            
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Send a menssage', 'automatorwp-keap'),
            'select_option'     => __( 'Send a <strong>message</strong>', 'automatorwp-keap'),
            /* translators: %1$s: Card. */
            'edit_label'        => sprintf( __( 'Send a %1$s to %2$s', 'automatorwp-keap' ), '{message}', '{to}' ),
            /* translators: %1$s: Card. */
            'log_label'         => sprintf( __( 'Send a %1$s to %2$s', 'automatorwp-keap' ) , '{message}', '{to}' ),
            'options'           => array(
                'message' => array(
                    'from' => 'message',
                    'default' => __('message', 'automatorwp-keap'),
                    'fields' => array(
                        'message_content' => array(
                            'name'          => __('Message Content:', 'automatorwp-keap'),
                            'type'          => 'textarea',
                            'default'       => '',
                            'required'      => true
                        )
                    ),
                ),
                'to' => array(
                      'from' => 'to',
                      'default' => __('to', 'automatorwp-keap'),
                      'fields' => array(
                          'to_user' => array(
                              'name'          => __('To (user handle):', 'automatorwp-keap'),
                              'desc'          => __('You can only send messages to people that you follow.', 'automatorwp-keap'),
                              'type'          => 'text',
                              'default'       => '',
                              'required'      => true
                          )
                      ),
                    ),
                ),
            ),
        );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        
        // Shorthand
        $message_content = $action_options['message_content'];
        $to_user = $action_options['to_user'];

        // Bail if Keap not configured
        if( ! automatorwp_keap_get_api() ) {
            $this->result = __( 'Keap integration is not configured in AutomatorWP settings', 'automatorwp-keap' );
            return;
        }

        $response = automatorwp_keap_send_message( $message_content, $to_user );

        if( $response === 200 ) {
            $this->result = __( 'Message sent!', 'autoamtorwp-keap' );
        }else {
            $this->result = __( 'The message could not be sent', 'automatorwp-keap' );
        }

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
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

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
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_keap_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Keap settings</a> to get this action to work.', 'automatorwp-keap' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-keap'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-keap' ),
                    'https://automatorwp.com/docs/keap/'
                ); ?>
            </div>
        <?php endif;
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
            'name' => __( 'Result:', 'automatorwp-keap' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Keap_Send_Message();