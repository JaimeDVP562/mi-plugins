<?php
/**
 * Send Email Template Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Send_Email_Template
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Send_Email_Template extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_send_email_template';

    /**
     * Register the action in AutomatorWP UI
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Send an email using a Template', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Send an <strong>email using a template</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Send template to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
            'log_label'         => sprintf( __( 'Send template to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
            'options'           => array(
                'recipient_email' => array(
                    'from'   => 'recipient_email', 
                    'fields' => array(
                        'recipient_email' => array(
                            'name'        => __( 'To', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the recipient email address.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                        'template_name' => array(
                            'name'        => __( 'Template Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Select the template created in Elastic Email.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'select',
                            // SCC Architecture: Fetch data using the generic helper function
                            'options'     => automatorwp_elasticmailsender_get_api_templates(),
                            'required'    => true,
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
        
        $recipient_email = isset( $action_options['recipient_email'] ) ? sanitize_email( $action_options['recipient_email'] ) : '';
        $template_name   = isset( $action_options['template_name'] ) ? sanitize_text_field( $action_options['template_name'] ) : '';

        if ( empty( $recipient_email ) || empty( $template_name ) ) {
            error_log( 'SCC Action Error: Missing recipient or template name.' );
            return;
        }
        
        $api_key    = get_option( 'elastic_email_api_key', '' );
        $from_email = get_option( 'elastic_email_from_address', get_bloginfo( 'admin_email' ) );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $payload = array(
            'Recipients' => array(
                'To' => array( $recipient_email )
            ),
            'Content' => array(
                'TemplateName' => $template_name,
                'From'         => $from_email,
            )
        );

        $response = wp_remote_post( 'https://api.elasticemail.com/v4/emails/transactional', array(
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

        error_log( 'SCC Action Success: Template email processed by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Send_Email_Template();