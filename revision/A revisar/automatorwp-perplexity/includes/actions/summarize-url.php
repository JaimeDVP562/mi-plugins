<?php
/**
 * Summarize URL
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Summarize_URL
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Summarize_URL extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_summarize_url';

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
                'label'         => __( 'Summarize a URL with Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Summarize a <strong>URL</strong> with Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Summarize {url} with Perplexity and store the summary', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Summarize a URL with Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'url' => array(
                        'from'    => 'url',
                        'default' => __( 'URL', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'url' => array(
                                'name'     => __( 'URL', 'automatorwp-perplexity' ),
                                'desc'     => __( 'URL of the page or article to summarize. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'text',
                                'default'  => '',
                                'required' => true,
                            ),
                            'summary_focus' => array(
                                'name'    => __( 'Summary Focus', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) What to focus on (e.g. "key findings", "pricing"). Supports tags.', 'automatorwp-perplexity' ),
                                'type'    => 'textarea',
                                'default' => '',
                            ),
                            'summary_length' => array(
                                'name'    => __( 'Summary Length', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Approximate length of the generated summary.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'short'  => __( 'Short (1-2 paragraphs)', 'automatorwp-perplexity' ),
                                    'medium' => __( 'Medium (3-5 paragraphs)', 'automatorwp-perplexity' ),
                                    'long'   => __( 'Long (detailed)', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'medium',
                            ),
                            'language' => array(
                                'name'    => __( 'Output Language', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Language for the summary (e.g. English, Spanish). Defaults to source language.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                            'model' => array(
                                'name'    => __( 'Model', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Perplexity model to use.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_models(),
                                'default' => 'sonar',
                            ),
                            'response_format' => array(
                                'name'    => __( 'Response Format', 'automatorwp-perplexity' ),
                                'desc'    => __( 'How Perplexity should format its response.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_response_formats(),
                                'default' => 'text',
                            ),
                            'max_tokens' => array(
                                'name'    => __( 'Max Tokens', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Maximum response length in tokens.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => 1024,
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
                    'perplexity_url_summary'        => array(
                        'label'   => __( 'Perplexity URL Summary', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Summary of the URL content', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_url_search_results' => array(
                        'label'   => __( 'Perplexity URL Search Results', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Source titles and URLs cited', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_url_summarized'     => array(
                        'label'   => __( 'Perplexity Summarized URL', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'The URL that was summarized', 'automatorwp-perplexity' ),
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
        $url                   = isset( $action_options['url'] )                   ? $action_options['url']                                     : '';
        $summary_focus         = isset( $action_options['summary_focus'] )         ? $action_options['summary_focus']                           : '';
        $summary_length        = isset( $action_options['summary_length'] )        ? $action_options['summary_length']                          : 'medium';
        $language              = isset( $action_options['language'] )              ? $action_options['language']                                : '';
        $model                 = isset( $action_options['model'] )                 ? $action_options['model']                                   : 'sonar';
        $response_format       = isset( $action_options['response_format'] )       ? $action_options['response_format']                         : 'text';
        $max_tokens            = isset( $action_options['max_tokens'] )            ? (int) $action_options['max_tokens']                        : 1024;
        $search_recency_filter = isset( $action_options['search_recency_filter'] ) ? $action_options['search_recency_filter']                    : '';
        $response_tag          = isset( $action_options['response_tag'] )          ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $url ) ) {
            $this->result = __( 'URL field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            $this->result = sprintf( __( '"%s" is not a valid URL.', 'automatorwp-perplexity' ), $url );
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

        $length_map = array(
            'short'  => 'Write a concise summary of 1 to 2 paragraphs.',
            'medium' => 'Write a clear summary of 3 to 5 paragraphs.',
            'long'   => 'Write a detailed and comprehensive summary.',
        );
        $length_instruction = isset( $length_map[ $summary_length ] ) ? $length_map[ $summary_length ] : $length_map['medium'];

        $prompt = sprintf( "Please read and summarize the content at: %s\n\n%s", esc_url_raw( $url ), $length_instruction );

        if ( ! empty( $summary_focus ) ) {
            $prompt .= "\n\nFocus specifically on: " . $summary_focus;
        }

        if ( ! empty( $language ) ) {
            $prompt .= "\n\nWrite the summary in " . $language . '.';
        }

        $system   = automatorwp_perplexity_apply_response_format( $response_format, '' );
        $messages = array();

        if ( ! empty( $system ) ) {
            $messages[] = array( 'role' => 'system', 'content' => $system );
        }

        $messages[] = array( 'role' => 'user', 'content' => $prompt );

        $api_args = array(
            'max_tokens'  => $max_tokens,
            'temperature' => 0.2,
        );
        if ( ! empty( $search_recency_filter ) ) $api_args['search_recency_filter'] = $search_recency_filter;

        $response = automatorwp_perplexity_api_request( $model, $messages, $api_args );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e = automatorwp_perplexity_extract_response( $response );

        automatorwp_update_action_tag( $action->ID, 'perplexity_url_summary',        $e['text'] );
        automatorwp_update_action_tag( $action->ID, 'perplexity_url_search_results', $e['search_results'] );
        automatorwp_update_action_tag( $action->ID, 'perplexity_url_summarized',     $url );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $e['text'] );
        }

        $this->result = sprintf( __( 'URL summarized successfully. Summary length: %d characters.', 'automatorwp-perplexity' ), strlen( $e['text'] ) );
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ),           10, 5 );

        parent::hooks();
    }

    /** @since 1.0.0 */
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

    /** @since 1.0.0 */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /** @since 1.0.0 */
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

new AutomatorWP_Perplexity_Summarize_URL();
