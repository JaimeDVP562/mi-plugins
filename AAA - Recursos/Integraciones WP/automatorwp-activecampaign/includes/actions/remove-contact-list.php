<?php
/**
 * Remove contact from list
 *
 * @package     AutomatorWP\Integrations\ActiveCampaign\Actions\Remove_Contact_List
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ActiveCampaign_Remove_Contact_List extends AutomatorWP_Integration_Action {

    public $integration = 'activecampaign';
    public $action = 'activecampaign_remove_contact_list';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove contact from list', 'automatorwp-activecampaign' ),
            'select_option'     => __( 'Remove <strong>contact</strong> from <strong>list</strong>', 'automatorwp-activecampaign' ),
            /* translators: %1$s: Contact. %2$s: List name. */
            'edit_label'        => sprintf( __( 'Remove %1$s from %2$s', 'automatorwp-activecampaign' ), '{contact}', '{list}' ),
            /* translators: %1$s: Contact. %2$s: List name.  */
            'log_label'         => sprintf( __( 'Remove %1$s from %2$s', 'automatorwp-activecampaign' ), '{contact}', '{list}' ),
            'options'           => array(
                'list' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'list',
                    'option_default'    => __( 'Select a list', 'automatorwp-activecampaign' ),
                    'name'              => __( 'List:', 'automatorwp-activecampaign' ),
                    'action_cb'         => 'automatorwp_activecampaign_get_lists',
                    'options_cb'        => 'automatorwp_activecampaign_options_cb_list',
                    'default'           => ''
                ) ),
                'contact' => array(
                    'from' => 'user_email',
                    'default' => __( 'contact', 'automatorwp-activecampaign' ),
                    'fields' => array(
                        'user_email' => array(
                            'name' => __( 'Email:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The contact email address.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'required'  => true,
                            'default' => ''
                        ),
                     ) )
            ),
        ) );

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
        $list_id = $action_options['list'];        
        $contact_data = wp_parse_args ( $action_options, array(
            'user_email'    => '',
        ));

        // Bail if email field is empty
        if ( empty( $contact_data['user_email'] ) ){
            $this->result = __( 'Email contact field is empty', 'automatorwp-activecampaign' );
            return;
        }
        
        $this->result = '';

        // Bail if ActiveCampaign not configured
        if( ! automatorwp_activecampaign_get_api() ) {
            $this->result = __( 'ActiveCampaign integration not configured in AutomatorWP settings.', 'automatorwp-activecampaign' );
            return;
        }
        
        $response = automatorwp_activecampaign_get_contact( $contact_data['user_email'] );

        // Bail if user doesn't exist in ActiveCampaign
        if (empty($response['contacts'])){
            
            $this->result = sprintf( __( '%s is not registered in ActiveCampaign', 'automatorwp-activecampaign' ), $contact_data['user_email'] );
            return;

        } else {          
            
            foreach ( $response['contacts'] as $contact ){
                $contact_id = $contact['id'];
            }
            
        }

        // To get the lists associated to the user
        $response = automatorwp_activecampaign_get_contact_lists( $contact_id );

        if ( !empty( $response['contactLists'] ) ){

            $user_in_list = false;
        
            foreach ( $response['contactLists'] as $contact_list ){
        
                // Bail if contact is already unsubscribed to that list
                if ( $list_id == $contact_list['list'] && $contact_list['status'] == '2'){
                    $this->result = sprintf( __( '%s is already unsubscribed to that list', 'automatorwp-activecampaign' ), $contact_data['user_email'] );
                    return;
                }

                if ( $list_id == $contact_list['list'] ) {
                    $user_in_list = true;
                }

            }

            // Bail if user is not associated to the list
            if ($user_in_list == false){

                $this->result = sprintf( __( '%s is not subscribed to that list', 'automatorwp-activecampaign' ), $contact_data['user_email'] );
                return;
            }
        
        } else {

            // Bail if user is not subscribed to any list
            $this->result = sprintf( __( '%s is not subscribed to any list', 'automatorwp-activecampaign' ), $contact_data['user_email'] );
            return;

        }

        // Value to unsubscribe the contact
        $status = '2';

        // To unsubscribe the user to selected list
        automatorwp_activecampaign_subscription_lists( $list_id, $contact_id, $status );   

        $this->result = sprintf( __( '%s removed from list', 'automatorwp-activecampaign' ), $contact_data['user_email'] );

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
        if( ! automatorwp_activecampaign_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">ActiveCampaign settings</a> to get this action to work.', 'automatorwp-activecampaign' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-activecampaign'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-activecampaign' ),
                    'https://automatorwp.com/docs/activecampaign/'
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
            'name' => __( 'Result:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_ActiveCampaign_Remove_Contact_List();