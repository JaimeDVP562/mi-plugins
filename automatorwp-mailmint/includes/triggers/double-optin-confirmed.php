<?php
/**
 * Double Opt-in Confirmed
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\Double_Optin_Confirmed
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Double_Optin_Confirmed extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_double_optin_confirmed';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A Mail Mint contact confirms double opt-in', 'automatorwp-mailmint' ),
            'select_option' => __( 'A Mail Mint contact confirms <strong>double opt-in</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A Mail Mint contact confirms double opt-in %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A Mail Mint contact confirms double opt-in', 'automatorwp-mailmint' ),
            'action'        => 'mailmint_after_confirm_double_optin',
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
     * Trigger listener — fires on mailmint_after_confirm_double_optin
     *
     * Hook passes: $contact (array|object|int)
     *
     * @since 1.0.0
     *
     * @param array|object|int $contact
     */
    public function listener( $contact )
    {
        if ( is_wp_error( $contact ) ) {
            return;
        }

        if ( is_array( $contact ) ) {
            $contact_id = isset( $contact['id'] ) ? (int) $contact['id'] : 0;
        } elseif ( is_object( $contact ) ) {
            $contact_id = isset( $contact->id ) ? (int) $contact->id : 0;
        } else {
            $contact_id = (int) $contact;
        }

        if ( ! $contact_id ) {
            return;
        }

        $user_id = automatorwp_mailmint_get_user_id_from_contact( $contact_id );

        if ( ! $user_id ) {
            return;
        }

        $contact_row = automatorwp_mailmint_get_contact( $contact_id );

        automatorwp_trigger_event( array(
            'trigger'                => $this->trigger,
            'user_id'                => $user_id,
            'mailmint_contact_email' => $contact_row ? $contact_row->email : '',
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

new AutomatorWP_MailMint_Double_Optin_Confirmed();
