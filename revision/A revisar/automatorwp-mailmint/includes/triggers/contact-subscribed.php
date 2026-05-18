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
            'action'        => 'mint_after_contact_creation',
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
     * Shared processing logic called from both hooks.
     * Only proceeds when the contact has status 'subscribed'.
     *
     * @since 1.0.0
     *
     * @param int $contact_id
     */
    private function process( $contact_id )
    {
        $contact = automatorwp_mailmint_get_contact( (int) $contact_id );

        if ( ! $contact || $contact->status !== 'subscribed' ) {
            return;
        }

        $user_id = automatorwp_mailmint_get_user_id_from_contact( (int) $contact_id );

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
     * Listener for mint_after_contact_creation (form submissions, programmatic inserts).
     *
     * @since 1.0.0
     *
     * @param int $contact_id
     */
    public function listener( $contact_id )
    {
        $this->process( $contact_id );
    }

    /**
     * Listener for mailmint_contacts_saved (admin UI contact creation since v1.19.5).
     * Skips update operations — only processes newly created contacts.
     *
     * @since 1.0.0
     *
     * @param int   $contact_id
     * @param array $params  Original request params; contains 'contact_id' on updates.
     */
    public function listener_from_saved( $contact_id, $params )
    {
        if ( isset( $params['contact_id'] ) ) {
            return;
        }

        $this->process( $contact_id );
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        // Admin UI creates contacts via ContactRepository (v1.19.5+), which fires
        // mailmint_contacts_saved instead of mint_after_contact_creation.
        add_action( 'mailmint_contacts_saved', array( $this, 'listener_from_saved' ), 10, 2 );

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

new AutomatorWP_MailMint_Contact_Subscribed();
