<?php
/**
 * Delete Contact
 *
 * @package     AutomatorWP\Integrations\Mailjet\Actions\Delete-Contact
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Mailjet_Delete_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'mailjet';
    public $action = 'mailjet_delete_contact';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Delete a contact', 'automatorwp-mailjet'),
            'select_option'     => __( 'Delete a <strong>contact</strong>', 'automatorwp-mailjet'),
            'edit_label'        => sprintf( __( 'Delete %1$s', 'automatorwp-mailjet' ), '{contact}' ),
            'log_label'         => sprintf( __( 'Delete %1$s', 'automatorwp-mailjet' ), '{contact}' ),
            'options'           => array(
                'contact' => array(
                    'from' => 'contact',
                    'default' => __('contact', 'automatorwp-mailjet'),
                    'fields' => array(
                        'contact' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('Contact: ', 'automatorwp-mailjet'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_mailjet_get_contacts',
                            'options_cb'        => 'automatorwp_mailjet_options_cb_contact',
                            'placeholder'       => 'Select a contact',
                            'default'           => '',
                            'required'          => true
                        )),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        $contact_email = $action_options['contact'];
        //error_log("Deleting contact: {$contact_email}" . PHP_EOL, 3, "debug2.log");

        if ( empty( $contact_email ) ) {
            return;
        }

        if( ! automatorwp_mailjet_get_api() ) {
            $this->result = __( 'Mailjet integration is not configured in AutomatorWP settings', 'automatorwp-mailjet' );
            return;
        }

        $response = automatorwp_mailjet_delete_contact( $contact_email);

        if ( $response === 200 || $response === 204 ) {
            $this->result = __( 'Contact deleted successfully', 'automatorwp-mailjet' );
        } elseif ( $response === 404 ) {
            $this->result = __( 'Contact not found', 'automatorwp-mailjet' );
        } else {
            $this->result = __( 'Failed to delete contact', 'automatorwp-mailjet' );
        }
    }

    public function hooks() {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function configuration_notice( $object, $item_type ) {

        if( $item_type !== 'action' || $object->type !== $this->action ) {
            return;
        }

        if( ! automatorwp_mailjet_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Mailjet settings</a> to get this action to work.', 'automatorwp-mailjet' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-mailjet'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-mailjet' ),
                    'https://automatorwp.com/docs/mailjet/'
                ); ?>
            </div>
        <?php endif;
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' || $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-mailjet' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Mailjet_Delete_Contact();
