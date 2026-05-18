<?php
/**
 * Remove Contact From List Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Remove_Contact_From_List
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Remove_Contact_From_List extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_remove_contact_from_list';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove a contact from a List', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Remove a contact from a <strong>list</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Remove contact from %1$s', 'automatorwp-elasticmailsender' ), '{list_name}' ),
            'log_label'         => sprintf( __( 'Remove contact from %1$s', 'automatorwp-elasticmailsender' ), '{list_name}' ),
            'options'           => array(
                'list_name' => array(
                    'from'   => 'list_name', 
                    'fields' => array(
                        'contact_email' => array(
                            'name'        => __( 'Contact Email', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the email address of the contact.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                        'list_name' => array(
                            'name'        => __( 'List Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Select the distribution list to remove the contact from.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'select',
                            'options'     => automatorwp_elasticmailsender_get_api_lists(),
                            'required'    => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $contact_email = isset( $action_options['contact_email'] ) ? sanitize_email( $action_options['contact_email'] ) : '';
        $list_name     = isset( $action_options['list_name'] ) ? sanitize_text_field( $action_options['list_name'] ) : '';

        if ( empty( $contact_email ) || empty( $list_name ) ) {
            error_log( 'SCC Action Error: Missing contact email or list name.' );
            return;
        }

        $api_key = get_option( 'elastic_email_api_key', '' );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $payload = array(
            'Emails' => array( $contact_email )
        );

        $endpoint = 'https://api.elasticemail.com/v4/lists/' . rawurlencode( $list_name ) . '/contacts/remove';

        $response = wp_remote_post( $endpoint, array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'          => 'application/json',
                'X-ElasticEmail-ApiKey' => $api_key
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 15
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'SCC Action API Request Error: ' . $response->get_error_message() );
            return;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        
        if ( $status_code !== 200 ) {
            error_log( 'SCC Action API Rejected (' . $status_code . '): ' . $body );
            return;
        }

        error_log( 'SCC Action Success: Contact removed from list ' . $list_name . ' by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Remove_Contact_From_List();