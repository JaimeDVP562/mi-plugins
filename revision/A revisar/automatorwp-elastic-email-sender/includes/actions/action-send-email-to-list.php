<?php
/**
 * Send Bulk Email to List Action
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Actions\Send_Email_To_List
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Action_Send_Email_To_List extends AutomatorWP_Integration_Action {

    public $integration = 'elasticmailsender';
    public $action      = 'elasticmailsender_send_email_to_list';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Send a template email to an entire List', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Send an <strong>email to a list</strong>', 'automatorwp-elasticmailsender' ),
            'edit_label'        => sprintf( __( 'Send template to list %1$s', 'automatorwp-elasticmailsender' ), '{list_name}' ),
            'log_label'         => sprintf( __( 'Send template to list %1$s', 'automatorwp-elasticmailsender' ), '{list_name}' ),
            'options'           => array(
                'list_name' => array(
                    'from'   => 'list_name', 
                    'fields' => array(
                        'list_name' => array(
                            'name'        => __( 'List Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Select the distribution list to send the campaign to.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'select',
                            'options'     => automatorwp_elasticmailsender_get_api_lists(),
                            'required'    => true,
                        ),
                        'template_name' => array(
                            'name'        => __( 'Template Name', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Select the template to send.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'select',
                            'options'     => automatorwp_elasticmailsender_get_api_templates(),
                            'required'    => true,
                        ),
                        'email_subject' => array(
                            'name'        => __( 'Subject', 'automatorwp-elasticmailsender' ),
                            'description' => __( 'Enter the subject of the campaign.', 'automatorwp-elasticmailsender' ),
                            'type'        => 'text',
                            'required'    => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $list_name     = isset( $action_options['list_name'] ) ? sanitize_text_field( $action_options['list_name'] ) : '';
        $template_name = isset( $action_options['template_name'] ) ? sanitize_text_field( $action_options['template_name'] ) : '';
        $email_subject = isset( $action_options['email_subject'] ) ? sanitize_text_field( $action_options['email_subject'] ) : '';

        if ( empty( $list_name ) || empty( $template_name ) || empty( $email_subject ) ) {
            error_log( 'SCC Action Error: Missing list, template, or subject.' );
            return;
        }

        $api_key    = get_option( 'elastic_email_api_key', '' );
        $from_email = get_option( 'elastic_email_from_address', get_bloginfo( 'admin_email' ) );
        
        if ( empty( $api_key ) ) {
            error_log( 'SCC Action Error: API Key is empty.' );
            return;
        }

        $payload = array(
            'Name'       => 'AWP Campaign - ' . time(),
            'Recipients' => array(
                'ListNames' => array( $list_name )
            ),
            'Content'    => array(
                array(
                    'TemplateName' => $template_name,
                    'Subject'      => $email_subject,
                    'From'         => $from_email,
                )
            )
        );

        $response = wp_remote_post( 'https://api.elasticemail.com/v4/campaigns', array(
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
        
        if ( $status_code !== 200 && $status_code !== 201 ) {
            error_log( 'SCC Action API Rejected (' . $status_code . '): ' . $body );
            return;
        }

        error_log( 'SCC Action Success: Campaign launched to list by Elastic Email API.' );
    }
} 

new AutomatorWP_ElasticMailSender_Action_Send_Email_To_List();