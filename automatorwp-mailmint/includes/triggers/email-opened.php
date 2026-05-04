<?php
/**
 * Email Opened
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\Email_Opened
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Email_Opened extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_email_opened';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A Mail Mint contact opens an email', 'automatorwp-mailmint' ),
            'select_option' => __( 'A Mail Mint contact <strong>opens an email</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A Mail Mint contact opens an email %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A Mail Mint contact opens an email', 'automatorwp-mailmint' ),
            'action'        => 'mailmint_after_email_open',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'mailmint_contact_email' => array(
                        'label'   => __( 'Contact Email', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The email of the contact', 'automatorwp-mailmint' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

    /**
     * Trigger listener — fires on mailmint_after_email_open
     *
     * Hook passes: $email_id (int) — the broadcast email log entry ID
     *
     * @since 1.0.0
     *
     * @param int $email_id
     */
    public function listener( $email_id )
    {
        $contact = automatorwp_mailmint_get_contact_from_email_log( (int) $email_id );

        if ( ! $contact ) {
            return;
        }

        $user_id = automatorwp_mailmint_get_user_id_from_contact( (int) $contact->id );

        if ( ! $user_id ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'                => $this->trigger,
            'user_id'                => $user_id,
            'mailmint_contact_email' => $contact->email,
        ) );
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields',                      array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation )
    {
        if ( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['mailmint_contact_email'] = isset( $event['mailmint_contact_email'] ) ? $event['mailmint_contact_email'] : '';

        return $log_meta;
    }

    /**
     * Trigger custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['mailmint_contact_email'] = array( 'name' => __( 'Contact Email:', 'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_Email_Opened();
