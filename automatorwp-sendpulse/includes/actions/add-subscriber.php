<?php
/**
 * Add subscriber to SendPulse addressbook
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Add-Subscriber
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Add_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_add_subscriber';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add subscriber to SendPulse addressbook', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Add <strong>subscriber</strong> to SendPulse addressbook', 'automatorwp-sendpulse' ),
            'edit_label'    => __( 'Add {email} to SendPulse', 'automatorwp-sendpulse' ),
            'log_label'     => __( 'Added {email} to SendPulse', 'automatorwp-sendpulse' ),
            'options'       => array(
                'subscriber' => array(
                    'from' => 'manual',
                    'fields' => array(
                        'email' => array(
                            'name'     => __( 'Subscriber Email:', 'automatorwp-sendpulse' ),
                            'type'     => 'email',
                            'default'  => '',
                            'required' => true,
                        ),
                        'first_name' => array(
                            'name'     => __( 'First name:', 'automatorwp-sendpulse' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => false,
                        ),
                        'last_name' => array(
                            'name'     => __( 'Last name:', 'automatorwp-sendpulse' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => false,
                        ),
                        'addressbook_id' => automatorwp_utilities_ajax_selector_field( array(
                            'name'       => __( 'Addressbook:', 'automatorwp-sendpulse' ),
                            'desc'       => __( 'Select the addressbook in your SendPulse account.', 'automatorwp-sendpulse' ),
                            'type'       => 'select',
                            'field'      => 'addressbook_id',
                            'action_cb'  => 'automatorwp_sendpulse_list_addressbooks',
                            'options_cb' => 'automatorwp_sendpulse_options_cb_addressbook',
                            'attributes' => array(
                                'placeholder' => __( 'Select addressbook', 'automatorwp-sendpulse' ),
                            ),
                            'default'    => '',
                        ) ),
                    ),
                ),
            ),
        ) );

    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        $email = isset( $action_options['email'] ) ? sanitize_email( $action_options['email'] ) : '';
        $first_name = isset( $action_options['first_name'] ) ? sanitize_text_field( $action_options['first_name'] ) : '';
        $last_name = isset( $action_options['last_name'] ) ? sanitize_text_field( $action_options['last_name'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) && $action_options['addressbook_id'] !== '' ? sanitize_text_field( $action_options['addressbook_id'] ) : null;

        if ( empty( $email ) ) {
            $this->result = __( 'Error: No email provided.', 'automatorwp-sendpulse' );
            return;
        }

        // Temporary debug logging: record the parameters and the API response when WP_DEBUG is enabled.
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            try {
                error_log( '[automatorwp-sendpulse] add_subscriber called with: email=' . $email . ' first_name=' . $first_name . ' last_name=' . $last_name . ' addressbook_id=' . var_export( $addressbook_id, true ) );
            } catch ( Exception $e ) {
                // ignore logging failures
            }
        }

        $response = automatorwp_sendpulse_add_subscriber( $email, $first_name, $last_name, $addressbook_id );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            try {
                if ( is_wp_error( $response ) ) {
                    error_log( '[automatorwp-sendpulse] automatorwp_sendpulse_add_subscriber WP_Error: ' . $response->get_error_message() . ' data: ' . print_r( $response->get_error_data(), true ) );
                } else {
                    error_log( '[automatorwp-sendpulse] automatorwp_sendpulse_add_subscriber response: ' . print_r( $response, true ) );
                }
            } catch ( Exception $e ) {
                // ignore logging failures
            }
        }

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'Subscriber added (or updated) in SendPulse.', 'automatorwp-sendpulse' );

    }

    public function hooks() {
        parent::hooks();
    }

}

new AutomatorWP_Sendpulse_Add_Subscriber();
