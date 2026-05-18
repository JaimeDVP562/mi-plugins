<?php
/**
 * Add subscriber to SendPulse addressbook
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Add-Subscriber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Add_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_add_subscriber';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add subscriber to SendPulse addressbook', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Add <strong>subscriber</strong> to SendPulse addressbook', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Add %s to SendPulse', 'automatorwp-sendpulse' ), '{email}' ),
            'log_label'     => sprintf( __( 'Add %s to SendPulse', 'automatorwp-sendpulse' ), '{email}' ),
            'options'       => array(
                'email' => array( 
                    'from'   => 'email', 
                    'fields' => array(
                        'email' => array(
                            'name'     => __( 'Subscriber Email:', 'automatorwp-sendpulse' ),
                            'type'     => 'text', // <-- EL FIX: 'text' en lugar de 'email' para que AutomatorWP lo dibuje
                            'default'  => '',
                            'required' => true,
                        ),
                        'first_name' => array(
                            'name'     => __( 'First name:', 'automatorwp-sendpulse' ),
                            'type'     => 'text',
                            'default'  => '',
                        ),
                        'last_name' => array(
                            'name'     => __( 'Last name:', 'automatorwp-sendpulse' ),
                            'type'     => 'text',
                            'default'  => '',
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
        $first_name = isset( $action_options['first_name'] ) ? sanitize_text_field( $action_options['first_name'] ) : '';
        $last_name = isset( $action_options['last_name'] ) ? sanitize_text_field( $action_options['last_name'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) ? sanitize_text_field( $action_options['addressbook_id'] ) : '';

        if ( empty( $email ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) $email = $user->user_email;
        }

        if ( empty( $email ) ) {
            $this->result = __( 'Error: No email provided.', 'automatorwp-sendpulse' );
            return;
        }

        $response = automatorwp_sendpulse_add_subscriber( $email, $first_name, $last_name, $addressbook_id );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Subscriber added (or updated) in SendPulse.', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Add_Subscriber();