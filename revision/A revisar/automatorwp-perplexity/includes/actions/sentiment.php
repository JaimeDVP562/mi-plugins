<?php
/**
 * Analyse Sentiment
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Sentiment
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Sentiment extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_sentiment';

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
                'label'         => __( 'Analyse sentiment of a text with Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Analyse the <strong>sentiment</strong> of a text with Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Analyse sentiment of {text} with Perplexity', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Analyse sentiment with Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'text' => array(
                        'from'    => 'text',
                        'default' => __( 'text', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'text' => array(
                                'name'     => __( 'Text', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Text to analyse. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'textarea',
                                'default'  => '',
                                'required' => true,
                            ),
                            'detail_level' => array(
                                'name'    => __( 'Detail Level', 'automatorwp-perplexity' ),
                                'desc'    => __( 'How much detail the analysis should include.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'basic'    => __( 'Basic (positive / neutral / negative)', 'automatorwp-perplexity' ),
                                    'detailed' => __( 'Detailed (emotions, intensity, explanation)', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'basic',
                            ),
                            'model' => array(
                                'name'    => __( 'Model', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Perplexity model to use.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_models(),
                                'default' => 'sonar',
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
                    'perplexity_sentiment'             => array(
                        'label'   => __( 'Perplexity Sentiment', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'positive / neutral / negative', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_sentiment_score'       => array(
                        'label'   => __( 'Perplexity Sentiment Score', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Score from -1 (negative) to 1 (positive)', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_sentiment_explanation' => array(
                        'label'   => __( 'Perplexity Sentiment Explanation', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Explanation of the sentiment analysis', 'automatorwp-perplexity' ),
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
        $text         = isset( $action_options['text'] )         ? $action_options['text']                                    : '';
        $detail_level = isset( $action_options['detail_level'] ) ? $action_options['detail_level']                            : 'basic';
        $model        = isset( $action_options['model'] )        ? $action_options['model']                                   : 'sonar';
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-perplexity' );
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

        $system = 'You are a sentiment analysis engine. Respond ONLY with a valid JSON object and nothing else. Do not include markdown fences or explanation outside the JSON.';

        if ( $detail_level === 'detailed' ) {
            $prompt = 'Analyse the sentiment of the following text and return a JSON with keys: "sentiment" (positive/neutral/negative), "score" (float from -1 to 1), "emotions" (array of strings), "intensity" (low/medium/high), "explanation" (one sentence).\n\nText: ' . $text;
        } else {
            $prompt = 'Analyse the sentiment of the following text and return a JSON with keys: "sentiment" (positive/neutral/negative), "score" (float from -1 to 1), "explanation" (one sentence).\n\nText: ' . $text;
        }

        $response = automatorwp_perplexity_api_request( $model, array(
            array( 'role' => 'system', 'content' => $system ),
            array( 'role' => 'user',   'content' => $prompt ),
        ), array(
            'max_tokens'  => 512,
            'temperature' => 0,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e    = automatorwp_perplexity_extract_response( $response );
        $raw  = trim( preg_replace( '/^```(?:json)?\s*|\s*```$/', '', $e['text'] ) );
        $data = json_decode( $raw, true );

        $sentiment   = isset( $data['sentiment'] )   ? sanitize_text_field( $data['sentiment'] )       : $e['text'];
        $score       = isset( $data['score'] )        ? (string) $data['score']                         : '';
        $explanation = isset( $data['explanation'] )  ? sanitize_textarea_field( $data['explanation'] ) : '';

        automatorwp_update_action_tag( $action->ID, 'perplexity_sentiment',             $sentiment );
        automatorwp_update_action_tag( $action->ID, 'perplexity_sentiment_score',       $score );
        automatorwp_update_action_tag( $action->ID, 'perplexity_sentiment_explanation', $explanation );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $sentiment );
        }

        $this->result = sprintf( __( 'Sentiment analysed: %s (score: %s).', 'automatorwp-perplexity' ), $sentiment, $score );
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

new AutomatorWP_Perplexity_Sentiment();
