<?php
/**
 * Send Prompt
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Send_Prompt
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Send_Prompt extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_send_prompt';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_action(
            $this->action,
            array(
                'integration'   => $this->integration,
                'label'         => __( 'Send a prompt to Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Send a <strong>prompt</strong> to Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Send {prompt} to Perplexity and store the response', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Send a prompt to Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'prompt' => array(
                        'from'    => 'prompt',
                        'default' => __( 'prompt', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'model'                 => array(
                                'name'    => __( 'Model', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Perplexity model to use.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_models(),
                                'default' => 'sonar',
                            ),
                            'system_message'        => array(
                                'name'    => __( 'System Message', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) AI behavior or persona. Supports tags.', 'automatorwp-perplexity' ),
                                'type'    => 'textarea',
                                'default' => '',
                            ),
                            'prompt'                => array(
                                'name'     => __( 'Prompt', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Question or instruction to send. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'textarea',
                                'default'  => '',
                                'required' => true,
                            ),
                            'response_format'       => array(
                                'name'    => __( 'Response Format', 'automatorwp-perplexity' ),
                                'desc'    => __( 'How Perplexity should format its response.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_response_formats(),
                                'default' => 'text',
                            ),
                            'max_tokens'            => array(
                                'name'    => __( 'Max Tokens', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Maximum response length in tokens.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => 1024,
                            ),
                            'temperature'           => array(
                                'name'    => __( 'Temperature', 'automatorwp-perplexity' ),
                                'desc'    => __( '0 = deterministic, 2 = very creative. Default: 1.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '1',
                            ),
                            'search_mode'           => array(
                                'name'    => __( 'Search Mode', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Type of sources to search. Leave empty to use the default (web).', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    ''         => __( 'Default (web)', 'automatorwp-perplexity' ),
                                    'web'      => __( 'Web', 'automatorwp-perplexity' ),
                                    'academic' => __( 'Academic', 'automatorwp-perplexity' ),
                                    'sec'      => __( 'SEC filings', 'automatorwp-perplexity' ),
                                ),
                                'default' => '',
                            ),
                            'search_recency_filter' => array(
                                'name'    => __( 'Search Recency Filter', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Restrict sources to a recent time window.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    ''      => __( 'No filter', 'automatorwp-perplexity' ),
                                    'day'   => __( 'Past day', 'automatorwp-perplexity' ),
                                    'week'  => __( 'Past week', 'automatorwp-perplexity' ),
                                    'month' => __( 'Past month', 'automatorwp-perplexity' ),
                                    'year'  => __( 'Past year', 'automatorwp-perplexity' ),
                                ),
                                'default' => '',
                            ),
                            'search_domain_filter'  => array(
                                'name'    => __( 'Search Domain Filter', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Comma-separated list of domains to restrict or exclude (prefix with - to exclude, e.g. example.com, -spam.com). Supports tags.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                            'reasoning_effort'      => array(
                                'name'    => __( 'Reasoning Effort', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Controls how much reasoning the model performs. Only applies to Sonar Reasoning Pro and Deep Research models.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    ''       => __( 'Default', 'automatorwp-perplexity' ),
                                    'low'    => __( 'Low (faster, less thorough)', 'automatorwp-perplexity' ),
                                    'medium' => __( 'Medium', 'automatorwp-perplexity' ),
                                    'high'   => __( 'High (slower, more thorough)', 'automatorwp-perplexity' ),
                                ),
                                'default' => '',
                            ),
                            'usage_limit'           => array(
                                'name'    => __( 'Usage Limit', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '0',
                            ),
                            'usage_period'          => array(
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
                            'response_tag'          => array(
                                'name'    => __( 'Store Response As Tag', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                        ),
                    ),
                ),
                'tags'          => array(
                    'perplexity_response'       => array(
                        'label'   => __( 'Perplexity Response', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'AI-generated response', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_search_results' => array(
                        'label'   => __( 'Perplexity Search Results', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Source titles and URLs cited', 'automatorwp-perplexity' ),
                    ),
                ),
            )
        );
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model                 = isset( $action_options['model'] )                 ? $action_options['model']                                   : 'sonar';
        $system_message        = isset( $action_options['system_message'] )        ? $action_options['system_message']                          : '';
        $prompt                = isset( $action_options['prompt'] )                ? $action_options['prompt']                                  : '';
        $response_format       = isset( $action_options['response_format'] )       ? $action_options['response_format']                         : 'text';
        $max_tokens            = isset( $action_options['max_tokens'] )            ? (int) $action_options['max_tokens']                        : 1024;
        $temperature           = isset( $action_options['temperature'] )           ? (float) $action_options['temperature']                     : 1;
        $search_mode           = isset( $action_options['search_mode'] )           ? $action_options['search_mode']                             : '';
        $search_recency_filter = isset( $action_options['search_recency_filter'] ) ? $action_options['search_recency_filter']                    : '';
        $search_domain_filter  = isset( $action_options['search_domain_filter'] )  ? $action_options['search_domain_filter']                     : '';
        $reasoning_effort      = isset( $action_options['reasoning_effort'] )      ? $action_options['reasoning_effort']                        : '';
        $response_tag          = isset( $action_options['response_tag'] )          ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $prompt ) ) {
            $this->result = __( 'Prompt field is empty.', 'automatorwp-perplexity' );
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

        $system_message = automatorwp_perplexity_apply_response_format( $response_format, $system_message );

        $messages = array();
        if ( ! empty( $system_message ) ) {
            $messages[] = array( 'role' => 'system', 'content' => $system_message );
        }
        $messages[] = array( 'role' => 'user', 'content' => $prompt );

        $api_args = array(
            'max_tokens'  => $max_tokens,
            'temperature' => $temperature,
        );
        if ( ! empty( $search_mode ) )           $api_args['search_mode']           = $search_mode;
        if ( ! empty( $search_recency_filter ) ) $api_args['search_recency_filter'] = $search_recency_filter;
        if ( ! empty( $search_domain_filter ) ) {
            $api_args['search_domain_filter'] = array_values( array_filter( array_map( 'trim', explode( ',', $search_domain_filter ) ) ) );
        }
        if ( ! empty( $reasoning_effort ) )      $api_args['reasoning_effort']      = $reasoning_effort;

        $response = automatorwp_perplexity_api_request( $model, $messages, $api_args );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e = automatorwp_perplexity_extract_response( $response );

        automatorwp_update_action_tag( $action->ID, 'perplexity_response',       $e['text'] );
        automatorwp_update_action_tag( $action->ID, 'perplexity_search_results', $e['search_results'] );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $e['text'] );
        }

        $this->result = sprintf( __( 'Prompt sent to Perplexity successfully. Response length: %d characters.', 'automatorwp-perplexity' ), strlen( $e['text'] ) );
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label',  array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta',  array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                      array( $this, 'log_fields' ),           10, 5 );

        parent::hooks();
    }

    /**
     * Configuration notice when API key is not set
     *
     * @since 1.0.0
     *
     * @param stdClass $object    The trigger/action object
     * @param string   $item_type The object type (trigger|action)
     */
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

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array    $log_meta       Log meta data
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array    $log_fields The log fields
     * @param stdClass $log        The log object
     * @param stdClass $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
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

new AutomatorWP_Perplexity_Send_Prompt();
