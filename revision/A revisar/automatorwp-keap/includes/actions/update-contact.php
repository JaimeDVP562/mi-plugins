<?php
/**
 * Update Contact
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Update-Contact
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Keap_Update_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action      = 'keap_update_contact';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Update contact', 'automatorwp-keap' ),
            'select_option' => __( 'Update a <strong>contact</strong> in Keap', 'automatorwp-keap' ),
            'edit_label'    => __( 'Update contact with email {email}', 'automatorwp-keap' ),
            'log_label'     => __( 'Updated contact', 'automatorwp-keap' ),
            'options'       => array(
                'contact' => array(
                    'from'    => 'email',
                    'default' => __( 'email', 'automatorwp-keap' ),
                    'fields'  => array(
                        'email' => array(
                            'name'        => __( 'Contact Email:', 'automatorwp-keap' ),
                            'type'        => 'text',
                            'default'     => '{user_email}',
                            'required'    => true,
                            'placeholder' => 'user@example.com',
                        ),
                        'first_name' => array(
                            'name'    => __( 'First Name:', 'automatorwp-keap' ),
                            'desc'    => __( 'Leave blank to skip updating this field.', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'last_name' => array(
                            'name'    => __( 'Last Name:', 'automatorwp-keap' ),
                            'desc'    => __( 'Leave blank to skip updating this field.', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'phone' => array(
                            'name'    => __( 'Phone:', 'automatorwp-keap' ),
                            'desc'    => __( 'Leave blank to skip updating this field.', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'company' => array(
                            'name'    => __( 'Company:', 'automatorwp-keap' ),
                            'desc'    => __( 'Leave blank to skip updating this field.', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Action execution function
     *
     * @since 1.1.0
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if Keap not configured
        if ( ! automatorwp_keap_get_api() ) {
            $this->result = __( 'Keap integration is not configured in AutomatorWP settings', 'automatorwp-keap' );
            return;
        }

        // Find contact by email
        $contact = automatorwp_keap_get_contact_by_email( $action_options['email'] );

        if ( ! $contact ) {
            $this->result = __( 'Contact not found in Keap', 'automatorwp-keap' );
            return;
        }

        // Build update payload — only include non-empty fields
        $update_data = array();

        if ( ! empty( $action_options['first_name'] ) ) {
            $update_data['given_name'] = sanitize_text_field( $action_options['first_name'] );
        }

        if ( ! empty( $action_options['last_name'] ) ) {
            $update_data['family_name'] = sanitize_text_field( $action_options['last_name'] );
        }

        if ( ! empty( $action_options['phone'] ) ) {
            $update_data['phone_numbers'] = array(
                array(
                    'number' => sanitize_text_field( $action_options['phone'] ),
                    'field'  => 'PHONE1',
                )
            );
        }

        if ( ! empty( $action_options['company'] ) ) {
            $update_data['company'] = array(
                'name' => sanitize_text_field( $action_options['company'] ),
            );
        }

        if ( empty( $update_data ) ) {
            $this->result = __( 'No fields to update', 'automatorwp-keap' );
            return;
        }

        $response = automatorwp_keap_update_contact( $contact['id'], $update_data );

        if ( $response !== false ) {
            $this->result = __( 'Contact updated successfully', 'automatorwp-keap' );
        } else {
            $this->result = __( 'Could not update contact', 'automatorwp-keap' );
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Configuration notice
     *
     * @since 1.1.0
     */
    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' ) {
            return;
        }

        if ( $object->type !== $this->action ) {
            return;
        }

        if ( ! automatorwp_keap_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Keap settings</a> to get this action to work.', 'automatorwp-keap' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-keap'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action log meta
     *
     * @since 1.1.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        $log_meta['email']  = isset( $action_options['email'] ) ? $action_options['email'] : '';

        return $log_meta;
    }

    /**
     * Action log fields
     *
     * @since 1.1.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' ) {
            return $log_fields;
        }

        if ( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-keap' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Keap_Update_Contact();