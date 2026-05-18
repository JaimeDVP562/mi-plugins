<?php
/**
 * Send SMS via SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Send_SMS
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Send_SMS extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_send_sms';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Send an SMS to a user via SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Send an <strong>SMS</strong> to a user via SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => __( 'Send an SMS to {phone}', 'automatorwp-sendpulse' ),
            'log_label'     => __( 'Send an SMS to {phone}', 'automatorwp-sendpulse' ),
            'options'       => array(
                'phone' => array( 
                    'from'    => 'phone',
                    'fields'  => array(
                        'sender' => array(
                            'name'    => __( 'SMS Sender Name (Sender ID):', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The brand name that appears on the mobile screen (e.g., MyStore). It MUST be pre-approved in your SendPulse account.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'phone' => array(
                            'name'    => __( 'User Phone Number:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Click the icon on the right to insert a dynamic tag (like WooCommerce Billing Phone) or enter a number in international format.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'message' => array(
                            'name'    => __( 'Message:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The text message you want to send. You can also use tags here.', 'automatorwp-sendpulse' ),
                            'type'    => 'text', 
                            'default' => '',
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $sender  = isset( $action_options['sender'] ) ? sanitize_text_field( $action_options['sender'] ) : '';
        $phone   = isset( $action_options['phone'] ) ? sanitize_text_field( $action_options['phone'] ) : '';
        $message = isset( $action_options['message'] ) ? sanitize_text_field( $action_options['message'] ) : '';

        if ( empty( $sender ) || empty( $phone ) || empty( $message ) ) {
            $this->result = __( 'Error: Missing Sender ID, Phone number, or Message.', 'automatorwp-sendpulse' );
            return;
        }

        $phone = preg_replace( '/[^0-9]/', '', $phone );

        $endpoint = '/sms/send';
        $payload = array(
            'sender' => $sender,
            'phones' => array( $phone ),
            'body'   => $message
        );
        
        $response = automatorwp_sendpulse_request( 'POST', $endpoint, array( 'body' => $payload ) );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        if ( isset( $response['is_error'] ) && $response['is_error'] ) {
            $error_msg = isset( $response['message'] ) ? $response['message'] : 'Unknown error';
            $this->result = sprintf( __( 'SendPulse SMS Error: %s', 'automatorwp-sendpulse' ), $error_msg );
            return;
        }

        $this->result = __( 'SMS sent successfully via SendPulse.', 'automatorwp-sendpulse' );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = (string) $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-sendpulse' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Sendpulse_Send_SMS();