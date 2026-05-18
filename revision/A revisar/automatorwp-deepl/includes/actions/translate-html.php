<?php
/**
 * Translate HTML
 *
 * @package     AutomatorWP\Integrations\DeepL\Actions\Translate_HTML
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DeepL_Translate_HTML extends AutomatorWP_Integration_Action {

    public $integration = 'deepl';
    public $action      = 'deepl_translate_html';
    public $result      = '';
    public $response    = '';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Translate an HTML content', 'automatorwp-deepl' ),
            'select_option' => __( 'Translate an <strong>HTML content</strong>', 'automatorwp-deepl' ),
            'edit_label'    => sprintf( __( 'Translate HTML %1$s to %2$s', 'automatorwp-deepl' ), '{html}', '{target_lang}' ),
            'log_label'     => sprintf( __( 'Translate HTML %1$s to %2$s', 'automatorwp-deepl' ), '{html}', '{target_lang}' ),
            'options'       => array(
                'html' => array(
                    'default' => __( 'HTML', 'automatorwp-deepl' ),
                    'fields'  => array(
                        'html' => array(
                            'name'     => __( 'HTML content:', 'automatorwp-deepl' ),
                            'desc'     => __( 'HTML content to translate. Tags are preserved and not counted as characters.', 'automatorwp-deepl' ),
                            'type'     => 'textarea',
                            'required' => true,
                            'default'  => '',
                        ),
                        // Optional: auto-detected if left empty
                        'source_lang' => array(
                            'name'       => __( 'Source language:', 'automatorwp-deepl' ),
                            'desc'       => __( 'Leave on auto-detect if you do not know the source language.', 'automatorwp-deepl' ),
                            'type'       => 'select',
                            'classes'    => 'automatorwp-selector',
                            'options_cb' => 'automatorwp_deepl_get_source_languages',
                            'default'    => '',
                        ),
                    ),
                ),
                'target_lang' => array(
                    'from'    => 'target_lang',
                    'default' => __( 'language', 'automatorwp-deepl' ),
                    'fields'  => array(
                        'target_lang' => array(
                            'name'       => __( 'Target language:', 'automatorwp-deepl' ),
                            'type'       => 'select',
                            'required'   => true,
                            'classes'    => 'automatorwp-selector',
                            'options_cb' => 'automatorwp_deepl_get_target_languages',
                            'default'    => 'EN-US',
                        ),
                    ),
                ),
            ),
            'tags' => automatorwp_deepl_get_actions_response_tags(),
        ) );

    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->result   = '';
        $this->response = '';

        $html        = $action_options['html'];
        $target_lang = $action_options['target_lang'];
        $source_lang = isset( $action_options['source_lang'] ) ? $action_options['source_lang'] : '';

        if ( empty( $html ) ) {
            $this->result = __( 'HTML field is empty.', 'automatorwp-deepl' );
            return;
        }

        if ( empty( $target_lang ) ) {
            $this->result = __( 'Target language field is empty.', 'automatorwp-deepl' );
            return;
        }

        if ( ! automatorwp_deepl_get_api() ) {
            $this->result = __( 'DeepL API Key is not configured.', 'automatorwp-deepl' );
            return;
        }

        // tag_handling=html preserves HTML structure and excludes tags from character billing
        $result = automatorwp_deepl_translate( $html, $target_lang, $source_lang, 'html' );

        if ( is_array( $result ) && isset( $result['error'] ) ) {
            $this->result = $result['error'];
            return;
        }

        // Sanitize HTML output before storing
        $this->response = wp_kses_post( $result );
        $this->result   = $this->response;

    }

    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;

        if ( ! automatorwp_deepl_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">DeepL settings</a> to get this action to work.', 'automatorwp-deepl' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-deepl'
                ); ?>
            </div>
        <?php endif;

    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result']   = $this->result;
        $log_meta['response'] = isset( $this->response ) ? $this->response : '';

        return $log_meta;

    }

    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-deepl' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DeepL_Translate_HTML();