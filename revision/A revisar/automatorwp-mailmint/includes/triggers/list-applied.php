<?php
/**
 * Contact Added to List
 *
 * @package     AutomatorWP\Integrations\MailMint\Triggers\List_Applied
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_List_Applied extends AutomatorWP_Integration_Trigger
{
    public $integration = 'mailmint';
    public $trigger     = 'mailmint_list_applied';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A contact is added to a Mail Mint list', 'automatorwp-mailmint' ),
            'select_option' => __( 'A contact is added to a Mail Mint <strong>list</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A contact is added to a Mail Mint list %1$s time(s)', 'automatorwp-mailmint' ), '{times}' ),
            'log_label'     => __( 'A contact is added to a Mail Mint list', 'automatorwp-mailmint' ),
            'action'        => 'mailmint_list_applied',
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
                    'mailmint_list_title' => array(
                        'label'   => __( 'List', 'automatorwp-mailmint' ),
                        'type'    => 'text',
                        'preview' => __( 'The list the contact was added to', 'automatorwp-mailmint' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );
    }

    /**
     * Trigger listener — fires on mailmint_list_applied
     *
     * Hook passes: $lists (array of list arrays or IDs), $contact_id (int or array of ints)
     *
     * @since 1.0.0
     *
     * @param array    $lists
     * @param int|array $contact_id
     */
    public function listener( $lists, $contact_id )
    {
        $contact_ids = is_array( $contact_id ) ? $contact_id : array( $contact_id );

        foreach ( $contact_ids as $cid ) {
            $user_id = automatorwp_mailmint_get_user_id_from_contact( (int) $cid );

            if ( ! $user_id ) {
                continue;
            }

            $contact = automatorwp_mailmint_get_contact( (int) $cid );
            $email   = $contact ? $contact->email : '';

            foreach ( $lists as $list ) {
                $list_title = automatorwp_mailmint_resolve_group_title( $list );

                automatorwp_trigger_event( array(
                    'trigger'                => $this->trigger,
                    'user_id'                => $user_id,
                    'mailmint_contact_email' => $email,
                    'mailmint_list_title'    => $list_title,
                ) );
            }
        }
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
        $log_meta['mailmint_list_title']    = isset( $event['mailmint_list_title'] )    ? $event['mailmint_list_title']    : '';

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
        $log_fields['mailmint_list_title']    = array( 'name' => __( 'List:',          'automatorwp-mailmint' ), 'type' => 'text' );

        return $log_fields;
    }
}

new AutomatorWP_MailMint_List_Applied();
