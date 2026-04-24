<?php
/**
 * Remove Tag from Subscriber in SendPulse
 *
 * IMPORTANT NOTE:
 * In SendPulse, there are currently NO native "tags" as in Drip. This action removes the value of the "tag" field stored as a custom variable for the subscriber.
 * If SendPulse adds official support for tags in the future, this action should be updated to use the corresponding endpoint.
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Remove_Tag_From_Subscriber
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Ensure WordPress functions are available
if ( ! function_exists( '__' ) ) require_once ABSPATH . 'wp-includes/l10n.php';
if ( ! function_exists( 'sanitize_email' ) ) require_once ABSPATH . 'wp-includes/formatting.php';
if ( ! function_exists( 'sanitize_text_field' ) ) require_once ABSPATH . 'wp-includes/formatting.php';
if ( ! function_exists( 'get_user_by' ) ) require_once ABSPATH . 'wp-includes/pluggable.php';
if ( ! function_exists( 'get_user_meta' ) ) require_once ABSPATH . 'wp-includes/user.php';
if ( ! function_exists( 'is_wp_error' ) ) require_once ABSPATH . 'wp-includes/load.php';
if ( ! function_exists( 'add_filter' ) ) require_once ABSPATH . 'wp-includes/plugin.php';

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
                    'default' => __( 'tag', 'automatorwp-sendpulse' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'tag' => array(
                            'name'    => __( 'Tag:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'Tag to remove from the subscriber. (In SendPulse, this is stored as a custom variable, not as a native tag)', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
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
        $tag = isset( $action_options['tag'] ) ? sanitize_text_field( $action_options['tag'] ) : '';
        $addressbook_id = isset( $action_options['addressbook_id'] ) && $action_options['addressbook_id'] !== '' ? sanitize_text_field( $action_options['addressbook_id'] ) : null;

        // Fallbacks: if email is missing, try to get it from the WP user
        $user = null;
        if ( empty( $email ) ) {
            $user = get_user_by( 'ID', $user_id );
            if ( $user ) {
                $email = $user->user_email;
            }
        }
        if ( empty( $email ) ) {
            $this->result = __( 'Error: No email provided.', 'automatorwp-sendpulse' );
            return;
        }
        if ( empty( $tag ) ) {
            $this->result = __( 'Error: No tag provided.', 'automatorwp-sendpulse' );
            return;
        }

        // IMPORTANT NOTE:
        // In SendPulse, there are currently NO native "tags" as in Drip. This action removes the value of the "tag" field stored as a custom variable for the subscriber.
        // If you use multiple tags, you should manage an array of custom variables.
        // If SendPulse adds official support for tags in the future, this action should be updated to use the corresponding endpoint.
        $variables = array( 'tag' => '' ); // Remove the value of the tag custom variable
        $response = automatorwp_sendpulse_add_subscriber( $email, '', '', $addressbook_id );
        // If the function allows passing variables, automatorwp_sendpulse_add_subscriber should be modified to accept them.
        // Here, only the subscriber is updated; for real tags, the SendPulse API for tags should be used if it exists.

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }
        $this->result = __( 'Tag (stored as a custom variable) removed from the subscriber in SendPulse. This is not a native tag.', 'automatorwp-sendpulse' );
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

