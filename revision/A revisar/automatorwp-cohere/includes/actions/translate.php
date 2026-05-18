<?php
/**
 * Translate Text
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Translate
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Translate extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_translate';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Translate text with Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Translate <strong>text</strong> with Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Translate {text} with Cohere and store the result', 'automatorwp-cohere' ),
            'log_label'     => __( 'Translate text with Cohere', 'automatorwp-cohere' ),
            'options'       => array(
                'text' => array(
                    'from'    => 'action',
                    'default' => __( 'text', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'text' => array(
                            'name'     => __( 'Text', 'automatorwp-cohere' ),
                            'desc'     => __( 'Text to translate. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'target_language' => array(
                            'name'     => __( 'Target Language', 'automatorwp-cohere' ),
                            'desc'     => __( 'Language to translate into (e.g. Spanish, French, German, Japanese). Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'source_language' => array(
                            'name'    => __( 'Source Language', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Language of the original text. Leave empty to auto-detect. Supports tags.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'usage_limit' => array(
                            'name'    => __( 'Usage Limit', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '0',
                        ),
                        'usage_period' => array(
                            'name'    => __( 'Limit Period', 'automatorwp-cohere' ),
                            'desc'    => __( 'Period over which the limit is counted.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => array(
                                'day'   => __( 'Per day', 'automatorwp-cohere' ),
                                'week'  => __( 'Per week', 'automatorwp-cohere' ),
                                'month' => __( 'Per month', 'automatorwp-cohere' ),
                            ),
                            'default' => 'day',
                        ),
                        'response_tag' => array(
                            'name'    => __( 'Store Translation As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to reuse the translation in subsequent actions.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_translation' => array(
                    'label'   => __( 'Cohere Translation', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Translated text', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $text            = isset( $action_options['text'] )            ? $action_options['text']                          : '';
        $target_language = isset( $action_options['target_language'] ) ? $action_options['target_language']               : '';
        $source_language = isset( $action_options['source_language'] ) ? $action_options['source_language']               : '';
        $response_tag    = isset( $action_options['response_tag'] )    ? sanitize_key( $action_options['response_tag'] )  : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( $target_language ) ) {
            $this->result = __( 'Target language field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( automatorwp_cohere_get_api_key() ) ) {
            $this->result = __( 'Cohere integration not configured in AutomatorWP settings.', 'automatorwp-cohere' );
            return;
        }

        if ( ! automatorwp_cohere_check_and_increment_usage( $action, $user_id, $action_options ) ) {
            $this->result = __( 'Usage limit reached. Action skipped.', 'automatorwp-cohere' );
            return;
        }

        $from_part = ! empty( $source_language ) ? " from {$source_language}" : '';
        $prompt    = "Translate the following text{$from_part} to {$target_language}. "
                   . "Return only the translated text, no explanations.\n\n{$text}";

        // command-a-translate-08-2025 is Cohere's dedicated translation model.
        // It is not exposed as a user-selectable option because it should always
        // be used for this action regardless of other model preferences.
        $response = automatorwp_cohere_api_request( '/v2/chat', array(
            'model'       => 'command-a-translate-08-2025',
            'messages'    => array( array( 'role' => 'user', 'content' => $prompt ) ),
            'max_tokens'  => 4096,
            'temperature' => 0,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $translation = automatorwp_cohere_extract_chat_text( $response );

        automatorwp_update_action_tag( $action->id, 'cohere_translation', $translation );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $translation );
        }

        $this->result = sprintf( __( 'Text translated to %s with Cohere successfully.', 'automatorwp-cohere' ), $target_language );
    }

    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ),           10, 3 );
        parent::hooks();
    }

    public function configuration_notice( $object, $item_type )
    {
        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;
        if ( empty( automatorwp_cohere_get_api_key() ) ): ?>
            <div class="automatorwp-notice-warning" style="margin-top:10px;margin-bottom:0;">
                <?php echo sprintf( __( 'You need to configure the <a href="%s" target="_blank">Cohere settings</a> to get this action to work.', 'automatorwp-cohere' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-cohere' ); ?>
            </div>
        <?php endif;
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-cohere' ), 'type' => 'text' );
        $log_fields['cohere_translation'] = array( 'name' => __( 'Translation:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Translate();
