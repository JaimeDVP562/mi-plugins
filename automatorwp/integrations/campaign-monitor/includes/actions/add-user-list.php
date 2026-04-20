<?php
/**
 * Add User List
 *
 * @package AutomatorWP\Integrations\Campaign_Monitor\Actions\Add_User_List
 * @since 1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Campaign_Monitor_Add_User_List extends AutomatorWP_Integration_Action {

    public $integration = 'campaign_monitor';
    public $action = 'campaignmonitor_add_user_list';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to list', 'automatorwp' ),
            'select_option'     => __( 'Add <strong>user</strong> to <strong>list</strong>', 'automatorwp' ),
            /* translators: %1$s: Subscriber. %2$s: List name. */
            'edit_label'        => sprintf( __( 'Add %1$s to %2$s', 'automatorwp' ), '{subscriber}', '{list}' ),
            /* translators: %1$s: Subscriber. %2$s: List name. */
            'log_label'         => sprintf( __( 'Add %1$s to %2$s', 'automatorwp' ), '{subscriber}', '{list}' ),
            'options'           => array(
                'list' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'list',
                    'option_default'    => __( 'list', 'automatorwp' ),
                    'name'              => __( 'List:', 'automatorwp' ),
                    'option_none'       => false,
                    'action_cb'         => 'automatorwp_campaign_monitor_get_lists',
                    'options_cb'        => 'automatorwp_campaign_monitor_options_cb_list',
                    'placeholder'       => 'Select a list',
                    'default'           => ''
                ) ),
                'subscriber' => array(
                    'from' => '',
                    'default' => __( 'user', 'automatorwp' ),
                    'fields' => array(
                        'phone' => array(
                            'name' => __( 'Phone:', 'automatorwp' ),
                            'type' => 'text',
                        ),
                        'consent_track' => array(
                            'name' => __( 'Consent to track email:', 'automatorwp' ),
                            'desc' => __( 'Check to enable to allow tracking of this email address.', 'automatorwp' ),
                            'type' => 'checkbox',
                            'classes' => 'cmb2-switch',
                        ),
                        'consent_sms' => array(
                            'name' => __( 'Consent to send SMS:', 'automatorwp' ),
                            'desc' => __( 'Check to enable to allow sending sms.', 'automatorwp' ),
                            'type' => 'checkbox',
                            'classes' => 'cmb2-switch',
                        ),
                    )
                )
            ),

        ));
    }
    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass $action
     * @param int $user_id
     * @param array $action_options
     * @param stdClass $automation
     */
    public function execute($action, $user_id, $action_options, $automation) {

        // Shorthand
        $list_id = $action_options['list'];
        $user = get_user_by ( 'ID', $user_id );
        $subscriber_email = $user->user_email;
        $subscriber_name = $user->first_name . ' ' . $user->last_name;
        $subscriber_phone = $action_options['phone'];
        $consent_track = (bool) $action_options['consent_track'];
        $consent_sms = (bool) $action_options['consent_sms'];

        $this->result = '';

        // Bail if list is empty
        if ( empty ( $list_id ) ){
            $this->result = __( 'No list selected.', 'automatorwp' );
            return;
        }

        $subscriber_data = array(
            'EmailAddress' => $subscriber_email,
            'Name' => $subscriber_name,
            'MobileNumber' => $subscriber_phone,
            'Resubscribe' => true,
            'ConsentToTrack' => $consent_track ? 'Yes' : 'No',
            'ConsentToSendSms' => $consent_sms ? 'Yes' : 'No',
        );

        // Obtain the Campaign Monitor API
        $api = automatorwp_campaign_monitor_get_api();

        if (!$api) {
            $this->result = __( 'Campaign Monitor API credentials are not set.', 'automatorwp' );
            return;
        }

        // To get information from user to handle
        $response_get = automatorwp_campaign_monitor_get_subscriber( $list_id, $subscriber_email );
        $status_code_get = wp_remote_retrieve_response_code( $response_get );
        $response_body_get = json_decode( wp_remote_retrieve_body( $response_get ), true );
        
        if ( $status_code_get === 400 ) {

            $response = automatorwp_campaign_monitor_add_subscriber( $list_id, $subscriber_data );

            $status_code = wp_remote_retrieve_response_code( $response );
            $response_body = json_decode( wp_remote_retrieve_body( $response ), true );
            
            if ($status_code === 201) {
                $this->result = sprintf( __( '%s added to list', 'automatorwp' ), $subscriber_email );
            } else {
                if ( isset ( $response_body['Message'] ) ) {
                    $error_message = $response_body['Message'];
                } else {
                    $error_message = $status_code;
                }
                $this->result = sprintf( __( 'The user could not be added. Error: %s', 'automatorwp'), $error_message );
            }
            
        } else {
            $this->result = sprintf( __( 'The user %s is already on the list', 'automatorwp' ), $subscriber_email );
            return;
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
        if( ! automatorwp_campaign_monitor_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Campaign Monitor settings</a> to get this action to work.', 'automatorwp' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-campaign_monitor'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp' ),
                    'https://automatorwp.com/docs/campaign-monitor/'
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
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Campaign_Monitor_Add_User_List();