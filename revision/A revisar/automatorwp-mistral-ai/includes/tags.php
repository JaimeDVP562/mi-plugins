<?php
/**
 * Tags
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function automatorwp_mistral_get_actions_response_tags() {

    return array(
        'response' => array(
            'label' => __( 'Response', 'automatorwp-mistral-ai' ),
            'type'  => 'text',
        ),
    );
}

function automatorwp_mistral_get_action_response_tag_replacement( $replacement, $tag_name, $action, $user_id, $action_args, $log ) {

    // Align integration slug with the plugin registration
    if ( empty( $action_args['integration'] ) || $action_args['integration'] !== 'mistral_ai' ) {
        return $replacement;
    }

    if ( $tag_name === 'response' ) {
        $replacement = automatorwp_get_log_meta( $log->id, 'response', true );
    }

    return $replacement;
}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_mistral_get_action_response_tag_replacement', 10, 6 );
