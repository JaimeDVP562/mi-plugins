<?php
/**
 * Delete Contacts
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Delete-Contacts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Smoove_Delete_Contacts extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_delete_contacts';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        error_log( 'Registering Smoove action: ' . $this->action );
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Delete a contact', 'automatorwp-smoove'),
            'select_option'     => __( 'Delete a <strong>contact</strong>', 'automatorwp-smoove'),
            'edit_label'        => sprintf( __( 'Delete %1$s', 'automatorwp-smoove' ), '{contact_id}' ),
            'log_label'         => sprintf( __( 'Deleted %1$s', 'automatorwp-smoove' ) , '{contact_id}' ),
            'options'           => array(
                'contact_id' => array(
                    'from' => 'contact',
                    'default' => __('contact ID', 'automatorwp-smoove'),
                    'fields' => array(
                        'contact_id' => array(
                            'name'          => __('Contact ID:', 'automatorwp-smoove'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        // Shorthand
        $contact_id = $action_options['contact_id'];

        // Bail if required field is empty
        if ( empty($contact_id) ) {
            return;
        }

        // Bail if Smoove not configured
        if( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }
        $api_key = automatorwp_smoove_get_option( 'api_key' );

        // Call API to delete contact in Smoove
        $response = automatorwp_smoove_delete_contact( $contact_id );

        // Check API response
        if( $response === 200 ) {
            $this->result = __( 'Contact deleted successfully in Smoove', 'automatorwp-smoove' );
        } else {
            $this->result = __( 'Failed to delete contact in Smoove', 'automatorwp-smoove' );
        }
    }
}
new AutomatorWP_Smoove_Delete_Contacts();
