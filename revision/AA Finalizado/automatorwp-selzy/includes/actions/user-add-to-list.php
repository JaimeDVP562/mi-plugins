<?php
/**
 * Add User To List
 *
 * @package     AutomatorWP\Integrations\Selzy\Actions\User_Add_To_List
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Selzy_Add_User extends AutomatorWP_Integration_Action {

    public $integration = 'selzy';
    public $action = 'selzy_add_user';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to list', 'automatorwp-selzy' ),
            'select_option'     => __( 'Add <strong>user</strong> to <strong>list</strong>', 'automatorwp-selzy' ),
            /* translators: %1$s: List. */
            'edit_label'        => sprintf( __( 'Add user to %1$s', 'automatorwp-selzy' ), '{list}' ),
            /* translators: %1$s: List. */
            'log_label'         => sprintf( __( 'Add user to %1$s', 'automatorwp-selzy' ), '{list}' ),
            'options'           => array(
                'list' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'list',
                    'option_default'    => __( 'list', 'automatorwp-selzy' ),
                    'name'              => __( 'List:', 'automatorwp-selzy' ),
                    'option_none'       => false,
                    'action_cb'         => 'automatorwp_selzy_get_lists',
                    'options_cb'        => 'automatorwp_selzy_options_cb_list',
                    'placeholder'       => 'Select a list',
                    'default'           => ''
                ) ),
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
        $user = get_user_by ( 'ID', $user_id );

        $this->result = '';

        // Bail if Selzy not configured
        if( ! automatorwp_selzy_get_api() ) {
            $this->result = __( 'Selzy integration not configured in AutomatorWP settings.', 'automatorwp-selzy' );
            return;
        }

         // Bail if list is empty
        if ( empty( $list_id ) ) {
            $this->result = __( 'List field is empty.', 'automatorwp-selzy' );
            return;
        }

        $user_email = $user -> user_email;

        // Bail if user is in list
        if(automatorwp_selzy_check_contact($user_email, $list_id )) {
            $this->result = __( 'Contact in list already.', 'automatorwp-selzy' );
            return;
        }
                
        $response = automatorwp_selzy_add_contact( $user_email, $list_id );

        if ( $response === 200 ) {
            $this->result = __( 'Added user to list', 'automatorwp-selzy' );
        } else {
            $this->result = __( 'Couldnt add user to list', 'automatorwp-selzy' );
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
        if( ! automatorwp_selzy_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Selzy settings</a> to get this action to work.', 'automatorwp-selzy' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-selzy'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-selzy' ),
                    'https://automatorwp.com/docs/selzy/'
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
            'name' => __( 'Result:', 'automatorwp-selzy' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Selzy_Add_User();