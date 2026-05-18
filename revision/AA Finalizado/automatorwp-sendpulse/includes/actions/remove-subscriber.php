<?php
/**
 * Remove subscriber from SendPulse addressbook
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Remove_Subscriber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Remove_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_remove_subscriber';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Remove subscriber from SendPulse addressbook', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Remove <strong>subscriber</strong> from SendPulse addressbook', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Remove %s from SendPulse', 'automatorwp-sendpulse' ), '{email}' ),
            'log_label'     => sprintf( __( 'Removed %s from SendPulse', 'automatorwp-sendpulse' ), '{email}' ),
            'options'       => array(
                'email' => array(
                    'from'   => 'email',
                    'fields' => array(
                        'email' => array(
                            'name'     => __( 'Subscriber Email:', 'automatorwp-sendpulse' ),
                            'type'     => 'text', // <-- FIX: Cambiado a 'text' para que se muestre en AutomatorWP
                            'default'  => '',
                            'required' => true,
                        ),
                        'addressbook_id' => array(
                            'name'       => __( 'Addressbook:', 'automatorwp-sendpulse' ),
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
        $addressbook_id = isset( $action_options['addressbook_id'] ) ? sanitize_text_field( $action_options['addressbook_id'] ) : '';

        if ( empty( $email ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) $email = $user->user_email;
        }

        if ( empty( $email ) ) {
            $this->result = __( 'Error: No email provided.', 'automatorwp-sendpulse' );
            return;
        }

        $response = automatorwp_sendpulse_remove_subscriber( $email, $addressbook_id );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Subscriber removed from SendPulse (or not found).', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Remove_Subscriber();