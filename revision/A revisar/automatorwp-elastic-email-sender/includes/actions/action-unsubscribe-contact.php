<?php
/**
 * Unsubscribe Contact Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Unsubscribe_Contact
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Unsubscribe_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_unsubscribe_contact';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Unsubscribe a contact', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Unsubscribe a <strong>contact</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Unsubscribe contact %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
            'log_label'         => sprintf( __( 'Unsubscribe contact %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
            'options'           => array(
                'contact_email' => array(
                    'from'   => 'contact_email', 
                    'fields' => array(
                        'contact_email' => array(
                            'name'        => __( 'Contact Email', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the email address of the contact to unsubscribe.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $contact_email = isset( $action_options['contact_email'] ) ? sanitize_email( $action_options['contact_email'] ) : '';

        if ( empty( $contact_email ) ) {
            error_log( 'SCC Action Error: Missing contact email.' );
            return;
        }

        $api_key = get_option( 'elastic_email_api_key', '' );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $payload = array(
            array(
                'Email'  => $contact_email,
                'Status' => 'Unsubscribed'
            )
        );

        $response = wp_remote_post( 'https://api.elasticemail.com/v4/contacts', array(
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

        error_log( 'SCC Action Success: Contact explicitly unsubscribed by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Unsubscribe_Contact();