<?php
/**
 * Send Double Opt-in Email
 *
 * @package     AutomatorWP\Integrations\MailMint\Actions\Send_Double_Optin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Send_Double_Optin extends AutomatorWP_Integration_Action
{
    public $integration = 'mailmint';
    public $action      = 'mailmint_send_double_optin';
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
            'label'         => __( 'Send a double opt-in email to a Mail Mint contact', 'automatorwp-mailmint' ),
            'select_option' => __( 'Send a <strong>double opt-in</strong> email to a Mail Mint contact', 'automatorwp-mailmint' ),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf( __( 'Send double opt-in email to %1$s', 'automatorwp-mailmint' ), '{email}' ),
            /* translators: %1$s: Email. */
            'log_label'     => sprintf( __( 'Send double opt-in email to %1$s', 'automatorwp-mailmint' ), '{email}' ),
            'options'       => array(
                'email' => array(
                    'from'    => 'email',
                    'default' => __( 'contact', 'automatorwp-mailmint' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-mailmint' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'    => 'text',
                            'default' => '',
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
        $email = isset( $action_options['email'] ) ? sanitize_email( $action_options['email'] ) : '';

        if ( empty( $email ) ) {
            $user  = get_user_by( 'ID', $user_id );
            $email = $user ? $user->user_email : '';
        }

        if ( empty( $email ) ) {
            $this->result = __( 'No email provided.', 'automatorwp-mailmint' );
            return;
        }

        $contact = automatorwp_mailmint_get_contact_by_email( $email );

        if ( ! $contact ) {
            $this->result = sprintf( __( 'No Mail Mint contact found for %s.', 'automatorwp-mailmint' ), $email );
            return;
        }

        automatorwp_mailmint_update_contact_status( (int) $contact->id, 'pending' );

        do_action( 'mint_send_double_optin_email', (int) $contact->id, $email );

        $this->result = sprintf( __( 'Double opt-in email triggered for %s.', 'automatorwp-mailmint' ), $email );
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

new AutomatorWP_MailMint_Send_Double_Optin();
