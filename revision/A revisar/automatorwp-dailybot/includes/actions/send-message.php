<?php
/**
 * Action: Send message
 * 
 * @since          1.0.0
 * @package        AutomatorWP\Integrations\Dailybot\Actions\Send_Message
 * @author         AutomatorWP
 */
//Exit if accesed directly
if(!defined ('ABSPATH')) exit;

class AutomatorWP_DailyBot_Send_Message extends AutomatorWP_Integration_Action{
    
    public $integration = 'dailybot';
    public $action = 'dailybot_send_message';

    /**
     * Register the action
     * 
     * @since 1.0.0
     */
    public function register(){
        
        automatorwp_register_action(
            $this->action,
            array(
                'integration'   => $this->integration,
                'label'         => __( 'Send a message via Dailybot', 'dailybot' ),
                'select_option' => __( '<strong>Send</strong> a <strong>message</strong>', 'dailybot' ),
                'edit_label'    => sprintf( __( 'Send message %1$s %2$s', 'dailybot' ), '{user_id}' ,'{message_text}' ),
                'log_label'     => sprintf( __( 'Send message %1$s %2$s', 'dailybot' ), '{user_id}' ,'{message_text}' ),
                'options'       => array(
                'user_id' => array(
                        'from'      => 'user_id',
                        'fields'    => array(
                            'user_id' => array(
                                'name'      => __( 'User ID:', 'dailybot' ),
                                'desc'      => __( 'The ID where the message will be sent.', 'dailybot' ),
                                'type'      => 'text',
                                'required'  => true,
                                'default'   => 'User ID'
                            )
                            
                        )
                    ),
                'message_text'  => array(
                        'from'    => 'message_text',
                        'fields'  => array(
                            'message_text' => array(
                                'name'      => __( 'Message:', 'dailybot' ),
                                'desc'     => __( 'The content of the message.', 'dailybot' ),
                                'type'     => 'textarea',
                                'required' => true,
                                'default'  => 'Message'
                            )
                        )
                )
                
                    )
            )
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
    public function execute($action,$user_id,$action_options,$automation){

    $key = automatorwp_dailybot_get_option('key','');


        if( empty($key) ){
           $this->result = __('Dailybot integration not configured in AutomatorWP settings.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);    
           return;
           }

        if(  empty($action_options) || !isset($action_options['user_id']) || !isset($action_options['message_text']) ){
             $this->result = __('Incomplete send message fields.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);
             return;
            }

            $username_uuid = $action_options['user_id'];
       
         if( $response = automatorwp_dailybot_send_message($username_uuid,$action_options['message_text']) ){
            $this->result = $response;
           }

    }

    /**
     * Register required hooks
     */
    public function hooks(){
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function configuration_notice($object,$item_type){
        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if(function_exists('automatorwp_dailybot_get_api') && ! automatorwp_dailybot_get_api()) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px;">
                <?php printf( __( 'Please configure <a href="%s">Dailybot settings</a>.', 'dailybot' ), admin_url( 'admin.php?page=automatorwp_settings&tab=dailybot' ) ); ?>
            </div>
        <?php endif;
    }

    public function log_meta($log_meta,$action,$user_id,$action_options,$automation){
        if($action->type === $this->action){
            $log_meta['result'] = $this->result;
        }
        return $log_meta;
    }

    public function log_fields($log_fields,$log,$object){
        if($log->type==='action' && $object->type === $this->action){
            $log_fields['result'] = array(
                'name' => __( 'Result:', 'dailybot' ),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}

new AutomatorWP_DailyBot_Send_Message();