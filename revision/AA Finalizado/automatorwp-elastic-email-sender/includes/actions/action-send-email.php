<?php
/**
 * Send Email Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Send_Email
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Send_Email extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action = 'elasticmailsender_send_email';


public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Send an email via Elastic Email', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Send an <strong>email</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Send email to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
            'log_label'         => sprintf( __( 'Send email to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
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
                        
                        'email_subject' => array(
                            'name'        => __( 'Subject', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                        
                        'email_body' => array(
                            'name'        => __( 'Content', 'automatorwp-elasticmailsender' ),
                            'type'        => 'textarea',
                            'required'    => true,
                        ),

                    ),
                ),

            ),
        ) );
    }

    /**
     * Action execution function
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
        $email_subject   = isset( $action_options['email_subject'] ) ? sanitize_text_field( $action_options['email_subject'] ) : '';
        $email_body      = isset( $action_options['email_body'] ) ? wp_kses_post( $action_options['email_body'] ) : '';

        if ( empty( $recipient_email ) || empty( $email_body ) ) {
            error_log( 'SCC Action Error: Missing recipient or body.' );
            return;
        }

        // We need to verify if this is the EXACT option name in wp_options
        $api_key    = get_option( 'elastic_email_api_key', '' );
        
        // Ensure the sender is a verified domain in Elastic Email!
        $from_email = get_option( 'elastic_email_from_address', get_bloginfo( 'admin_email' ) );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty. The option name in wp_options might be different.' );
            return;
        }

        $payload = array(
            'Recipients' => array(
                'To' => array( $recipient_email )
            ),
            'Content' => array(
                'Body' => array(
                    array(
                        'ContentType' => 'HTML',
                        'Charset'     => 'utf-8',
                        'Content'     => $email_body
                    )
                ),
                'Subject' => $email_subject,
                'From'    => $from_email, // If this is a .local domain, the API will reject it.
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

        error_log( 'SCC Action Success: Email processed by Elastic Email API.' );
    }
}

new AutomatorWP_ElasticMailSender_Action_Send_Email();