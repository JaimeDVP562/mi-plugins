<?php
/**
 * Add or Update Contact Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Add_Update_Contact
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Add_Update_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_add_update_contact';

    /**
     * Register the action in AutomatorWP UI
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add or update a contact', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Add or update a <strong>contact</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Add or update contact %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
            'log_label'         => sprintf( __( 'Add or update contact %1$s', 'automatorwp-elasticmailsender' ), '{contact_email}' ),
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
                        'first_name' => array(
                            'name'        => __( 'First Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the first name (Optional).', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => false,
                        ),
                        'last_name' => array(
                            'name'        => __( 'Last Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the last name (Optional).', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => false,
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Execute the action
     *
     * @since 1.0.0
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's configured options
     * @param stdClass $automation     The automation object
     * @return void
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $contact_email = isset( $action_options['contact_email'] ) ? sanitize_email( $action_options['contact_email'] ) : '';
        $first_name    = isset( $action_options['first_name'] ) ? sanitize_text_field( $action_options['first_name'] ) : '';
        $last_name     = isset( $action_options['last_name'] ) ? sanitize_text_field( $action_options['last_name'] ) : '';

        if ( empty( $contact_email ) ) {
            error_log( 'SCC Action Error: Missing contact email.' );
            return;
        }

        $api_key = get_option( 'elastic_email_api_key', '' );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        // SCC Architecture: Build the contact object
        $contact_data = array( 'Email' => $contact_email );
        
        if ( ! empty( $first_name ) ) {
            $contact_data['FirstName'] = $first_name;
        }
        
        if ( ! empty( $last_name ) ) {
            $contact_data['LastName'] = $last_name;
        }

        // Elastic Email v4 expects an array of contact objects
        $payload = array( $contact_data );

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

        error_log( 'SCC Action Success: Contact profile added/updated by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Add_Update_Contact();