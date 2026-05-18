<?php
/**
 * Update contact
 *
 * @package     AutomatorWP\Integrations\ActiveCampaign\Actions\Update_Contact
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ActiveCampaign_Update_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'activecampaign';
    public $action = 'activecampaign_update_contact';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Update contact in ActiveCampaign', 'automatorwp-activecampaign' ),
            'select_option'     => __( 'Update <strong>contact</strong> in ActiveCampaign', 'automatorwp-activecampaign' ),
            /* translators: %1$s: Contact. */
            'edit_label'        => sprintf( __( 'Update %1$s in ActiveCampaign', 'automatorwp-activecampaign' ), '{contact}' ),
            /* translators: %1$s: Contact. */
            'log_label'         => sprintf( __( 'Update %1$s in ActiveCampaign', 'automatorwp-activecampaign' ), '{contact}' ),
            'options'           => array(
                'contact' => array(
                    'from' => 'email',
                    'default' => __( 'contact', 'automatorwp-activecampaign' ),
                    'fields' => array(
                        'email' => array(
                            'name' => __( 'Email:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The user email address.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'required'  => true,
                            'default' => ''
                        ),
                        'first_name' => array(
                            'name' => __( 'First Name:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The user\'s first name.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                        'last_name' => array(
                            'name' => __( 'Last Name:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The user\'s last name.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                        'phone' => array(
                            'name' => __( 'Phone:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The user\'s phone.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
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
    public function execute( $action, $user_id, $action_options, $automation ) {

        $contact_data = array(
            'email'         => $action_options['email'],
            'firstName'    => $action_options['first_name'],
            'lastName'     => $action_options['last_name'],
            'phone'         => $action_options['phone'],
        );

        $this->result = '';
        
        // Bail if user email is empty
        if ( empty( $contact_data['email'] ) ) {
            $this->result = __( 'Email contact field is empty.', 'automatorwp-activecampaign' );
            return;
        }

        // Bail if ActiveCampaign not configured
        if( ! automatorwp_activecampaign_get_api() ) {
            $this->result = __( 'ActiveCampaign integration not configured in AutomatorWP settings.', 'automatorwp-activecampaign' );
            return;
        }

        $response = automatorwp_activecampaign_get_contact( $contact_data['email'] );

        // Update if contact exists in ActiveCampaign
        if ( ! empty( $response['contacts'] ) ) {

            foreach ( $response['contacts'] as $contact ) {
                $contact_id = $contact['id'];
            }

            // To avoid update data with empty fields
            foreach ( $contact_data as $key => $value_update ) {
                if ( empty( $value_update ) ) {
                    unset($contact_data[$key]);
                }
            }

            automatorwp_activecampaign_update_contact( $contact_id, $contact_data );

            $this->result =  sprintf( __( 'Contact %s updated', 'automatorwp-activecampaign' ), $contact_data['email'] );

        } else {
        
            $this->result = sprintf( __( '%s is not registered in ActiveCampaign', 'automatorwp-activecampaign' ), $contact_data['email'] );
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

new AutomatorWP_ActiveCampaign_Update_Contact();