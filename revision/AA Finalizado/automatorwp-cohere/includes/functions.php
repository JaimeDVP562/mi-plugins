<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\Cohere\Functions
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── API KEY ───────────────────────────────────────────────────────────────

/**
 * Get the Cohere API key from AutomatorWP settings
 *
 * @since  1.0.0
 *
 * @return string
 */
function automatorwp_cohere_get_api_key()
{
    return automatorwp_get_option( 'automatorwp_cohere_api_key', '' );
}

// ─── API REQUEST (retry + exponential back-off) ────────────────────────────

/**
 * Make a Cohere API request.
 * Retries on 429 / 5xx with 1 s → 2 s → 4 s back-off.
 *
 * @since  1.0.0
 *
 * @param string $endpoint  API endpoint path, e.g. '/v2/chat' or '/v1/classify'
 * @param array  $body      Request body as associative array
 * @param int    $retries   Max attempts (default 3)
 *
 * @return array|WP_Error
 */
function automatorwp_cohere_api_request( $endpoint, $body, $retries = 3 )
{
    $api_key = automatorwp_cohere_get_api_key();
    if ( empty( $api_key ) ) {
        return new WP_Error( 'missing_api_key', __( 'Cohere API key is missing. Please configure it in AutomatorWP settings.', 'automatorwp-cohere' ) );
    }

    $url             = 'https://api.cohere.com' . $endpoint;
    $retryable_codes = array( 429, 500, 502, 503, 504 );
    $attempt         = 0;
    $last_error      = null;

    while ( $attempt < $retries ) {
        if ( $attempt > 0 ) sleep( (int) pow( 2, $attempt - 1 ) );

        $http = wp_remote_post( $url, array(
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

        if ( $code === 200 || $code === 201 ) return $decoded;

        $msg = isset( $decoded['message'] ) ? $decoded['message']
             : sprintf( __( 'HTTP %d error from Cohere API.', 'automatorwp-cohere' ), $code );

        if ( in_array( $code, $retryable_codes, true ) ) {
            $last_error = new WP_Error( 'api_error_' . $code, $msg );
            continue;
        }

        return new WP_Error( 'api_error', $msg );
    }

    if ( $last_error instanceof WP_Error ) {
        return new WP_Error( $last_error->get_error_code(),
            sprintf( __( 'Cohere API failed after %d attempts: %s', 'automatorwp-cohere' ),
                $retries, $last_error->get_error_message() ) );
    }

    return new WP_Error( 'api_error', __( 'Cohere API request failed after multiple attempts.', 'automatorwp-cohere' ) );
}

// ─── MODELS ───────────────────────────────────────────────────────────────

/**
 * Get the list of available Cohere chat models
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_get_chat_models()
{
    return array(
        'command-a-03-2025'          => __( 'Command A (most powerful, 256k)', 'automatorwp-cohere' ),
        'command-a-reasoning-08-2025' => __( 'Command A Reasoning (extended thinking, 256k)', 'automatorwp-cohere' ),
        'command-a-vision-07-2025'   => __( 'Command A Vision (image + text, 128k)', 'automatorwp-cohere' ),
        'command-r-plus-08-2024'     => __( 'Command R+ (RAG workflows, 128k)', 'automatorwp-cohere' ),
        'command-r7b-12-2024'        => __( 'Command R7B (fast & lightweight, 128k)', 'automatorwp-cohere' ),
    );
}

/**
 * Get the list of available Cohere embed models
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_get_embed_models()
{
    return array(
        'embed-v4.0'                    => __( 'Embed v4.0 (text + images, variable dims)', 'automatorwp-cohere' ),
        'embed-english-v3.0'            => __( 'Embed English v3.0 (1024 dims)', 'automatorwp-cohere' ),
        'embed-english-light-v3.0'      => __( 'Embed English Light v3.0 (384 dims, fast)', 'automatorwp-cohere' ),
        'embed-multilingual-v3.0'       => __( 'Embed Multilingual v3.0 (1024 dims)', 'automatorwp-cohere' ),
        'embed-multilingual-light-v3.0' => __( 'Embed Multilingual Light v3.0 (384 dims, fast)', 'automatorwp-cohere' ),
    );
}

/**
 * Get the list of available Cohere rerank models
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_get_rerank_models()
{
    return array(
        'rerank-v4.0-pro'  => __( 'Rerank v4.0 Pro (multilingual, best quality)', 'automatorwp-cohere' ),
        'rerank-v4.0-fast' => __( 'Rerank v4.0 Fast (multilingual, low latency)', 'automatorwp-cohere' ),
        'rerank-v3.5'      => __( 'Rerank v3.5 (English + JSON, 4k context)', 'automatorwp-cohere' ),
    );
}

/**
 * Get the list of available embed input types
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_get_embed_input_types()
{
    return array(
        'search_document' => __( 'Search Document (index content)', 'automatorwp-cohere' ),
        'search_query'    => __( 'Search Query (query text)', 'automatorwp-cohere' ),
        'classification'  => __( 'Classification', 'automatorwp-cohere' ),
        'clustering'      => __( 'Clustering', 'automatorwp-cohere' ),
    );
}

// ─── RESPONSE EXTRACTION ──────────────────────────────────────────────────

/**
 * Extract text from a Cohere v2/chat API response
 *
 * @since  1.0.0
 *
 * @param array $response
 *
 * @return string
 */
function automatorwp_cohere_extract_chat_text( $response )
{
    if ( ! isset( $response['message']['content'] ) ) return '';
    foreach ( $response['message']['content'] as $block ) {
        if ( isset( $block['type'] ) && $block['type'] === 'text' && isset( $block['text'] ) ) {
            $text = $block['text'];
            // Remove fenced code blocks (e.g. ```json ... ```) and trim whitespace
            $text = preg_replace( '/^```(?:\\w+)?\\s*|\\s*```$/', '', $text );
            return trim( (string) $text );
        }
    }
    return '';
}

/**
 * Extract citations from a Cohere v2/chat API response
 *
 * @since  1.0.0
 *
 * @param array $response
 *
 * @return string Newline-separated list of cited texts
 */
function automatorwp_cohere_extract_chat_citations( $response )
{
    if ( ! isset( $response['message']['citations'] ) || ! is_array( $response['message']['citations'] ) ) return '';
    $lines = array();
    foreach ( $response['message']['citations'] as $citation ) {
        if ( isset( $citation['text'] ) && ! empty( $citation['text'] ) ) {
            $lines[] = $citation['text'];
        }
    }
    return implode( "\n", $lines );
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
function automatorwp_cohere_usage_option_key( $scope_key, $period )
{
    return 'automatorwp_cohere_usage_' . md5( $scope_key . '_' . $period );
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
function automatorwp_cohere_period_expiry( $period )
{
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
function automatorwp_cohere_get_usage_count( $scope_key, $period = 'day' )
{
    $data = get_option( automatorwp_cohere_usage_option_key( $scope_key, $period ), array( 'count' => 0, 'expires' => 0 ) );
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
function automatorwp_cohere_increment_usage( $scope_key, $period = 'day' )
{
    $key  = automatorwp_cohere_usage_option_key( $scope_key, $period );
    $data = get_option( $key, array( 'count' => 0, 'expires' => 0 ) );
    if ( time() > (int) $data['expires'] ) { $data['count'] = 0; $data['expires'] = automatorwp_cohere_period_expiry( $period ); }
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
function automatorwp_cohere_is_limit_reached( $scope_key, $limit, $period = 'day' )
{
    if ( (int) $limit <= 0 ) return false;
    return automatorwp_cohere_get_usage_count( $scope_key, $period ) >= (int) $limit;
}

/**
 * Check & increment usage. Returns false if limit is reached, true otherwise.
 *
 * @since  1.0.0
 *
 * @param stdClass $action
 * @param int      $user_id
 * @param array    $action_options Pre-parsed action options (must contain usage_limit and usage_period)
 *
 * @return bool
 */
function automatorwp_cohere_check_and_increment_usage( $action, $user_id, $action_options = array() )
{
    $limit  = isset( $action_options['usage_limit'] )  ? (int) $action_options['usage_limit']  : 0;
    $period = isset( $action_options['usage_period'] ) ? $action_options['usage_period']        : 'day';
    if ( $limit <= 0 ) return true;
    $scope = 'user_' . $user_id . '_action_' . $action->id;
    if ( automatorwp_cohere_is_limit_reached( $scope, $limit, $period ) ) {
        return false;
    }
    automatorwp_cohere_increment_usage( $scope, $period );
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
function automatorwp_cohere_get_conversation_history( $id )
{
    $data = get_option( 'automatorwp_cohere_conv_' . md5( $id ), array() );
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
function automatorwp_cohere_save_conversation_history( $id, $history )
{
    update_option( 'automatorwp_cohere_conv_' . md5( $id ), $history, false );
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
function automatorwp_cohere_clear_conversation_history( $id )
{
    delete_option( 'automatorwp_cohere_conv_' . md5( $id ) );
}

// ─── SHARED FIELD DEFINITIONS (DRY) ───────────────────────────────────────

/**
 * Return the shared field definition for the chat model selector
 *
 * @since  1.0.0
 *
 * @param string $default Default model slug
 *
 * @return array
 */
function automatorwp_cohere_field_chat_model( $default = 'command-a-03-2025' )
{
    return array( 'from' => 'model', 'default' => $default, 'fields' => array(
        'model' => array(
            'name'    => __( 'Model', 'automatorwp-cohere' ),
            'desc'    => __( 'Cohere Command model to use.', 'automatorwp-cohere' ),
            'type'    => 'select',
            'options' => automatorwp_cohere_get_chat_models(),
            'default' => $default,
        ),
    ) );
}

/**
 * Return the shared field definition for the embed model selector
 *
 * @since  1.0.0
 *
 * @param string $default Default model slug
 *
 * @return array
 */
function automatorwp_cohere_field_embed_model( $default = 'embed-v4.0' )
{
    return array( 'from' => 'model', 'default' => $default, 'fields' => array(
        'model' => array(
            'name'    => __( 'Model', 'automatorwp-cohere' ),
            'desc'    => __( 'Cohere Embed model to use.', 'automatorwp-cohere' ),
            'type'    => 'select',
            'options' => automatorwp_cohere_get_embed_models(),
            'default' => $default,
        ),
    ) );
}

/**
 * Return the shared field definition for the rerank model selector
 *
 * @since  1.0.0
 *
 * @param string $default Default model slug
 *
 * @return array
 */
function automatorwp_cohere_field_rerank_model( $default = 'rerank-v4.0-pro' )
{
    return array( 'from' => 'model', 'default' => $default, 'fields' => array(
        'model' => array(
            'name'    => __( 'Model', 'automatorwp-cohere' ),
            'desc'    => __( 'Cohere Rerank model to use.', 'automatorwp-cohere' ),
            'type'    => 'select',
            'options' => automatorwp_cohere_get_rerank_models(),
            'default' => $default,
        ),
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
function automatorwp_cohere_field_max_tokens( $default = 1024 )
{
    return array( 'from' => 'max_tokens', 'default' => $default, 'fields' => array(
        'max_tokens' => array(
            'name'    => __( 'Max Tokens', 'automatorwp-cohere' ),
            'desc'    => __( 'Maximum response length in tokens.', 'automatorwp-cohere' ),
            'type'    => 'text',
            'default' => $default,
        ),
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
function automatorwp_cohere_field_temperature( $default = '0.3' )
{
    return array( 'from' => 'temperature', 'default' => $default, 'fields' => array(
        'temperature' => array(
            'name'    => __( 'Temperature', 'automatorwp-cohere' ),
            'desc'    => __( '0 = deterministic, 1 = creative. Default: 0.3.', 'automatorwp-cohere' ),
            'type'    => 'text',
            'default' => $default,
        ),
    ) );
}

/**
 * Return the shared field definition for the response tag input
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_field_response_tag()
{
    return array( 'from' => 'response_tag', 'default' => '', 'fields' => array(
        'response_tag' => array(
            'name'    => __( 'Store Response As Tag', 'automatorwp-cohere' ),
            'desc'    => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-cohere' ),
            'type'    => 'text',
            'default' => '',
        ),
    ) );
}

/**
 * Return the shared field definitions for the usage limit and period selectors
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_field_usage_limit()
{
    return array( 'from' => 'usage_limit', 'default' => '0', 'fields' => array(
        'usage_limit'  => array(
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
    ) );
}

/**
 * Truncate long option values for the admin preview button to avoid overflowing labels.
 * Applied only for Cohere integration items in edit context.
 *
 * @since 1.0.1
 */
function automatorwp_cohere_option_preview_truncate( $value, $object, $item_type, $option, $context )
{
    if ( $context !== 'edit' ) {
        return $value;
    }

    if ( empty( $object->type ) || strpos( $object->type, 'cohere_' ) !== 0 ) {
        return $value;
    }

    $truncate_opts = array( 'text', 'prompt', 'query', 'documents', 'categories', 'additional_command', 'context', 'system_message' );
    if ( ! in_array( $option, $truncate_opts, true ) ) {
        return $value;
    }

    if ( is_array( $value ) ) {
        $value = implode( ', ', $value );
    }

    // Strip HTML and collapse whitespace
    $value = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $value ) ) );

    if ( $value === '' ) {
        // Fallback to option default if available
        $type_args = automatorwp_automation_item_type_args( $object, $item_type );
        if ( isset( $type_args['options'][ $option ] ) && isset( $type_args['options'][ $option ]['default'] ) ) {
            $value = $type_args['options'][ $option ]['default'];
        }
    }

    $max = 40;
    if ( mb_strlen( $value ) > $max ) {
        $value = mb_substr( $value, 0, $max - 3 ) . '...';
    }

    return $value;
}
add_filter( 'automatorwp_get_automation_item_option_replacement', 'automatorwp_cohere_option_preview_truncate', 10, 5 );

// ─── ACTION TAGS ──────────────────────────────────────────────────────────────

/**
 * In-memory store for tag values set during a single action execution.
 * Keyed by action ID → tag name → value.
 */
global $automatorwp_cohere_pending_tags;
$automatorwp_cohere_pending_tags = array();

/**
 * Store a tag value for the current action execution.
 * The value is persisted to log meta via automatorwp_cohere_action_log_meta().
 *
 * Defined here (guarded) so every action file can call it safely.
 * The perplexity plugin may define its own copy; the guard prevents conflicts.
 *
 * @param int    $action_id
 * @param string $tag_name
 * @param mixed  $value
 */
if ( ! function_exists( 'automatorwp_update_action_tag' ) ) {
    function automatorwp_update_action_tag( $action_id, $tag_name, $value )
    {
        global $automatorwp_cohere_pending_tags;
        $automatorwp_cohere_pending_tags[ (int) $action_id ][ $tag_name ] = $value;
    }
}

/**
 * Flush pending Cohere tags into the log meta array before it is saved.
 * Runs at priority 5, before each action's own log_meta (priority 10).
 */
function automatorwp_cohere_action_log_meta( $log_meta, $action, $user_id, $action_options, $automation )
{
    if ( strpos( (string) $action->type, 'cohere_' ) !== 0 ) {
        return $log_meta;
    }
    global $automatorwp_cohere_pending_tags;
    $action_id = (int) $action->id;
    if ( ! empty( $automatorwp_cohere_pending_tags[ $action_id ] ) ) {
        foreach ( $automatorwp_cohere_pending_tags[ $action_id ] as $tag => $value ) {
            $log_meta[ $tag ] = $value;
        }
    }
    return $log_meta;
}
add_filter( 'automatorwp_user_completed_action_log_meta', 'automatorwp_cohere_action_log_meta', 5, 5 );

/**
 * Return stored Cohere tag values when AutomatorWP resolves action tags.
 * Reads from the log meta written by automatorwp_cohere_action_log_meta().
 */
function automatorwp_cohere_get_action_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log )
{
    if ( strpos( (string) $action->type, 'cohere_' ) !== 0 ) {
        return $replacement;
    }
    if ( empty( $log->id ) ) {
        return $replacement;
    }
    $value = automatorwp_get_log_meta( $log->id, $tag_name, true );
    if ( $value !== '' && $value !== false ) {
        $replacement = $value;
    }
    return $replacement;
}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_cohere_get_action_tag_replacement', 10, 6 );
