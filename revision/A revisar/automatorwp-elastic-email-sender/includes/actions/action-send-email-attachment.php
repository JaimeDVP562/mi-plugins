<?php
/**
 * Send Email with Attachment Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Send_Email_Attachment
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Send_Email_Attachment extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_send_email_attachment';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Send an email with an attachment', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Send an <strong>email with attachment</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Send email with attachment to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
            'log_label'         => sprintf( __( 'Send email with attachment to %1$s', 'automatorwp-elasticmailsender' ), '{recipient_email}' ),
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
                        'attachment_url' => array(
                            'name'        => __( 'Attachment URL', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the full URL of the file (e.g. https://yoursite.com/file.pdf).', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $recipient_email = isset( $action_options['recipient_email'] ) ? sanitize_email( $action_options['recipient_email'] ) : '';
        $email_subject   = isset( $action_options['email_subject'] ) ? sanitize_text_field( $action_options['email_subject'] ) : '';
        $email_body      = isset( $action_options['email_body'] ) ? wp_kses_post( $action_options['email_body'] ) : '';
        $attachment_url  = isset( $action_options['attachment_url'] ) ? esc_url_raw( $action_options['attachment_url'] ) : '';

        if ( empty( $recipient_email ) || empty( $email_body ) || empty( $attachment_url ) ) {
            error_log( 'SCC Action Error: Missing recipient, body, or attachment URL.' );
            return;
        }

        $api_key    = get_option( 'elastic_email_api_key', '' );
        $from_email = get_option( 'elastic_email_from_address', get_bloginfo( 'admin_email' ) );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $attachments_array = array();
        $file_response     = wp_remote_get( $attachment_url, array( 'timeout' => 15 ) );

        if ( ! is_wp_error( $file_response ) && wp_remote_retrieve_response_code( $file_response ) === 200 ) {
            
            $file_content = wp_remote_retrieve_body( $file_response );
            $file_name    = basename( parse_url( $attachment_url, PHP_URL_PATH ) );
            $file_type    = wp_check_filetype( $file_name );
            $mime_type    = ! empty( $file_type['type'] ) ? $file_type['type'] : 'application/octet-stream';

            $attachments_array[] = array(
                'BinaryContent' => base64_encode( $file_content ),
                'Name'          => $file_name,
                'ContentType'   => $mime_type
            );
        } else {
            error_log( 'SCC Action Warning: Could not download the attachment from the provided URL.' );
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
                'From'    => $from_email
            )
        );

        if ( ! empty( $attachments_array ) ) {
            $payload['Content']['Attachments'] = $attachments_array;
        }

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

        error_log( 'SCC Action Success: Email with attachment processed by Elastic Email API.' );
    }
}

new AutomatorWP_ElasticMailSender_Action_Send_Email_Attachment();