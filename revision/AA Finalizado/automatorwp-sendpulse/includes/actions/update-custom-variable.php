<?php
/**
 * Update Custom Variable of Subscriber in SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Update_Custom_Variable
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Update_Custom_Variable extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_update_custom_variable';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Update custom variable of subscriber in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Update <strong>custom variable</strong> of <strong>subscriber</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Update %1$s to %2$s for subscriber', 'automatorwp-sendpulse' ), '{variable}', '{value}' ),
            'log_label'     => sprintf( __( 'Update %1$s to %2$s for subscriber', 'automatorwp-sendpulse' ), '{variable}', '{value}' ),
            'options'       => array(
                'variable' => array(
                    'from'    => 'variable',
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-sendpulse' ),
                            'type'    => 'text', 
                            'default' => '',
                        ),
                        'variable' => array(
                            'name'    => __( 'Variable Name:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The exact name of the variable in SendPulse (e.g. phone, city).', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'value' => array(
                            'name'    => __( 'Variable Value:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The new value you want to set.', 'automatorwp-sendpulse' ),
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
        $variable_name = isset( $action_options['variable'] ) ? sanitize_text_field( $action_options['variable'] ) : '';
        $variable_value = isset( $action_options['value'] ) ? sanitize_text_field( $action_options['value'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) ? sanitize_text_field( $action_options['addressbook_id'] ) : '';

        if ( empty( $email ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) $email = $user->user_email;
        }

        if ( empty( $email ) || empty( $variable_name ) || empty( $addressbook_id ) ) {
            $this->result = __( 'Error: Missing email, variable name, or addressbook.', 'automatorwp-sendpulse' );
            return;
        }

        $payload = array(
            'emails' => array(
                array(
                    'email'     => $email,
                    'variables' => array( $variable_name => $variable_value )
                )
            )
        );
        
        $response = automatorwp_sendpulse_request( 'POST', "/addressbooks/{$addressbook_id}/emails", array( 'body' => $payload ) );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Custom variable updated successfully in SendPulse.', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Update_Custom_Variable();