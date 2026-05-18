<?php
/**
 * Update Contact Custom Field Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Update_Contact_Field
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Update_Contact_Field extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_update_contact_field';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Update a custom field for a contact', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Update a <strong>custom field</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Update a custom field for %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
            'log_label'         => sprintf( __( 'Update a custom field for %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
            'options'           => array(
                'contact_email' => array(
                    'from'   => 'contact_email', 
                    'fields' => array(
                        'contact_email' => array(
                            'name'        => __( 'Contact Email', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the email address of the contact.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                        'field_name' => array(
                            'name'        => __( 'Field Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'The exact name of the custom field in Elastic Email (e.g. city, company).', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                        'field_value' => array(
                            'name'        => __( 'Field Value', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'The value to save in this field.', 'automatorwp-elasticmailsender' ),
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
        $field_name    = isset( $action_options['field_name'] ) ? sanitize_text_field( $action_options['field_name'] ) : '';
        $field_value   = isset( $action_options['field_value'] ) ? sanitize_text_field( $action_options['field_value'] ) : '';

        if ( empty( $contact_email ) || empty( $field_name ) ) {
            error_log( 'SCC Action Error: Missing contact email or field name.' );
            return;
        }

        $api_key = get_option( 'elastic_email_api_key', '' );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $payload = array(
            array(
                'Email'        => $contact_email,
                'CustomFields' => array(
                    $field_name => $field_value
                )
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

        error_log( 'SCC Action Success: Custom field updated by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Update_Contact_Field();