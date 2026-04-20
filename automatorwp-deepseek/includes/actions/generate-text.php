<?php

/**
 * Action: Generate text
 */

if (! defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Deepseek_Generate_Text extends AutomatorWP_Integration_Action
{

    public $integration = 'deepseek';
    public $action      = 'deepseek_generate_text';

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

    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Generate a text', 'automatorwp-deepseek'),
            'select_option' => __('Generate a <strong>text</strong>', 'automatorwp-deepseek'),
            'edit_label'    => sprintf(__('Generate text with %1$s', 'automatorwp-deepseek'), '{prompt}'),
            'log_label'     => sprintf(__('Generate text with %1$s', 'automatorwp-deepseek'), '{prompt}'),
            'options'       => array(
                'prompt' => array(
                    'fields'  => array(
                        'prompt' => array(
                            'name'     => __('Prompt:', 'automatorwp-deepseek'),
                            'desc'     => __('The prompt to generate the text.', 'automatorwp-deepseek'),
                            'type'     => 'textarea',
                            'required' => true,
                        ),
                        // Added model selector to choose between chat and reasoner
                        'model' => array(
                            'name'    => __('Model:', 'automatorwp-deepseek'),
                            'desc'    => __('Choose the DeepSeek model.', 'automatorwp-deepseek'),
                            'type'    => 'select',
                            'options' => array(
                                'deepseek-chat'     => 'DeepSeek Chat',
                                'deepseek-reasoner' => 'DeepSeek Reasoner (DeepThink)',
                            ),
                            'default' => 'deepseek-chat',
                        ),
                    ),
                ),
            ),
            // Updated to the new tag function for DeepSeek
            'tags' => automatorwp_deepseek_get_actions_response_tags(),
        ));
    }

    public function execute($action, $user_id, $action_options, $automation)
    {
        $prompt = isset($action_options['prompt']) ? trim(sanitize_textarea_field($action_options['prompt'])) : '';
        $model  = isset($action_options['model']) ? sanitize_text_field($action_options['model']) : 'deepseek-chat';

        if (empty($prompt)) {
            $this->result = __('Error: Prompt field is empty.', 'automatorwp-deepseek');
            return;
        }

        // Enforce maximum prompt length to avoid huge payloads
        $max_len = defined('AUTOMATORWP_DEEPSEEK_MAX_PROMPT_LENGTH') ? AUTOMATORWP_DEEPSEEK_MAX_PROMPT_LENGTH : 3000;
        $truncated = false;
        if (function_exists('mb_strlen') && mb_strlen($prompt) > $max_len) {
            $prompt = mb_substr($prompt, 0, $max_len);
            $truncated = true;
        } elseif (! function_exists('mb_strlen') && strlen($prompt) > $max_len) {
            $prompt = substr($prompt, 0, $max_len);
            $truncated = true;
        }

        // Call our new DeepSeek API function
        $text = automatorwp_deepseek_api_request($prompt, $model);

        if (! $text || is_wp_error($text)) {
            $error_msg = is_wp_error($text) ? $text->get_error_message() : __('Error: Please check your DeepSeek configuration (API Key).', 'automatorwp-deepseek');
            $this->result   = $error_msg;
            $this->response = '';
            return;
        }

        // Store result and response for tags/log meta
        $safe_text = wp_kses_post($text);
        if ($truncated) {
            $safe_text = sprintf(__('(Prompt truncated to %d characters) ', 'automatorwp-deepseek'), $max_len) . $safe_text;
        }
        $this->result   = $safe_text;
        $this->response = $safe_text;

        // Update the automation meta so the tag {deepseek_response} works
        automatorwp_update_automation_meta($automation->id, 'deepseek_last_response', $safe_text);
    }

    /**
     * Add response meta to action log.
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {
        $current_action = isset($action_options['action']) ? $action_options['action'] : '';

        // Ensure this runs only for this action
        if ($current_action !== $this->action) {
            return $log_meta;
        }

        if (! empty($this->response)) {
            $log_meta['response'] = $this->response;
        }

        return $log_meta;
    }
}

new AutomatorWP_Deepseek_Generate_Text();
