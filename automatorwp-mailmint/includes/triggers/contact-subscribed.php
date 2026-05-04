<?php
/**
 * Contact Subscribed
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\Contact_Subscribed
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Contact_Subscribed extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_contact_subscribed';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A Mail Mint contact is subscribed', 'automatorwp-mailmint' ),
            'select_option' => __( 'A Mail Mint contact is <strong>subscribed</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A Mail Mint contact is subscribed %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A Mail Mint contact is subscribed', 'automatorwp-mailmint' ),
            'action'        => 'mint_subscriber_status_to_subscribed',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2,
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
                    'mailmint_old_status' => array(
                        'label'   => __( 'Previous Status', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The previous subscription status', 'automatorwp-mailmint' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

    /**
     * Trigger listener — fires on mint_subscriber_status_to_subscribed
     *
     * @since 1.0.0
     *
     * @param int    $contact_id
     * @param string $old_status
     */
    public function listener( $contact_id, $old_status )
    {
        $user_id = automatorwp_mailmint_get_user_id_from_contact( (int) $contact_id );

        if ( ! $user_id ) {
            return;
        }

        $contact = automatorwp_mailmint_get_contact( (int) $contact_id );

        automatorwp_trigger_event( array(
            'trigger'                => $this->trigger,
            'user_id'                => $user_id,
            'mailmint_contact_email' => $contact ? $contact->email : '',
            'mailmint_old_status'    => sanitize_text_field( (string) $old_status ),
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
        $log_meta['mailmint_old_status']    = isset( $event['mailmint_old_status'] )    ? $event['mailmint_old_status']    : '';

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

        $log_fields['mailmint_contact_email'] = array( 'name' => __( 'Contact Email:',    'automatorwp-mailmint' ), 'type' => 'text' );
        $log_fields['mailmint_old_status']    = array( 'name' => __( 'Previous Status:',  'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_Contact_Subscribed();
