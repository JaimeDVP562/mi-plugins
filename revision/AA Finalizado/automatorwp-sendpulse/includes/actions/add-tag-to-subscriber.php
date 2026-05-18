<?php
/**
 * Add Tag to Subscriber in SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Add_Tag_To_Subscriber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Add_Tag_To_Subscriber extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_add_tag_to_subscriber';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add tag to subscriber in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Add <strong>tag</strong> to <strong>subscriber</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Add %s to subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
            'log_label'     => sprintf( __( 'Add %s to subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
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
                            'desc'    => __( 'SendPulse tag to add to the subscriber.', 'automatorwp-sendpulse' ),
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

        if ( empty( $email ) || empty( $tag ) || empty( $addressbook_id ) ) {
            $this->result = __( 'Error: Missing email, tag, or addressbook.', 'automatorwp-sendpulse' );
            return;
        }

        $payload = array(
            'emails' => array(
                array(
                    'email'     => $email,
                    'variables' => array( 'tag' => $tag )
                )
            )
        );
        
        $response = automatorwp_sendpulse_request( 'POST', "/addressbooks/{$addressbook_id}/emails", array( 'body' => $payload ) );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Tag added successfully to subscriber in SendPulse.', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Add_Tag_To_Subscriber();