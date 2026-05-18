<?php
/**
 * Action: Send email via Dailybot
 * 
 * @since       1.0.0
 * @package     AutomatorWP\Integrations\Dailybot\Actions\Send_Email
 * @author      AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DailyBot_Send_Email extends AutomatorWP_Integration_Action {

    public $integration = 'dailybot';
    public $action      = 'dailybot_send_email';

    /**
     * Register the action
     * 
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action(
            $this->action,
            array(
                'integration'   => $this->integration,
                'label'         => __( 'Send an email via Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                'select_option' => __( '<strong>Send</strong> an <strong>email</strong>', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                'edit_label'    => sprintf( __( 'Send email to %1$s %2$s %3$s %4$s', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ), '{receiver_username}', '{receiver_email}', '{subject}','{email_content}'),
                'log_label'     => sprintf( __( 'Send email to %1$s %2$s %3$s %4$s', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ), '{receiver_username}', '{receiver_email}', '{subject}', '{email_content}' ),
                'options'       => array(
                    'receiver_username' => array(
                        'from'   => 'receiver_username',
                        'fields' => array(
                            'receiver_username' => array(
                                'name'     => __( 'Username dailybot receiver:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'desc'     => __( 'The username sender.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'type'     => 'text',
                                'required' => true,
                                'default'  => __('Receiver username', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
                            ),
                        )  ),
                        'receiver_email' => array(
                            'from' => 'receiver_email',
                            'fields' => array(
                                'receiver_email' => array(
                                'name'     => __( 'To:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'desc'     => __( 'The receiver email address.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'type'     => 'text',
                                'required' => true,
                                'default'  => 'Joe@example.com',
                                )
                            ),),                            
                            'subject' => array(
                                'from' => 'subject',
                                'fields' => array(
                                    'subject' => array(
                                'name'     => __( 'Subject:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'desc'     => __( 'The email subject.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'type'     => 'text',
                                'required' => true,
                                'default'  => 'subject',
                                    )
                )),
                            'email_content' => array(
                                'from'   => 'email_content',
                                'fields' => array(
                                    'email_content' => array(
                                'name'     => __( 'Message:', 'dailybot' ),
                                'desc'     => __( 'The email body content.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'type'     => 'textarea',
                                'required' => true,
                                'default'  => 'email content',
                                ))
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
    
    $key = automatorwp_dailybot_get_option('key','');

    if( empty($key) ){
        $this->result = __('Dailybot integration not configured in AutomatorWP settings.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);    
    }

    if(  empty($action_options) || !isset($action_options['subject']) || !isset($action_options['receiver_username']) || !isset($action_options['email_content']) ){
    $this->result = __('Incomplete send email fields.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);
    return;
    }
     $username_uuid = automatorwp_dailybot_get_uuid($action_options['receiver_username']);
       
     if( $response = automatorwp_dailybot_send_email($username_uuid,$action_options['subject'],$action_options['email_content']) ){
            $this->result = $response;
       }

    }

    public function hooks() {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function configuration_notice( $object, $item_type ) {
        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if ( function_exists( 'automatorwp_dailybot_get_api' ) && ! automatorwp_dailybot_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px;">
                <?php printf( __( 'Please configure <a href="%s">Dailybot settings</a>.', 'dailybot' ), admin_url( 'admin.php?page=automatorwp_settings&tab=dailybot' ) ); ?>
            </div>
        <?php endif;
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if ( $action->type === $this->action ) {
            $log_meta['result'] = $this->result;
        }
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type === 'action' && $object->type === $this->action ) {
            $log_fields['result'] = array(
                'name' => __( 'Result:', 'dailybot' ),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}

new AutomatorWP_DailyBot_Send_Email();