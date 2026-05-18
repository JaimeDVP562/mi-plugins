<?php
/**
 * Capture Lead
 *
 * @package     AutomatorWP\DocsBot\Actions\Capture_Lead
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Capture_Lead extends AutomatorWP_Integration_Action {

    public $integration = 'docsbot';
    public $action      = 'docsbot_capture_lead';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Capture a lead on a conversation', 'automatorwp-docsbot' ),
            'select_option' => __( 'Capture a <strong>lead</strong> on a DocsBot conversation', 'automatorwp-docsbot' ),
            /* translators: %1$s: Conversation ID. */
            'edit_label'    => sprintf( __( 'Capture a lead on DocsBot conversation %1$s', 'automatorwp-docsbot' ), '{conversation_id}' ),
            /* translators: %1$s: Conversation ID. */
            'log_label'     => sprintf( __( 'Capture a lead on DocsBot conversation %1$s', 'automatorwp-docsbot' ), '{conversation_id}' ),
            'options'       => array(
                'conversation_id' => array(
                    'default' => __( 'lead', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'conversation_id' => array(
                            'name'     => __( 'Conversation ID:', 'automatorwp-docsbot' ),
                            'desc'     => __( 'The DocsBot conversation ID. Use the {docsbot_conversation_id} tag from a DocsBot trigger.', 'automatorwp-docsbot' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'lead_name' => array(
                            'name'    => __( 'Name:', 'automatorwp-docsbot' ),
                            'desc'    => __( 'The lead\'s full name. You can use tags like {user_name}.', 'automatorwp-docsbot' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'lead_email' => array(
                            'name'    => __( 'Email:', 'automatorwp-docsbot' ),
                            'desc'    => __( 'The lead\'s email address. You can use tags like {user_email}.', 'automatorwp-docsbot' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'lead_company' => array(
                            'name'    => __( 'Company:', 'automatorwp-docsbot' ),
                            'desc'    => __( 'The lead\'s company name (optional).', 'automatorwp-docsbot' ),
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
     * @since 1.0.0
     *
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $conversation_id = isset( $action_options['conversation_id'] ) ? sanitize_text_field( $action_options['conversation_id'] ) : '';
        $lead_name       = isset( $action_options['lead_name'] )       ? sanitize_text_field( $action_options['lead_name'] )       : '';
        $lead_email      = isset( $action_options['lead_email'] )      ? sanitize_email( $action_options['lead_email'] )           : '';
        $lead_company    = isset( $action_options['lead_company'] )    ? sanitize_text_field( $action_options['lead_company'] )    : '';

        if( empty( $conversation_id ) ) {
            $this->result = __( 'No conversation ID provided.', 'automatorwp-docsbot' );
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) {
            $this->result = __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' );
            return;
        }

        $metadata = array();

        if( ! empty( $lead_name ) ) {
            $metadata['name'] = $lead_name;
        }

        if( ! empty( $lead_email ) ) {
            $metadata['email'] = $lead_email;
        }

        if( ! empty( $lead_company ) ) {
            $metadata['company'] = $lead_company;
        }

        $endpoint = sprintf( 'conversations/%s/lead', rawurlencode( $conversation_id ) );
        // PHP encodes empty arrays as JSON arrays ([]) but DocsBot expects an object ({}).
        // Casting to stdClass ensures wp_json_encode produces {} when no metadata fields are provided.
        $response = automatorwp_docsbot_api_request( $endpoint, array( 'metadata' => empty( $metadata ) ? new stdClass() : $metadata ) );

        if( is_wp_error( $response ) ) {
            $this->result = sprintf(
                __( 'DocsBot API error: %s', 'automatorwp-docsbot' ),
                $response->get_error_message()
            );
            return;
        }

        $this->result = sprintf(
            __( 'Lead captured on conversation %s.', 'automatorwp-docsbot' ),
            $conversation_id
        );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_action( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     */
    public function configuration_notice( $object, $item_type ) {

        if( $item_type !== 'action' || $object->type !== $this->action ) {
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    esc_html__( 'You need to configure the %s to use this action.', 'automatorwp-docsbot' ),
                    '<a href="' . esc_url( admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-docsbot' ) ) . '" target="_blank">' . esc_html__( 'DocsBot AI settings', 'automatorwp-docsbot' ) . '</a>'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' || $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Capture_Lead();
