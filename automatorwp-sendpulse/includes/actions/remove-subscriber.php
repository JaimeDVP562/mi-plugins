<?php
/**
 * Remove subscriber from SendPulse addressbook
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Remove-Subscriber
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Remove_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_remove_subscriber';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Remove subscriber from SendPulse addressbook', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Remove <strong>subscriber</strong> from SendPulse addressbook', 'automatorwp-sendpulse' ),
            'edit_label'    => __( 'Remove {email} from SendPulse', 'automatorwp-sendpulse' ),
            'log_label'     => __( 'Removed {email} from SendPulse', 'automatorwp-sendpulse' ),
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
                        'addressbook_id' => array(
                            'name'     => __( 'Addressbook ID (optional):', 'automatorwp-sendpulse' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => false,
                        ),
                    ),
                ),
            ),
        ) );

    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        $email = isset( $action_options['email'] ) ? sanitize_email( $action_options['email'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) && $action_options['addressbook_id'] !== '' ? sanitize_text_field( $action_options['addressbook_id'] ) : null;

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
        parent::hooks();
    }

}

new AutomatorWP_Sendpulse_Remove_Subscriber();
