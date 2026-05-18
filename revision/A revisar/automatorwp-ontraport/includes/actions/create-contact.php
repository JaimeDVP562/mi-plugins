<?php
/**
 * Create Card
 *
 * @package     AutomatorWP\Integrations\Ontraport\Actions\Create-Contact
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Ontraport_Create_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'ontraport';
    public $action = 'ontraport_create_contact';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register(){
            
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Create a new contact', 'automatorwp-ontraport'),
            'select_option'     => __( 'Create a new <strong>contact</strong>', 'automatorwp-ontraport'),
            /* translators: %1$s: Contact. */
            'edit_label'        => sprintf( __( 'Create a new %1$s', 'automatorwp-ontraport' ), '{contact}' ),
            /* translators: %1$s: Contact. */
            'log_label'         => sprintf( __( 'Create a new %1$s', 'automatorwp-ontraport' ) , '{contact}'),
            'options'           => array(
                'contact' => array(
                    'from' => 'contact',
                    'default' => __('contact', 'automatorwp-ontraport'),
                    'fields' => array(
                        'firstname' => array(
                            'name'          => __('First name:', 'automatorwp-ontraport'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                        'lastname' => array(
                            'name'          => __('Last name: ', 'automatorwp-ontraport'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                        'email' => array(
                            'name'          => __('Email: ', 'automatorwp-ontraport'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                        'phone' => array(
                            'name'          => __('Phone: ', 'automatorwp-ontraport'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                            ),
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
        $first_name = $action_options['firstname'];
        $last_name = $action_options['lastname'];
        $email = $action_options['email'];
        $phone = $action_options['phone'];
        
        // Bail if list_id is empty
        if ( empty ( $first_name ) ||  empty ( $last_name ) ||  empty ( $email ) ||  empty ( $phone ) ) {
            return;
        }

        // Bail if Ontraport not configured
        if( ! automatorwp_ontraport_get_api() ) {
            $this->result = __( 'Ontraport integration is not configured in AutomatorWP settings', 'automatorwp-ontraport' );
            return;
        }

        $response = automatorwp_ontraport_create_contact( $first_name,$last_name, $email, $phone);

        if( $response === 200 ) {
            $this->result = __( 'Created contact', 'autoamtorwp-ontraport' );
        }else {
            $this->result = __( 'The contact could not be created', 'automatorwp-ontraport' );
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
        if( ! automatorwp_ontraport_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Ontraport settings</a> to get this action to work.', 'automatorwp-ontraport' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-ontraport'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-ontraport' ),
                    'https://automatorwp.com/docs/ontraport/'
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
            'name' => __( 'Result:', 'automatorwp-ontraport' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Ontraport_Create_Contact();