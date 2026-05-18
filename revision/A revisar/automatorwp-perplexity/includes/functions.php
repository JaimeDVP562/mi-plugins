<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\Perplexity\Functions
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── API KEY ───────────────────────────────────────────────────────────────

/**
 * Get the Perplexity API key from AutomatorWP settings
 *
 * @since  1.0.0
 *
 * @return string
 */
function automatorwp_perplexity_get_api_key() {
    return automatorwp_get_option( 'automatorwp_perplexity_api_key', '' );
}

// ─── API REQUEST (retry + exponential back-off) ────────────────────────────

/**
 * Make a Perplexity API request.
 * Retries on 429 / 5xx with 1 s → 2 s → 4 s back-off.
 *
 * @since  1.0.0
 *
 * @param string $model
 * @param array  $messages  [{role, content}, …]
 * @param array  $args      Extra body params
 * @param int    $retries   Max attempts (default 3)
 *
 * @return array|WP_Error
 */
function automatorwp_perplexity_api_request( $model, $messages, $args = array(), $retries = 3 ) {

    $api_key = automatorwp_perplexity_get_api_key();
    if ( empty( $api_key ) ) {
        return new WP_Error( 'missing_api_key', __( 'Perplexity API key is missing. Please configure it in AutomatorWP settings.', 'automatorwp-perplexity' ) );
    }

    $body            = array_merge( array( 'model' => $model, 'messages' => $messages ), $args );
    $retryable_codes = array( 429, 500, 502, 503, 504 );
    $attempt         = 0;
    $last_error      = null;

    while ( $attempt < $retries ) {
        if ( $attempt > 0 ) sleep( (int) pow( 2, $attempt - 1 ) );

        $http = wp_remote_post( 'https://api.perplexity.ai/chat/completions', array(
            'timeout' => 90,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body' => wp_json_encode( $body ),
        ) );
        $attempt++;

        if ( is_wp_error( $http ) ) { $last_error = $http; continue; }

        $code    = (int) wp_remote_retrieve_response_code( $http );
        $decoded = json_decode( wp_remote_retrieve_body( $http ), true );

        if ( $code === 200 ) return $decoded;

        $msg = isset( $decoded['error']['message'] ) ? $decoded['error']['message']
             : sprintf( __( 'HTTP %d error from Perplexity API.', 'automatorwp-perplexity' ), $code );

        if ( in_array( $code, $retryable_codes, true ) ) {
            $last_error = new WP_Error( 'api_error_' . $code, $msg );
            continue;
        }

        return new WP_Error( 'api_error', $msg );
    }

    if ( $last_error instanceof WP_Error ) {
        return new WP_Error( $last_error->get_error_code(),
            sprintf( __( 'Perplexity API failed after %d attempts: %s', 'automatorwp-perplexity' ),
                $retries, $last_error->get_error_message() ) );
    }

    return new WP_Error( 'api_error', __( 'Perplexity API request failed after multiple attempts.', 'automatorwp-perplexity' ) );
}

// ─── MODELS & FORMATS ─────────────────────────────────────────────────────

/**
 * Get the list of available Perplexity models
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_get_models() {
    return array(
        'sonar'               => __( 'Sonar (lightweight, fast)', 'automatorwp-perplexity' ),
        'sonar-pro'           => __( 'Sonar Pro (advanced reasoning)', 'automatorwp-perplexity' ),
        'sonar-deep-research' => __( 'Sonar Deep Research (comprehensive)', 'automatorwp-perplexity' ),
        'sonar-reasoning-pro' => __( 'Sonar Reasoning Pro (advanced CoT)', 'automatorwp-perplexity' ),
    );
}

/**
 * Get the list of available response formats
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_get_response_formats() {
    return array(
        'text'     => __( 'Plain text', 'automatorwp-perplexity' ),
        'markdown' => __( 'Markdown', 'automatorwp-perplexity' ),
        'json'     => __( 'JSON', 'automatorwp-perplexity' ),
    );
}

/**
 * Append a response-format instruction to the system prompt
 *
 * @since  1.0.0
 *
 * @param string $format          One of: text, markdown, json
 * @param string $existing_system Existing system prompt to append to
 *
 * @return string
 */
function automatorwp_perplexity_apply_response_format( $format, $existing_system = '' ) {
    $map = array(
        'text'     => 'Respond using plain text only. Do not use Markdown, HTML, bullet points, headers, or any special formatting.',
        'markdown' => 'Format your response using Markdown. Use headers, bullet points, bold text and code blocks where appropriate.',
        'json'     => 'Respond ONLY with a valid JSON object. No preamble, no explanation, no Markdown fences. The output must be directly parseable by JSON.parse().',
    );
    if ( ! isset( $map[ $format ] ) || $format === 'text' ) return $existing_system;
    return ! empty( $existing_system ) ? $existing_system . "\n\n" . $map[ $format ] : $map[ $format ];
}

// ─── USAGE LIMITING ───────────────────────────────────────────────────────

/**
 * Build the option key used to store usage data for a given scope and period
 *
 * @since  1.0.0
 *
 * @param string $scope_key Unique scope identifier
 * @param string $period    day|week|month
 *
 * @return string
 */
function automatorwp_perplexity_usage_option_key( $scope_key, $period ) {
    return 'automatorwp_perplexity_usage_' . md5( $scope_key . '_' . $period );
}

/**
 * Get the Unix timestamp at which the given period expires
 *
 * @since  1.0.0
 *
 * @param string $period day|week|month
 *
 * @return int
 */
function automatorwp_perplexity_period_expiry( $period ) {
    switch ( $period ) {
        case 'week':  return strtotime( 'next Monday midnight' );
        case 'month': return strtotime( 'first day of next month midnight' );
        default:      return strtotime( 'tomorrow midnight' );
    }
}

/**
 * Get the current usage count for a given scope and period
 *
 * @since  1.0.0
 *
 * @param string $scope_key Unique scope identifier
 * @param string $period    day|week|month
 *
 * @return int
 */
function automatorwp_perplexity_get_usage_count( $scope_key, $period = 'day' ) {
    $data = get_option( automatorwp_perplexity_usage_option_key( $scope_key, $period ), array( 'count' => 0, 'expires' => 0 ) );
    return ( time() > (int) $data['expires'] ) ? 0 : (int) $data['count'];
}

/**
 * Increment the usage counter for a given scope and period
 *
 * @since  1.0.0
 *
 * @param string $scope_key Unique scope identifier
 * @param string $period    day|week|month
 *
 * @return void
 */
function automatorwp_perplexity_increment_usage( $scope_key, $period = 'day' ) {
    $key  = automatorwp_perplexity_usage_option_key( $scope_key, $period );
    $data = get_option( $key, array( 'count' => 0, 'expires' => 0 ) );
    if ( time() > (int) $data['expires'] ) { $data['count'] = 0; $data['expires'] = automatorwp_perplexity_period_expiry( $period ); }
    $data['count']++;
    update_option( $key, $data, false );
}

/**
 * Check whether the usage limit has been reached for a given scope and period
 *
 * @since  1.0.0
 *
 * @param string $scope_key Unique scope identifier
 * @param int    $limit     Maximum allowed uses (0 = unlimited)
 * @param string $period    day|week|month
 *
 * @return bool
 */
function automatorwp_perplexity_is_limit_reached( $scope_key, $limit, $period = 'day' ) {
    if ( (int) $limit <= 0 ) return false;
    return automatorwp_perplexity_get_usage_count( $scope_key, $period ) >= (int) $limit;
}

/**
 * Check & increment usage. Returns false if limit is reached, true otherwise.
 *
 * @since  1.0.0
 * @since  1.1.0 Accepts $action_options to read limit/period from parsed values.
 *
 * @param stdClass $action
 * @param int      $user_id
 * @param array    $action_options Pre-parsed action options (must contain usage_limit and usage_period)
 *
 * @return bool
 */
function automatorwp_perplexity_check_and_increment_usage( $action, $user_id, $action_options = array() ) {
    $limit  = isset( $action_options['usage_limit'] )  ? (int) $action_options['usage_limit']  : 0;
    $period = isset( $action_options['usage_period'] ) ? $action_options['usage_period']        : 'day';
    if ( $limit <= 0 ) return true;
    $scope = 'user_' . $user_id . '_action_' . $action->ID;
    if ( automatorwp_perplexity_is_limit_reached( $scope, $limit, $period ) ) {
        return false;
    }
    automatorwp_perplexity_increment_usage( $scope, $period );
    return true;
}

// ─── CONVERSATION HISTORY ─────────────────────────────────────────────────

/**
 * Get the stored conversation history for a given conversation ID
 *
 * @since  1.0.0
 *
 * @param string $id Conversation identifier
 *
 * @return array
 */
function automatorwp_perplexity_get_conversation_history( $id ) {
    $data = get_option( 'automatorwp_perplexity_conv_' . md5( $id ), array() );
    return is_array( $data ) ? $data : array();
}

/**
 * Save the conversation history for a given conversation ID
 *
 * @since  1.0.0
 *
 * @param string $id      Conversation identifier
 * @param array  $history Array of message objects [{role, content}]
 *
 * @return void
 */
function automatorwp_perplexity_save_conversation_history( $id, $history ) {
    update_option( 'automatorwp_perplexity_conv_' . md5( $id ), $history, false );
}

/**
 * Delete the stored conversation history for a given conversation ID
 *
 * @since  1.0.0
 *
 * @param string $id Conversation identifier
 *
 * @return void
 */
function automatorwp_perplexity_clear_conversation_history( $id ) {
    delete_option( 'automatorwp_perplexity_conv_' . md5( $id ) );
}

// ─── RESPONSE EXTRACTION ──────────────────────────────────────────────────

/**
 * Extract text and search results from a Perplexity API response array.
 *
 * Builds a human-readable list from the search_results field (title + URL),
 * which replaced the deprecated citations field in May 2025.
 *
 * @since  1.0.0
 *
 * @param array $response
 *
 * @return array { text: string, search_results: string }
 */
function automatorwp_perplexity_extract_response( $response ) {
    $text           = isset( $response['choices'][0]['message']['content'] ) ? $response['choices'][0]['message']['content'] : '';
    $search_results = '';

    if ( isset( $response['search_results'] ) && is_array( $response['search_results'] ) ) {
        $lines = array();
        foreach ( $response['search_results'] as $result ) {
            $title = isset( $result['title'] ) ? $result['title'] : '';
            $url   = isset( $result['url'] )   ? $result['url']   : '';
            if ( ! empty( $url ) ) {
                $lines[] = $title ? $title . ' — ' . $url : $url;
            }
        }
        $search_results = implode( "\n", $lines );
    }

    return array( 'text' => $text, 'search_results' => $search_results );
}

// ─── SHARED FIELD DEFINITIONS (DRY) ───────────────────────────────────────

/**
 * Return the shared field definition for the model selector
 *
 * @since  1.0.0
 *
 * @param string $default Default model slug
 *
 * @return array
 */
function automatorwp_perplexity_field_model( $default = 'sonar' ) {
    return array( 'from' => 'model', 'default' => $default, 'fields' => array(
        'model' => array( 'name' => __( 'Model', 'automatorwp-perplexity' ), 'desc' => __( 'Perplexity model to use.', 'automatorwp-perplexity' ), 'type' => 'select', 'options' => automatorwp_perplexity_get_models(), 'default' => $default ),
    ) );
}

/**
 * Return the shared field definition for the response format selector
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_field_response_format() {
    return array( 'from' => 'response_format', 'default' => 'text', 'fields' => array(
        'response_format' => array( 'name' => __( 'Response Format', 'automatorwp-perplexity' ), 'desc' => __( 'How Perplexity should format its response.', 'automatorwp-perplexity' ), 'type' => 'select', 'options' => automatorwp_perplexity_get_response_formats(), 'default' => 'text' ),
    ) );
}

/**
 * Return the shared field definition for the max tokens input
 *
 * @since  1.0.0
 *
 * @param int $default Default token limit
 *
 * @return array
 */
function automatorwp_perplexity_field_max_tokens( $default = 1024 ) {
    return array( 'from' => 'max_tokens', 'default' => $default, 'fields' => array(
        'max_tokens' => array( 'name' => __( 'Max Tokens', 'automatorwp-perplexity' ), 'desc' => __( 'Maximum response length in tokens.', 'automatorwp-perplexity' ), 'type' => 'text', 'default' => $default ),
    ) );
}

/**
 * Return the shared field definition for the temperature input
 *
 * @since  1.0.0
 *
 * @param string $default Default temperature value
 *
 * @return array
 */
function automatorwp_perplexity_field_temperature( $default = '1' ) {
    return array( 'from' => 'temperature', 'default' => $default, 'fields' => array(
        'temperature' => array( 'name' => __( 'Temperature', 'automatorwp-perplexity' ), 'desc' => __( '0 = deterministic, 2 = very creative. Default: 1.', 'automatorwp-perplexity' ), 'type' => 'text', 'default' => $default ),
    ) );
}

/**
 * Return the shared field definition for the response tag input
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_field_response_tag() {
    return array( 'from' => 'response_tag', 'default' => '', 'fields' => array(
        'response_tag' => array( 'name' => __( 'Store Response As Tag', 'automatorwp-perplexity' ), 'desc' => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-perplexity' ), 'type' => 'text', 'default' => '' ),
    ) );
}

/**
 * Return the shared field definitions for the usage limit and period selectors
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_field_usage_limit() {
    return array( 'from' => 'usage_limit', 'default' => '0', 'fields' => array(
        'usage_limit'  => array( 'name' => __( 'Usage Limit', 'automatorwp-perplexity' ), 'desc' => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-perplexity' ), 'type' => 'text', 'default' => '0' ),
        'usage_period' => array( 'name' => __( 'Limit Period', 'automatorwp-perplexity' ), 'desc' => __( 'Period over which the limit is counted.', 'automatorwp-perplexity' ), 'type' => 'select', 'options' => array( 'day' => __( 'Per day', 'automatorwp-perplexity' ), 'week' => __( 'Per week', 'automatorwp-perplexity' ), 'month' => __( 'Per month', 'automatorwp-perplexity' ) ), 'default' => 'day' ),
    ) );
}

/**
 * Return the shared field definition for the search mode selector
 *
 * @since  1.1.0
 *
 * @return array
 */
function automatorwp_perplexity_field_search_mode() {
    return array( 'from' => 'search_mode', 'default' => '', 'fields' => array(
        'search_mode' => array(
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
    ) );
}

/**
 * Return the shared field definition for the search recency filter
 *
 * @since  1.1.0
 *
 * @return array
 */
function automatorwp_perplexity_field_search_recency_filter() {
    return array( 'from' => 'search_recency_filter', 'default' => '', 'fields' => array(
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
    ) );
}

/**
 * Return the shared field definition for the search domain filter
 *
 * @since  1.1.0
 *
 * @return array
 */
function automatorwp_perplexity_field_search_domain_filter() {
    return array( 'from' => 'search_domain_filter', 'default' => '', 'fields' => array(
        'search_domain_filter' => array(
            'name'    => __( 'Search Domain Filter', 'automatorwp-perplexity' ),
            'desc'    => __( '(Optional) Comma-separated list of domains to restrict or exclude (prefix with - to exclude, e.g. example.com, -spam.com). Supports tags.', 'automatorwp-perplexity' ),
            'type'    => 'text',
            'default' => '',
        ),
    ) );
}

/**
 * Return the shared field definition for the reasoning effort selector
 *
 * @since  1.1.0
 *
 * @return array
 */
function automatorwp_perplexity_field_reasoning_effort() {
    return array( 'from' => 'reasoning_effort', 'default' => '', 'fields' => array(
        'reasoning_effort' => array(
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
    ) );
}
