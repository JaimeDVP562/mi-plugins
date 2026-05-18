<?php
/**
 * Add Tag to Contact
 *
 * @package     AutomatorWP\Integrations\MailMint\Actions\Add_Tag
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MailMint_Add_Tag extends AutomatorWP_Integration_Action
{
    public $integration = 'mailmint';
    public $action      = 'mailmint_add_tag';
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
            'label'         => __( 'Add a tag to a Mail Mint contact', 'automatorwp-mailmint' ),
            'select_option' => __( 'Add a <strong>tag</strong> to a Mail Mint contact', 'automatorwp-mailmint' ),
            /* translators: %1$s: Tag. */
            'edit_label'    => sprintf( __( 'Add tag %1$s to Mail Mint contact', 'automatorwp-mailmint' ), '{tag}' ),
            /* translators: %1$s: Tag. */
            'log_label'     => sprintf( __( 'Add tag %1$s to Mail Mint contact', 'automatorwp-mailmint' ), '{tag}' ),
            'options'       => array(
                'tag' => array(
                    'from'    => 'tag',
                    'default' => __( 'tag', 'automatorwp-mailmint' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-mailmint' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-mailmint' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'tag' => automatorwp_utilities_ajax_selector_field( array(
                            'name'       => __( 'Tag:', 'automatorwp-mailmint' ),
                            'desc'       => __( 'Select the Mail Mint tag to apply to the contact.', 'automatorwp-mailmint' ),
                            'type'       => 'select',
                            'field'      => 'tag',
                            'action_cb'  => 'automatorwp_mailmint_get_tags',
                            'options_cb' => 'automatorwp_mailmint_get_tags',
                            'attributes' => array(
                                'placeholder' => __( 'Select a tag', 'automatorwp-mailmint' ),
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

        $tag_value = isset( $action_options['tag'] ) ? $action_options['tag'] : '';
        $tag_id    = is_array( $tag_value ) ? (int) reset( $tag_value ) : (int) $tag_value;

        if ( empty( $email ) || ! $tag_id ) {
            $this->result = __( 'No email or tag provided.', 'automatorwp-mailmint' );
            return;
        }

        $contact = automatorwp_mailmint_get_contact_by_email( $email );

        if ( ! $contact ) {
            $this->result = sprintf( __( 'No Mail Mint contact found for %s.', 'automatorwp-mailmint' ), $email );
            return;
        }

        mailmint_add_contact_to_groups( 'tags', array( $tag_id ), (int) $contact->id );

        $tag  = automatorwp_mailmint_get_group( $tag_id );
        $name = $tag ? $tag->title : $tag_id;

        $this->result = sprintf( __( 'Tag "%1$s" added to %2$s.', 'automatorwp-mailmint' ), $name, $email );
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

new AutomatorWP_MailMint_Add_Tag();
