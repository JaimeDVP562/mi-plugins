<?php

/**
 * Tags for DeepSeek.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Get actions response tags for DeepSeek.
 * * Used in the 'tags' parameter of automatorwp_register_action.
 * * @return array
 */
function automatorwp_deepseek_get_actions_response_tags()
{
    return array(
        'response' => array(
            'label' => __('DeepSeek Response', 'automatorwp-deepseek'),
            'type'  => 'text',
        ),
    );
}

/**
 * Handle the replacement of DeepSeek action tags.
 * * @param string $replacement The default replacement value.
 * @param string $tag_name    The name of the tag being replaced.
 * @param object $action      The action object.
 * @param int    $user_id     The user ID.
 * @param array  $action_args The action arguments.
 * @param object $log         The action log object.
 * * @return string
 */
function automatorwp_deepseek_get_action_response_tag_replacement($replacement, $tag_name, $action, $user_id, $action_args, $log)
{
    // Align integration slug with the plugin registration
    $integration = isset($action_args['integration']) ? $action_args['integration'] : '';

    if ($integration !== 'deepseek') {
        return $replacement;
    }

    // Replace the 'response' tag with the content stored in action log meta
    if ($tag_name === 'response') {
        $response = automatorwp_get_log_meta($log->id, 'response', true);

        if (! empty($response)) {
            // Return the response, allowing basic HTML formatting if needed
            $replacement = wp_kses_post($response);
        }
    }

    return $replacement;
}
add_filter('automatorwp_get_action_tag_replacement', 'automatorwp_deepseek_get_action_response_tag_replacement', 10, 6);
