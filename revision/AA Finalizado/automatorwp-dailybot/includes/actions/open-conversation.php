<?php
/**
 * Action: Open conversation
 * 
 * @since       1.0.0
 * @package     AutomatorWP\Integrations\Dailybot\Actions\Open_Conversation
 * @author      AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Dailybot_Open_Conversation extends AutomatorWP_Integration_Action{
    
    public $integration = 'dailybot';
    public $action      = 'dailybot_open_conversation';

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
                'label'         => __( 'Open a conversation via Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                'select_option' => __( '<strong>Open</strong> a <strong>conversation</strong>', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                'edit_label'    => sprintf( __( 'Open conversation with %1$s', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ), '{target_user}' ),
                'log_label'     => sprintf( __( 'Open conversation with %1$s', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ), '{target_user}' ),
                'options'       => array(
                    'target_user' => array(
                        'from'   => 'target_user',
                        'fields' => array(
                            'target_user' => array(
                                'name'     => __( 'User ID:', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'desc'     => __( 'The Dailybot ID of the person to start a conversation with.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
                                'type'     => 'text',
                                'required' => true,
                                'default'  => 'User ID'
                            ),
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
    public function execute( $action, $user_id, $action_options, $automation ) {
        $key = automatorwp_dailybot_get_option('key','');

        if( empty($key) ){
            $this->result =  __('Dailybot integration not configured in AutomatorWP settings.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);
            return;
        }

        if( empty($action_options) || !isset($action_options['target_user']) || empty($action_options['target_user']) ){
            $this->result = __('Incomplete open conversation fields',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN);
            return;
            }
            $username_uuid = $action_options['target_user'];

            if( $response = automatorwp_dailybot_open_conversation($username_uuid) ){
                $this->result = $response;
            }
    }

    public function hooks() {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function configuration_notice($object,$item_type){
        if($item_type !=='action' || $object->type !== $this->action) return;
        if(function_exists('automatorwp_dailybot_get_api') && !automatorwp_dailybot_get_api()):?>
            <div class="automatorwp-notice-warning" style="margin-top:10px;">
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
    
    public function log_fields($log_fields,$log,$object){
        if($log->type === 'action' && $object->type === $this->action){
            $log_fields['result']= array(
                'name' => __( 'Result:', 'dailybot' ),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}
new AutomatorWP_Dailybot_Open_Conversation();
