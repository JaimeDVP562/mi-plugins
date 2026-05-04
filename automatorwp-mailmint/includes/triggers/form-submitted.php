<?php
/**
 * Form Submitted
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\Form_Submitted
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Form_Submitted extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_form_submitted';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A Mail Mint form is submitted', 'automatorwp-mailmint' ),
            'select_option' => __( 'A Mail Mint <strong>form</strong> is submitted', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A Mail Mint form is submitted %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A Mail Mint form is submitted', 'automatorwp-mailmint' ),
            'action'        => 'mailmint_after_form_submit',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'mailmint_contact_email' => array(
                        'label'   => __( 'Contact Email', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The email of the contact who submitted the form', 'automatorwp-mailmint' ),
                    ),
                    'mailmint_form_id' => array(
                        'label'   => __( 'Form ID', 'automatorwp-mailmint' ),
                        'type'    => 'integer',
                        'preview' => __( 'The ID of the submitted form', 'automatorwp-mailmint' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

    /**
     * Trigger listener — fires on mailmint_after_form_submit
     *
     * Hook passes: $form_id (int), $contact_id (int), $contact (array|object)
     *
     * @since 1.0.0
     *
     * @param int              $form_id
     * @param int              $contact_id
     * @param array|object     $contact
     */
    public function listener( $form_id, $contact_id, $contact )
    {
        $user_id = automatorwp_mailmint_get_user_id_from_contact( (int) $contact_id );

        if ( ! $user_id ) {
            return;
        }

        $contact_row = automatorwp_mailmint_get_contact( (int) $contact_id );

        automatorwp_trigger_event( array(
            'trigger'                => $this->trigger,
            'user_id'                => $user_id,
            'mailmint_contact_email' => $contact_row ? $contact_row->email : '',
            'mailmint_form_id'       => (int) $form_id,
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
        $log_meta['mailmint_form_id']       = isset( $event['mailmint_form_id'] )       ? $event['mailmint_form_id']       : '';

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
        $log_fields['mailmint_form_id']       = array( 'name' => __( 'Form ID:',       'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_Form_Submitted();
