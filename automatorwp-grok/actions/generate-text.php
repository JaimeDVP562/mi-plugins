<?php
/**
 * Action: Generate text from prompt.
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AUTOMATORWP_GROK_ACTION_GENERATE_TEXT', 'automatorwp_grok_generate_text' );

/**
 * Register action.
 *
 * @return void
 */
function automatorwp_grok_register_generate_text_action() {

    if ( ! function_exists( 'automatorwp_register_action' ) ) {
        return;
    }

    automatorwp_register_action(
        AUTOMATORWP_GROK_ACTION_GENERATE_TEXT,
        array(
            'integration'   => 'grok',
            'label'         => __( 'Generate text from prompt', 'automatorwp-grok' ),
            'select_option' => __( 'Generate text from prompt', 'automatorwp-grok' ),
            'edit_label'    => __( 'Generate text from prompt', 'automatorwp-grok' ),
            'log_label'     => __( 'Generate text from prompt', 'automatorwp-grok' ),

            // ✅ Action tags (AutomatorWP 5.5.5 compatible approach).
            // This makes {response} appear in the tags selector for this action.
            'tags'          => array(
                'response' => array(
                    'label' => __( 'Response', 'automatorwp-grok' ),
                    'type'  => 'text',
                ),
            ),

            'options'       => array(
                'prompt' => array(
                    'from'        => 'text',
                    'name'        => __( 'Prompt', 'automatorwp-grok' ),
                    'type'        => 'textarea',
                    'placeholder' => __( 'Write your prompt...', 'automatorwp-grok' ),
                    'default'     => '',
                    'required'    => true,
                ),
                'model'  => array(
                    'from'        => 'text',
                    'name'        => __( 'Model', 'automatorwp-grok' ),
                    'type'        => 'text',
                    'placeholder' => 'grok-beta',
                    'default'     => 'grok-beta',
                    'required'    => false,
                ),
            ),

            'callback'      => 'automatorwp_grok_action_generate_text_callback',
        )
    );
}
add_action( 'init', 'automatorwp_grok_register_generate_text_action', 30 );

/**
 * Execute action.
 *
 * We store the generated text so the {response} tag can retrieve it.
 *
 * @param array $action Action.
 * @param int   $user_id User ID.
 * @param array $action_options Options.
 * @param int   $automation_id Automation ID (optional).
 * @param int   $trigger_log_id Trigger log ID (optional).
 * @param int   $action_log_id Action log ID (optional).
 * @return void
 */
function automatorwp_grok_action_generate_text_callback( $action, $user_id, $action_options, $automation_id = 0, $trigger_log_id = 0, $action_log_id = 0 ) {

    $prompt = isset( $action_options['prompt'] ) ? $action_options['prompt'] : '';
    $model  = isset( $action_options['model'] ) ? $action_options['model'] : 'grok-beta';

    $result = automatorwp_grok_generate_text( $prompt, $model );

    $response_text = '';
    if ( isset( $result['response'] ) && is_string( $result['response'] ) ) {
        $response_text = trim( $result['response'] );
    }

    /**
     * AutomatorWP 5.x usually resolves action tags from action log meta.
     * We'll store it there when possible.
     */
    if ( $action_log_id && function_exists( 'automatorwp_update_action_log_meta' ) ) {
        automatorwp_update_action_log_meta( $action_log_id, 'response', $response_text );
        return;
    }

    // Compatibility fallback.
    if ( $action_log_id && function_exists( 'automatorwp_actions_update_action_log_meta' ) ) {
        automatorwp_actions_update_action_log_meta( $action_log_id, 'response', $response_text );
        return;
    }

    // Last resort fallback (not ideal, but prevents losing the value).
    if ( $user_id ) {
        update_user_meta( $user_id, '_automatorwp_grok_last_response', $response_text );
    }
}
