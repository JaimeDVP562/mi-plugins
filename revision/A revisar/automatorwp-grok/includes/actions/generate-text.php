<?php

/**
 * Action: Generate text
 */

if (! defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Grok_Generate_Text extends AutomatorWP_Integration_Action
{

    public $integration = 'grok';
    public $action      = 'grok_generate_text';

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
            'label'         => __('Generate a text', 'automatorwp-grok'),
            'select_option' => __('Generate a <strong>text</strong>', 'automatorwp-grok'),
            'edit_label'    => sprintf(__('Generate text with %1$s', 'automatorwp-grok'), '{prompt}'),
            'log_label'     => sprintf(__('Generate text with %1$s', 'automatorwp-grok'), '{prompt}'),
            'options'       => array(
                'prompt' => array(
                    'fields'  => array(
                        'prompt' => array(
                            'name'     => __('Prompt:', 'automatorwp-grok'),
                            'desc'     => __('The prompt to generate the text using Grok.', 'automatorwp-grok'),
                            'type'     => 'textarea',
                            'required' => true,
                        ),
                        'model' => array(
                            'name'    => __('Model:', 'automatorwp-grok'),
                            'desc'    => __('Choose the Grok model.', 'automatorwp-grok'),
                            'type'    => 'select',
                            'options' => array(
                                'grok-2-1212' => 'Grok 2',
                                'grok-beta'   => 'Grok Beta',
                            ),
                            'default' => 'grok-2-1212',
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
        $prompt = isset($action_options['prompt']) ? trim(sanitize_textarea_field($action_options['prompt'])) : '';
        $model  = isset($action_options['model']) ? sanitize_text_field($action_options['model']) : 'grok-2-1212';

        if (empty($prompt)) {
            $this->result = __('Error: Prompt field is empty.', 'automatorwp-grok');
            return;
        }

        // Call the Grok API function
        $text = automatorwp_grok_api_request($prompt, $model);

        if (! $text || is_wp_error($text)) {
            $error_msg = is_wp_error($text) ? $text->get_error_message() : __('Error: Please check your Grok configuration (API Key).', 'automatorwp-grok');
            $this->result   = $error_msg;
            $this->response = '';
            return;
        }

        $safe_text      = wp_kses_post($text);
        $this->result   = $safe_text;
        $this->response = $safe_text;

        // Update meta for tags
        automatorwp_update_automation_meta($automation->id, 'grok_last_response', $safe_text);
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

new AutomatorWP_Grok_Generate_Text();
