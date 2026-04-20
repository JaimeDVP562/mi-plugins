<?php
/**
 * Add User
 *
 * @package     AutomatorWP\Integrations\AWeber\Actions\Add_User
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;


class AutomatorWP_AWeber_Add_User_List extends AutomatorWP_Integration_Action {
    
    public $integration = 'aweber';
    public $action = 'aweber_add_user_list';

    /**
     * Store the action result
     *
     * @since 1.0.0
     *
     * @var string $result
     */
    public $result = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration' => $this->integration,
            'label' => __( 'Add user to list', 'automatorwp' ),
            'select_option' => __( 'Add <strong>user</strong> to list', 'automatorwp' ),
            /* translators: %1$s: List. */
            'edit_label'        => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{list}' ),
            /* translators: %1$s: List. */
            'log_label'         => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{list}' ),
            'options'           => array(
                'list' => array(
                    'from' => 'lists',
                    'default' => __( 'list', 'automatorwp' ),
                    'fields' => array(
                        'account' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __( 'Account:', 'automatorwp' ),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_aweber_get_accounts',
                            'options_cb'        => 'automatorwp_aweber_options_cb_account',
                            'placeholder'       => 'Select an account',
                            'default'           => ''
                        ) ),
                        'lists' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __( 'List:', 'automatorwp' ),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_aweber_get_lists',
                            'options_cb'        => 'automatorwp_aweber_options_cb_list',
                            'placeholder'       => 'Select a list',
                            'default'           => ''
                        ) ),      
                    ),
                )
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
    public function execute( $action, $user_id, $action_options, $automation){

        $params = automatorwp_aweber_get_request_parameters();

        // Bail if the authorization has not been setup from settings
        if( $params === false ) {
            $this->result = __( 'AWeber integration not configured in AutomatorWP settings.', 'automatorwp' );
            return;
        }

        // Shorthand
        $user = get_user_by ( 'ID', $user_id );
        $account_id = $action_options['account'];
        $list_id = $action_options['lists'];

        $subscriber_data = array(
            'email' => $user->user_email,
            'name'  => $user->first_name . ' ' .$user->last_name,
            'update_existing' => true,
        );

        $response = automatorwp_aweber_add_subscriber( $subscriber_data, $account_id, $list_id );

        if ( isset( $response['error'] ) ){
            $this->result = sprintf( __( '%s could not be added to the list in AWeber. Error: %s, %s', 'automatorwp' ), $user->user_email, $response['error']['status'], $response['error']['message'] );
        } else {
            $this->result = sprintf( __( '%s added to list in AWeber', 'automatorwp' ), $user->user_email );
        }
        
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

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

new AutomatorWP_AWeber_Add_User_List();