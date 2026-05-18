<?php
/**
 * Delete Source
 *
 * @package     AutomatorWP\DocsBot\Actions\Delete_Source
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Delete_Source extends AutomatorWP_Integration_Action {

    public $integration = 'docsbot';
    public $action      = 'docsbot_delete_source';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Delete a source from the bot', 'automatorwp-docsbot' ),
            'select_option' => __( 'Delete a <strong>source</strong> from the DocsBot bot', 'automatorwp-docsbot' ),
            /* translators: %1$s: Source ID. */
            'edit_label'    => sprintf( __( 'Delete source %1$s from the DocsBot bot', 'automatorwp-docsbot' ), '{source_id}' ),
            /* translators: %1$s: Source ID. */
            'log_label'     => sprintf( __( 'Delete source %1$s from the DocsBot bot', 'automatorwp-docsbot' ), '{source_id}' ),
            'options'       => array(
                'source_id' => array(
                    'default' => __( 'source', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'source_id' => array(
                            'name'     => __( 'Source ID:', 'automatorwp-docsbot' ),
                            'desc'     => __( 'The ID of the source to delete. Use the {docsbot_source_id} tag from the "Add source" action.', 'automatorwp-docsbot' ),
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
     * @since 1.0.0
     *
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $source_id = isset( $action_options['source_id'] ) ? sanitize_text_field( $action_options['source_id'] ) : '';

        if( empty( $source_id ) ) {
            $this->result = __( 'No source ID provided.', 'automatorwp-docsbot' );
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) {
            $this->result = __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' );
            return;
        }

        $response = automatorwp_docsbot_admin_request( 'sources/' . rawurlencode( $source_id ), 'DELETE' );

        // 404 treated as success — source already deleted, idempotent
        if( is_wp_error( $response ) ) {
            $data = $response->get_error_data();
            if( isset( $data['status'] ) && $data['status'] === 404 ) {
                $this->result = __( 'Source not found (already deleted).', 'automatorwp-docsbot' );
                return;
            }
            $this->result = sprintf(
                __( 'DocsBot API error: %s', 'automatorwp-docsbot' ),
                $response->get_error_message()
            );
            return;
        }

        $this->result = sprintf(
            __( 'Source %s deleted successfully.', 'automatorwp-docsbot' ),
            $source_id
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

new AutomatorWP_DocsBot_Delete_Source();
