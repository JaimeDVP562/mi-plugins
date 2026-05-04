<?php
/**
 * Add Contact to List
 *
 * @package     AutomatorWP\Integrations\MailMint\Actions\Add_To_List
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Add_To_List extends AutomatorWP_Integration_Action
{
    public $integration = 'mailmint';
    public $action      = 'mailmint_add_to_list';
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
            'label'         => __( 'Add a Mail Mint contact to a list', 'automatorwp-mailmint' ),
            'select_option' => __( 'Add a Mail Mint contact to a <strong>list</strong>', 'automatorwp-mailmint' ),
            /* translators: %1$s: List. */
            'edit_label'    => sprintf( __( 'Add Mail Mint contact to list %1$s', 'automatorwp-mailmint' ), '{list}' ),
            /* translators: %1$s: List. */
            'log_label'     => sprintf( __( 'Add Mail Mint contact to list %1$s', 'automatorwp-mailmint' ), '{list}' ),
            'options'       => array(
                'list' => array(
                    'from'    => 'list',
                    'default' => __( 'list', 'automatorwp-mailmint' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-mailmint' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'list' => automatorwp_utilities_ajax_selector_field( array(
                            'name'       => __( 'List:', 'automatorwp-mailmint' ),
                            'desc'       => __( 'Select the Mail Mint list to add the contact to.', 'automatorwp-mailmint' ),
                            'type'       => 'select',
                            'field'      => 'list',
                            'action_cb'  => 'automatorwp_mailmint_get_lists',
                            'options_cb' => 'automatorwp_mailmint_get_lists',
                            'attributes' => array(
                                'placeholder' => __( 'Select a list', 'automatorwp-mailmint' ),
                            ),
                        ) ),
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

        $list_value = isset( $action_options['list'] ) ? $action_options['list'] : '';
        $list_id    = is_array( $list_value ) ? (int) reset( $list_value ) : (int) $list_value;

        if ( empty( $email ) || ! $list_id ) {
            $this->result = __( 'No email or list provided.', 'automatorwp-mailmint' );
            return;
        }

        $contact = automatorwp_mailmint_get_contact_by_email( $email );

        if ( ! $contact ) {
            $this->result = sprintf( __( 'No Mail Mint contact found for %s.', 'automatorwp-mailmint' ), $email );
            return;
        }

        mailmint_add_contact_to_groups( 'lists', array( $list_id ), (int) $contact->id );

        $list = automatorwp_mailmint_get_group( $list_id );
        $name = $list ? $list->title : $list_id;

        $this->result = sprintf( __( '%1$s added to list "%2$s".', 'automatorwp-mailmint' ), $email, $name );
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

new AutomatorWP_MailMint_Add_To_List();
