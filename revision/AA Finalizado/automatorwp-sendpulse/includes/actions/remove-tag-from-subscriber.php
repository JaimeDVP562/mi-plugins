<?php
/**
 * Remove Tag from Subscriber in SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Remove_Tag_From_Subscriber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Remove_Tag_From_Subscriber extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_remove_tag_from_subscriber';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Remove tag from subscriber in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Remove <strong>tag</strong> from <strong>subscriber</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Remove %s from subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
            'log_label'     => sprintf( __( 'Remove %s from subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
            'options'       => array(
                'tag' => array( 
                    'from'    => 'tag',
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-sendpulse' ),
                            'type'    => 'text', 
                            'default' => '',
                        ),
                        'tag' => array(
                            'name'    => __( 'Tag:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Tag to remove from the subscriber.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'addressbook_id' => array(
                            'name'       => __( 'Addressbook:', 'automatorwp-sendpulse' ),
                            'desc'       => __( 'Select the addressbook in your SendPulse account.', 'automatorwp-sendpulse' ),
                            'type'       => 'select',
                            'options_cb' => 'automatorwp_sendpulse_get_addressbooks_options',
                            'default'    => '',
                            'required'   => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $email = isset( $action_options['email'] ) ? sanitize_email( $action_options['email'] ) : '';
        $tag = isset( $action_options['tag'] ) ? sanitize_text_field( $action_options['tag'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) ? sanitize_text_field( $action_options['addressbook_id'] ) : '';

        if ( empty( $email ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) $email = $user->user_email;
        }

        if ( empty( $email ) || empty( $addressbook_id ) ) {
            $this->result = __( 'Error: Missing email or addressbook.', 'automatorwp-sendpulse' );
            return;
        }

        if ( ! empty( $tag ) ) {
            $endpoint = "/addressbooks/{$addressbook_id}/emails/tags";
            $payload_tags = array(
                'emails' => array( $email ),
                'tags'   => array( $tag )
            );
            automatorwp_sendpulse_request( 'DELETE', $endpoint, array( 'body' => $payload_tags ) );
        }

        $variables = array( 'tag' => '-' ); 
        $response = automatorwp_sendpulse_add_subscriber( $email, '', '', $addressbook_id, $variables );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Tag removed successfully (replaced with dash).', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Remove_Tag_From_Subscriber();