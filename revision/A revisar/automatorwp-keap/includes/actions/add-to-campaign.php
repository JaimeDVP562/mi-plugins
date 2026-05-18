<?php
/**
 * Add to Campaign
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Add-To-Campaign
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Keap_Add_To_Campaign extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action      = 'keap_add_to_campaign';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add contact to campaign', 'automatorwp-keap' ),
            'select_option' => __( 'Add contact to a <strong>campaign</strong> in Keap', 'automatorwp-keap' ),
            'edit_label'    => __( 'Add contact {email} to campaign {campaign_id}', 'automatorwp-keap' ),
            'log_label'     => __( 'Added contact to campaign', 'automatorwp-keap' ),
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
                'campaign_id' => array(
                    'from'    => 'campaign_id',
                    'default' => __( 'campaign', 'automatorwp-keap' ),
                    'fields'  => array(
                        'campaign_id' => array(
                            'name'     => __( 'Campaign ID:', 'automatorwp-keap' ),
                            'desc'     => __( 'Enter the Keap Campaign ID. Found in Keap under Marketing → Campaigns.', 'automatorwp-keap' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'sequence_id' => array(
                            'name'     => __( 'Sequence ID:', 'automatorwp-keap' ),
                            'desc'     => __( 'Enter the Sequence ID within the campaign. Required by the Keap API.', 'automatorwp-keap' ),
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

        $campaign_id = absint( $action_options['campaign_id'] );
        $sequence_id = absint( $action_options['sequence_id'] );

        if ( empty( $campaign_id ) || empty( $sequence_id ) ) {
            $this->result = __( 'Campaign ID and Sequence ID are required', 'automatorwp-keap' );
            return;
        }

        $result = automatorwp_keap_add_contact_to_campaign( $contact['id'], $campaign_id, $sequence_id );

        if ( $result ) {
            $this->result = __( 'Contact added to campaign successfully', 'automatorwp-keap' );
        } else {
            $this->result = __( 'Could not add contact to campaign. Check Campaign ID and Sequence ID.', 'automatorwp-keap' );
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

        $log_meta['result']      = $this->result;
        $log_meta['email']       = isset( $action_options['email'] ) ? $action_options['email'] : '';
        $log_meta['campaign_id'] = isset( $action_options['campaign_id'] ) ? $action_options['campaign_id'] : '';
        $log_meta['sequence_id'] = isset( $action_options['sequence_id'] ) ? $action_options['sequence_id'] : '';

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

new AutomatorWP_Keap_Add_To_Campaign();