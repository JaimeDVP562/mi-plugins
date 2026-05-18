<?php

/**
 * Action: Analyze Sentiment
 */

if (! defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Grok_Analyze_Sentiment extends AutomatorWP_Integration_Action
{

    public $integration = 'grok';
    public $action      = 'grok_analyze_sentiment';

    /**
     * Holds last response for logging.
     *
     * @var string
     */
    public $response = '';

    public function __construct()
    {
        if (method_exists(get_parent_class($this), '__construct')) {
            parent::__construct();
        }

        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
    }

    /**
     * Register the action
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Analyze sentiment', 'automatorwp-grok'),
            'select_option' => __('Analyze the <strong>sentiment</strong> of a text', 'automatorwp-grok'),
            'edit_label'    => sprintf(__('Analyze sentiment of %1$s', 'automatorwp-grok'), '{text}'),
            'log_label'     => sprintf(__('Analyze sentiment of %1$s', 'automatorwp-grok'), '{text}'),
            'options'       => array(
                'text_to_analyze' => array(
                    'fields'  => array(
                        'text' => array(
                            'name'     => __('Text:', 'automatorwp-grok'),
                            'desc'     => __('The text to analyze (Returns: POSITIVE, NEGATIVE, or NEUTRAL).', 'automatorwp-grok'),
                            'type'     => 'textarea',
                            'required' => true,
                        ),
                    ),
                ),
            ),
            'tags' => automatorwp_grok_get_actions_response_tags(),
        ));
    }

    /**
     * Execute the action
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
        $text_input = isset($action_options['text']) ? trim(sanitize_textarea_field($action_options['text'])) : '';

        if (empty($text_input)) {
            $this->result = __('Error: Text field is empty.', 'automatorwp-grok');
            return;
        }

        // Force a concise response from Grok to simplify logic
        $prompt = "Analyze the sentiment of the following text and respond ONLY with one of these three words: POSITIVE, NEGATIVE, or NEUTRAL. Text: " . $text_input;

        // Call our main API function
        $sentiment = automatorwp_grok_api_request($prompt, 'grok-2-1212');

        if (! $sentiment || is_wp_error($sentiment)) {
            $error_msg = is_wp_error($sentiment) ? $sentiment->get_error_message() : __('Error contacting Grok.', 'automatorwp-grok');
            $this->result   = $error_msg;
            $this->response = '';
            return;
        }

        // Clean response to ensure it's just the keyword for easy filtering
        $clean_sentiment = strtoupper(trim(str_replace('.', '', $sentiment)));

        $this->result   = $clean_sentiment;
        $this->response = $clean_sentiment;

        // Update the automation meta so the tag {grok_last_response} works
        automatorwp_update_automation_meta($automation->id, 'grok_last_response', $clean_sentiment);
    }

    /**
     * Add response meta to action log
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {
        $current_action = isset($action_options['action']) ? $action_options['action'] : '';

        if ($current_action !== $this->action) {
            return $log_meta;
        }

        if (! empty($this->response)) {
            $log_meta['response'] = $this->response;
        }

        return $log_meta;
    }
}

new AutomatorWP_Grok_Analyze_Sentiment();
