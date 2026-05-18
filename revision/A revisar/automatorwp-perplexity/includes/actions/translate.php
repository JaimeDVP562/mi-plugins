<?php
/**
 * Translate Text
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Translate
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Translate extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_translate';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register()
    {
        automatorwp_register_action(
            $this->action,
            array(
                'integration'   => $this->integration,
                'label'         => __( 'Translate text with Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Translate <strong>text</strong> with Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Translate {text} with Perplexity and store the result', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Translate text with Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'text' => array(
                        'from'    => 'text',
                        'default' => __( 'text', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'text' => array(
                                'name'     => __( 'Text to Translate', 'automatorwp-perplexity' ),
                                'desc'     => __( 'The text to translate. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'textarea',
                                'default'  => '',
                                'required' => true,
                            ),
                            'target_language' => array(
                                'name'     => __( 'Target Language', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Language to translate into (e.g. Spanish, French, Japanese). Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'text',
                                'default'  => '',
                                'required' => true,
                            ),
                            'source_language' => array(
                                'name'    => __( 'Source Language', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Language of the original text. Leave empty for auto-detection.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                            'tone' => array(
                                'name'    => __( 'Tone', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Desired register for the translation.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'neutral'  => __( 'Neutral', 'automatorwp-perplexity' ),
                                    'formal'   => __( 'Formal', 'automatorwp-perplexity' ),
                                    'informal' => __( 'Informal', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'neutral',
                            ),
                            'model' => array(
                                'name'    => __( 'Model', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Perplexity model to use.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_models(),
                                'default' => 'sonar',
                            ),
                            'max_tokens' => array(
                                'name'    => __( 'Max Tokens', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Maximum response length in tokens.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => 1024,
                            ),
                            'usage_limit' => array(
                                'name'    => __( 'Usage Limit', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '0',
                            ),
                            'usage_period' => array(
                                'name'    => __( 'Limit Period', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Period over which the limit is counted.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'day'   => __( 'Per day', 'automatorwp-perplexity' ),
                                    'week'  => __( 'Per week', 'automatorwp-perplexity' ),
                                    'month' => __( 'Per month', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'day',
                            ),
                            'response_tag' => array(
                                'name'    => __( 'Store Response As Tag', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                        ),
                    ),
                ),
                'tags'          => array(
                    'perplexity_translation'        => array(
                        'label'   => __( 'Perplexity Translation', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Translated text', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_translation_target' => array(
                        'label'   => __( 'Perplexity Translation Target Language', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Target language used', 'automatorwp-perplexity' ),
                    ),
                ),
            )
        );
    }

    /**
     * Action execution function
     *
     * @since 1.1.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation )
    {
        $text            = isset( $action_options['text'] )            ? $action_options['text']                                    : '';
        $target_language = isset( $action_options['target_language'] ) ? $action_options['target_language']                         : '';
        $source_language = isset( $action_options['source_language'] ) ? $action_options['source_language']                         : '';
        $tone            = isset( $action_options['tone'] )            ? $action_options['tone']                                    : 'neutral';
        $model           = isset( $action_options['model'] )           ? $action_options['model']                                   : 'sonar';
        $max_tokens      = isset( $action_options['max_tokens'] )      ? (int) $action_options['max_tokens']                        : 1024;
        $response_tag    = isset( $action_options['response_tag'] )    ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( empty( $target_language ) ) {
            $this->result = __( 'Target language field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( empty( automatorwp_perplexity_get_api_key() ) ) {
            $this->result = __( 'Perplexity integration not configured in AutomatorWP settings.', 'automatorwp-perplexity' );
            return;
        }

        if ( ! automatorwp_perplexity_check_and_increment_usage( $action, $user_id, $action_options ) ) {
            $this->result = __( 'Usage limit reached. Action skipped.', 'automatorwp-perplexity' );
            return;
        }

        $tone_map = array(
            'neutral'  => 'neutral register',
            'formal'   => 'formal register',
            'informal' => 'informal/conversational register',
        );
        $tone_label = isset( $tone_map[ $tone ] ) ? $tone_map[ $tone ] : 'neutral register';
        $from_label = ! empty( $source_language ) ? 'from ' . $source_language . ' ' : '';

        $system = 'You are a professional translator. Translate text accurately, preserving the original meaning and structure. Output ONLY the translated text with no preamble, commentary, or quotation marks.';
        $prompt = sprintf( 'Translate the following text %sinto %s using a %s:\n\n%s', $from_label, $target_language, $tone_label, $text );

        $response = automatorwp_perplexity_api_request( $model, array(
            array( 'role' => 'system', 'content' => $system ),
            array( 'role' => 'user',   'content' => $prompt ),
        ), array(
            'max_tokens'  => $max_tokens,
            'temperature' => 0.2,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e = automatorwp_perplexity_extract_response( $response );

        automatorwp_update_action_tag( $action->ID, 'perplexity_translation',        $e['text'] );
        automatorwp_update_action_tag( $action->ID, 'perplexity_translation_target', $target_language );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $e['text'] );
        }

        $this->result = sprintf( __( 'Text translated to %s successfully.', 'automatorwp-perplexity' ), $target_language );
    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ),           10, 5 );

        parent::hooks();
    }

    /** @since 1.1.0 */
    public function configuration_notice( $object, $item_type )
    {
        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;

        if ( empty( automatorwp_perplexity_get_api_key() ) ): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Perplexity settings</a> to get this action to work.', 'automatorwp-perplexity' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-perplexity'
                ); ?>
            </div>
        <?php endif;
    }

    /** @since 1.1.0 */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /** @since 1.1.0 */
    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-perplexity' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Perplexity_Translate();
