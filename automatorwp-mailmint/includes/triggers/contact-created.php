<?php
/**
 * Contact Created
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\Contact_Created
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Contact_Created extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_contact_created';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A new Mail Mint contact is created', 'automatorwp-mailmint' ),
            'select_option' => __( 'A new Mail Mint <strong>contact</strong> is created', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A new Mail Mint contact is created %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A new Mail Mint contact is created', 'automatorwp-mailmint' ),
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
                        'preview' => __( 'The email of the created contact', 'automatorwp-mailmint' ),
                    ),
                    'mailmint_contact_first_name' => array(
                        'label'   => __( 'Contact First Name', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The first name of the created contact', 'automatorwp-mailmint' ),
                    ),
                    'mailmint_contact_last_name' => array(
                        'label'   => __( 'Contact Last Name', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The last name of the created contact', 'automatorwp-mailmint' ),
                    ),
                    'mailmint_contact_id' => array(
                        'label'   => __( 'Contact ID', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The Mail Mint internal contact ID', 'automatorwp-mailmint' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

    /**
     * Trigger listener — fires on mint_after_contact_creation
     *
     * @since 1.0.0
     *
     * @param int $contact_id
     */
    public function listener( $contact_id )
    {
        $contact = automatorwp_mailmint_get_contact( $contact_id );

        if ( ! $contact ) {
            return;
        }

        $user_id = automatorwp_mailmint_get_user_id_from_contact( $contact_id );

        if ( ! $user_id ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'                    => $this->trigger,
            'user_id'                    => $user_id,
            'mailmint_contact_id'        => (int) $contact_id,
            'mailmint_contact_email'     => isset( $contact->email )      ? $contact->email      : '',
            'mailmint_contact_first_name'=> isset( $contact->first_name ) ? $contact->first_name : '',
            'mailmint_contact_last_name' => isset( $contact->last_name )  ? $contact->last_name  : '',
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

        $log_meta['mailmint_contact_id']         = isset( $event['mailmint_contact_id'] )         ? $event['mailmint_contact_id']         : '';
        $log_meta['mailmint_contact_email']       = isset( $event['mailmint_contact_email'] )       ? $event['mailmint_contact_email']       : '';
        $log_meta['mailmint_contact_first_name']  = isset( $event['mailmint_contact_first_name'] )  ? $event['mailmint_contact_first_name']  : '';
        $log_meta['mailmint_contact_last_name']   = isset( $event['mailmint_contact_last_name'] )   ? $event['mailmint_contact_last_name']   : '';

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

        $log_fields['mailmint_contact_id']         = array( 'name' => __( 'Contact ID:',         'automatorwp-mailmint' ), 'type' => 'text' );
        $log_fields['mailmint_contact_email']       = array( 'name' => __( 'Contact Email:',      'automatorwp-mailmint' ), 'type' => 'text' );
        $log_fields['mailmint_contact_first_name']  = array( 'name' => __( 'First Name:',         'automatorwp-mailmint' ), 'type' => 'text' );
        $log_fields['mailmint_contact_last_name']   = array( 'name' => __( 'Last Name:',          'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_Contact_Created();
