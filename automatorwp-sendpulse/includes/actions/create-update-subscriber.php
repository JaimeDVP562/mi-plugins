<?php
/**
 * Create or Update Subscriber in SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Create_Update_Subscriber
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Ensure WordPress functions are available
if ( ! function_exists( '__' ) ) require_once ABSPATH . 'wp-includes/l10n.php';
if ( ! function_exists( 'sanitize_email' ) ) require_once ABSPATH . 'wp-includes/formatting.php';
if ( ! function_exists( 'sanitize_text_field' ) ) require_once ABSPATH . 'wp-includes/formatting.php';
if ( ! function_exists( 'get_user_by' ) ) require_once ABSPATH . 'wp-includes/pluggable.php';
if ( ! function_exists( 'get_user_meta' ) ) require_once ABSPATH . 'wp-includes/user.php';
if ( ! function_exists( 'is_wp_error' ) ) require_once ABSPATH . 'wp-includes/load.php';
if ( ! function_exists( 'add_filter' ) ) require_once ABSPATH . 'wp-includes/plugin.php';

class AutomatorWP_Sendpulse_Create_Update_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_create_update_subscriber';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create / update subscriber in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Create / update <strong>subscriber</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Create/update %s', 'automatorwp-sendpulse' ), '{email}' ),
            'log_label'     => sprintf( __( 'Create/update %s', 'automatorwp-sendpulse' ), '{email}' ),
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

        // Fallbacks: if any field is empty, try to get from WP user
        $user = null;
        if ( empty( $email ) || empty( $first_name ) || empty( $last_name ) ) {
            $user = get_user_by( 'ID', $user_id );
        }
        if ( empty( $email ) && $user ) {
            $email = $user->user_email;
        }
        if ( empty( $first_name ) && $user ) {
            $first_name = get_user_meta( $user->ID, 'first_name', true );
        }
        if ( empty( $last_name ) && $user ) {
            $last_name = get_user_meta( $user->ID, 'last_name', true );
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

        $this->result = __( 'Subscriber created or updated in SendPulse.', 'automatorwp-sendpulse' );
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

new AutomatorWP_Sendpulse_Create_Update_Subscriber();

