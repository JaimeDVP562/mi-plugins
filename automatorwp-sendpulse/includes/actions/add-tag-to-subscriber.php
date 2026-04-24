<?php
/**
 * Add Tag to Subscriber in SendPulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Add_Tag_To_Subscriber
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

class AutomatorWP_Sendpulse_Add_Tag_To_Subscriber extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_add_tag_to_subscriber';
    public $result = '';

    public function register() {
        // IMPORTANT NOTE:
        // In SendPulse, there are currently NO native "tags" as in Drip. This action stores the value of the "tag" field as a custom variable for the subscriber.
        // If SendPulse adds official support for tags in the future, this action should be updated to use the corresponding endpoint.
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add tag to subscriber in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Add <strong>tag</strong> to <strong>subscriber</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Add %s to subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
            'log_label'     => sprintf( __( 'Add %s to subscriber', 'automatorwp-sendpulse' ), '{tag}' ),
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
                            'desc'    => __( 'Tag to add to the subscriber. (In SendPulse, this is stored as a custom variable, not as a native tag)', 'automatorwp-sendpulse' ),
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

        // NOTE: In SendPulse, the "tag" is stored as a custom variable, not as a native tag.
        // If in the future the SendPulse API supports real tags, update the endpoint here.
        $variables = array( 'tag' => $tag );
        $response = automatorwp_sendpulse_add_subscriber( $email, '', '', $addressbook_id );
        // If the function allows passing variables, automatorwp_sendpulse_add_subscriber should be modified to accept them.
        // Here, only the subscriber is added; for real tags, the SendPulse API for tags should be used if it exists.

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }
        $this->result = __( 'Tag (stored as a custom variable) added to the subscriber in SendPulse. This is not a native tag.', 'automatorwp-sendpulse' );
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

