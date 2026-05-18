<?php

/**
 * Action: Summarize content
 */

if (! defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Grok_Summarize_Content extends AutomatorWP_Integration_Action
{

    public $integration = 'grok';
    public $action      = 'grok_summarize_content';

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
            'label'         => __('Summarize content', 'automatorwp-grok'),
            'select_option' => __('Generate a <strong>summary</strong> of a text', 'automatorwp-grok'),
            'edit_label'    => sprintf(__('Summarize %1$s', 'automatorwp-grok'), '{content}'),
            'log_label'     => sprintf(__('Summarize %1$s', 'automatorwp-grok'), '{content}'),
            'options'       => array(
                'content_to_summarize' => array(
                    'fields'  => array(
                        'content' => array(
                            'name'     => __('Content:', 'automatorwp-grok'),
                            'desc'     => __('The text or post content to summarize.', 'automatorwp-grok'),
                            'type'     => 'textarea',
                            'required' => true,
                        ),
                        'length' => array(
                            'name'    => __('Summary length:', 'automatorwp-grok'),
                            'desc'    => __('Approximate length of the summary.', 'automatorwp-grok'),
                            'type'    => 'select',
                            'options' => array(
                                'short'  => __('Very short (1 sentence)', 'automatorwp-grok'),
                                'medium' => __('Medium (1 paragraph)', 'automatorwp-grok'),
                                'long'   => __('Detailed (Key points)', 'automatorwp-grok'),
                            ),
                            'default' => 'medium',
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
        $content = isset($action_options['content']) ? trim(sanitize_textarea_field($action_options['content'])) : '';
        $length  = isset($action_options['length']) ? $action_options['length'] : 'medium';

        if (empty($content)) {
            $this->result = __('Error: Content field is empty.', 'automatorwp-grok');
            return;
        }

        // Define specific instructions based on length option
        $instructions = "Summarize the following text briefly.";
        if ($length === 'short') $instructions = "Summarize the following text in exactly one short sentence.";
        if ($length === 'long') $instructions = "Summarize the following text by highlighting the 3 most important key points.";

        $prompt = $instructions . " Text: " . $content;

        // Call our Grok API function
        $summary = automatorwp_grok_api_request($prompt, 'grok-2-1212');

        if (! $summary || is_wp_error($summary)) {
            $error_msg = is_wp_error($summary) ? $summary->get_error_message() : __('Error contacting Grok.', 'automatorwp-grok');
            $this->result   = $error_msg;
            $this->response = '';
            return;
        }

        $safe_summary   = wp_kses_post($summary);
        $this->result   = $safe_summary;
        $this->response = $safe_summary;

        // Update the tag {grok_last_response}
        automatorwp_update_automation_meta($automation->id, 'grok_last_response', $safe_summary);
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

new AutomatorWP_Grok_Summarize_Content();
