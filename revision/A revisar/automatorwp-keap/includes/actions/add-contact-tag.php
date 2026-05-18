<?php
/**
 * Add Contact Tag
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Add-Contact-Tag
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Keap_Add_Contact_Tag extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action      = 'keap_add_contact_tag';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add tag to contact', 'automatorwp-keap' ),
            'select_option' => __( 'Add a <strong>tag</strong> to a contact', 'automatorwp-keap' ),
            'edit_label'    => __( 'Add tag {tag} to contact {email}', 'automatorwp-keap' ),
            'log_label'     => __( 'Added tag to contact', 'automatorwp-keap' ),
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
                    ),
                ),
                'tag' => array(
                    'from'    => 'tag',
                    'default' => __( 'tag', 'automatorwp-keap' ),
                    'fields'  => array(
                        'tag' => array(
                            'name'     => __( 'Tag ID:', 'automatorwp-keap' ),
                            'desc'     => __( 'Enter the Keap Tag ID (numeric). You can find it in Keap under CRM → Tags.', 'automatorwp-keap' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
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

        $tag_value = $action_options['tag'];

        // Anti-numeric patch: prevent AutomatorWP tag replacement turning text into 0
        $raw_tag = isset( $action->options['tag']['tag'] ) ? $action->options['tag']['tag'] : $tag_value;
        if ( is_numeric( $tag_value ) && ! is_numeric( $raw_tag ) ) {
            $tag_value = $raw_tag;
        }

        $result = automatorwp_keap_add_contact_tag( $contact['id'], $tag_value );

        if ( $result ) {
            $this->result = __( 'Tag added to contact successfully', 'automatorwp-keap' );
        } else {
            $this->result = __( 'Could not add tag to contact. Check that the Tag ID is correct.', 'automatorwp-keap' );
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
        $log_meta['tag']    = isset( $action_options['tag'] ) ? $action_options['tag'] : '';

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

new AutomatorWP_Keap_Add_Contact_Tag();