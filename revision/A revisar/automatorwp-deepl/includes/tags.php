<?php
/**
 * Tags
 *
 * @package     AutomatorWP\DeepL\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get action response tags for DeepL actions
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_deepl_get_actions_response_tags() {

    return array(
        'response' => array(
            'label'   => __( 'DeepL response', 'automatorwp-deepl' ),
            'type'    => 'text',
            'preview' => 'DeepL response',
        ),
    );

}

/**
 * Custom action response tag replacement
 *
 * @since 1.0.0
 *
 * @param string   $replacement  The tag replacement
 * @param string   $tag_name     The tag name (without {})
 * @param stdClass $action       The action object
 * @param int      $user_id      The user ID
 * @param string   $content      The content to parse
 * @param stdClass $log          The last action log object
 *
 * @return string
 */
function automatorwp_deepl_get_action_response_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {

    $action_args = automatorwp_get_action( $action->type );

    // Skip if action is not from this integration
    if ( $action_args['integration'] !== 'deepl' ) {
        return $replacement;
    }

    switch ( $tag_name ) {
        case 'response':
            $replacement = automatorwp_get_log_meta( $log->id, 'response', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_deepl_get_action_response_tag_replacement', 10, 6 );
