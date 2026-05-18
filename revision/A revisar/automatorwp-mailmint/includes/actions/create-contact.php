<?php
/**
 * Create or Update Contact
 *
 * @package     AutomatorWP\Integrations\MailMint\Actions\Create_Contact
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Create_Contact extends AutomatorWP_Integration_Action
{
    public $integration = 'mailmint';
    public $action      = 'mailmint_create_contact';
    public $result      = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create or update a Mail Mint contact', 'automatorwp-mailmint' ),
            'select_option' => __( 'Create or update a Mail Mint <strong>contact</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf( __( 'Create or update Mail Mint contact %1$s', 'automatorwp-mailmint' ), '{email}' ),
            /* translators: %1$s: Email. */
            'log_label'     => sprintf( __( 'Create or update Mail Mint contact %1$s', 'automatorwp-mailmint' ), '{email}' ),
            'options'       => array(
                'email' => array(
                    'from'    => 'email',
                    'default' => __( 'contact', 'automatorwp-mailmint' ),
                    'fields'  => array(
                        'email' => array(
                            'name'       => __( 'Email:', 'automatorwp-mailmint' ),
                            'desc'       => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'sample@email.com or use the tag selector', 'automatorwp-mailmint' ),
                            ),
                            'default'    => '',
                        ),
                        'first_name' => array(
                            'name'       => __( 'First Name:', 'automatorwp-mailmint' ),
                            'desc'       => __( 'Leave empty to use the first name of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'John or use the tag selector', 'automatorwp-mailmint' ),
                            ),
                            'default'    => '',
                        ),
                        'last_name' => array(
                            'name'       => __( 'Last Name:', 'automatorwp-mailmint' ),
                            'desc'       => __( 'Leave empty to use the last name of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'Doe or use the tag selector', 'automatorwp-mailmint' ),
                            ),
                            'default'    => '',
                        ),
                        'status' => array(
                            'name'    => __( 'Status:', 'automatorwp-mailmint' ),
                            'type'    => 'select',
                            'options' => array(
                                'subscribed'   => __( 'Subscribed',   'automatorwp-mailmint' ),
                                'pending'      => __( 'Pending',      'automatorwp-mailmint' ),
                                'unsubscribed' => __( 'Unsubscribed', 'automatorwp-mailmint' ),
                            ),
                            'default' => 'subscribed',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already parsed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation )
    {
        $email      = isset( $action_options['email'] )      ? sanitize_email( $action_options['email'] )           : '';
        $first_name = isset( $action_options['first_name'] ) ? sanitize_text_field( $action_options['first_name'] ) : '';
        $last_name  = isset( $action_options['last_name'] )  ? sanitize_text_field( $action_options['last_name'] )  : '';
        $status     = isset( $action_options['status'] )     ? sanitize_text_field( $action_options['status'] )     : 'subscribed';

        $user = null;

        if ( empty( $email ) || empty( $first_name ) || empty( $last_name ) ) {
            $user = get_user_by( 'ID', $user_id );
        }

        if ( empty( $email ) && $user ) {
            $email = $user->user_email;
        }

        if ( empty( $email ) ) {
            $this->result = __( 'No email provided.', 'automatorwp-mailmint' );
            return;
        }

        if ( empty( $first_name ) && $user ) {
            $first_name = get_user_meta( $user->ID, 'first_name', true );
        }

        if ( empty( $last_name ) && $user ) {
            $last_name = get_user_meta( $user->ID, 'last_name', true );
        }

        $existing = automatorwp_mailmint_get_contact_by_email( $email );

        if ( $existing ) {
            global $wpdb;

            $wpdb->update(
                $wpdb->prefix . 'mint_contacts',
                array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'status'     => $status,
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => (int) $existing->id ),
                array( '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );

            $this->result = sprintf( __( 'Contact %s updated.', 'automatorwp-mailmint' ), $email );
        } else {
            global $wpdb;

            $inserted = $wpdb->insert(
                $wpdb->prefix . 'mint_contacts',
                array(
                    'email'      => $email,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'status'     => $status,
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%s', '%s', '%s', '%s' )
            );

            $this->result = $inserted
                ? sprintf( __( 'Contact %s created.', 'automatorwp-mailmint' ), $email )
                : sprintf( __( 'Failed to create contact %s.', 'automatorwp-mailmint' ), $email );
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = (string) $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'action' || $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_Create_Contact();
