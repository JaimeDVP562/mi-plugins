<?php
/**
 * Create Source
 *
 * @package     AutomatorWP\DocsBot\Actions\Create_Source
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Create_Source extends AutomatorWP_Integration_Action {

    public $integration = 'docsbot';
    public $action      = 'docsbot_create_source';
    public $source_id   = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add a source to the bot', 'automatorwp-docsbot' ),
            'select_option' => __( 'Add a <strong>source</strong> to the DocsBot bot', 'automatorwp-docsbot' ),
            /* translators: %1$s: URL. */
            'edit_label'    => sprintf( __( 'Add %1$s as a source to the DocsBot bot', 'automatorwp-docsbot' ), '{source_url}' ),
            /* translators: %1$s: URL. */
            'log_label'     => sprintf( __( 'Add %1$s as a source to the DocsBot bot', 'automatorwp-docsbot' ), '{source_url}' ),
            'options'       => array(
                'source_url' => array(
                    'default' => __( 'source', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'source_type' => array(
                            'name'    => __( 'Source Type:', 'automatorwp-docsbot' ),
                            'type'    => 'select',
                            'options' => array(
                                'url'     => __( 'URL', 'automatorwp-docsbot' ),
                                'youtube' => __( 'YouTube', 'automatorwp-docsbot' ),
                                'sitemap' => __( 'Sitemap', 'automatorwp-docsbot' ),
                                'rss'     => __( 'RSS Feed', 'automatorwp-docsbot' ),
                            ),
                            'default' => 'url',
                        ),
                        'source_url' => array(
                            'name'     => __( 'URL:', 'automatorwp-docsbot' ),
                            'desc'     => __( 'The URL to add as a source. You can use tags from previous triggers.', 'automatorwp-docsbot' ),
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

        $source_type = isset( $action_options['source_type'] ) ? sanitize_text_field( $action_options['source_type'] ) : 'url';
        $source_url  = isset( $action_options['source_url'] )  ? esc_url_raw( $action_options['source_url'] )          : '';

        if( empty( $source_url ) ) {
            $this->result = __( 'No URL provided.', 'automatorwp-docsbot' );
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) {
            $this->result = __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' );
            return;
        }

        $body = array(
            'type' => $source_type,
            'url'  => $source_url,
        );

        $response = automatorwp_docsbot_admin_request( 'sources', 'POST', $body );

        if( is_wp_error( $response ) ) {
            $this->result = sprintf(
                __( 'DocsBot API error: %s', 'automatorwp-docsbot' ),
                $response->get_error_message()
            );
            return;
        }

        $this->source_id = isset( $response['id'] ) ? sanitize_text_field( $response['id'] ) : '';
        $this->result    = sprintf(
            __( 'Source created with ID: %s', 'automatorwp-docsbot' ),
            $this->source_id
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

        $log_meta['result']            = $this->result;
        $log_meta['docsbot_source_id'] = $this->source_id;

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

        $log_fields['docsbot_source_id'] = array(
            'name' => __( 'Source ID:', 'automatorwp-docsbot' ),
            'desc' => __( 'The ID of the created source. Use it in the "Delete source" action.', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Create_Source();
