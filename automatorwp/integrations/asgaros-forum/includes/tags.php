<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Asgaros-Forum\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Asgaros Forum trigger topic tags
 */
function automatorwp_asgaros_forum_get_general_tags() {
    return array(
        'forum_id' => array(
            'label'   => __( 'Forum ID', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => '123',
        ),
        'forum_name' => array(
            'label'   => __( 'Forum name', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => 'My forum',
        ),
        'topic_id' => array(
            'label'   => __( 'Topic ID', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => '123',
        ),
        'topic_title' => array(
            'label'   => __( 'Topic title', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => 'Topic title',
        ),
    );
}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_asgaros_forum_get_trigger_general_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'asgaros_forum' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'forum_id':
        case 'topic_id':
            $replacement = automatorwp_get_log_meta( $log->id, $tag_name, true );
            break;
        case 'topic_title':
            $topic_id = automatorwp_get_log_meta( $log->id, 'topic_id', true );
            $replacement =  automatorwp_asgaros_forum_get_topic_title( absint( $topic_id ) );
            break;
        case 'forum_name':
            $forum_id = automatorwp_get_log_meta( $log->id, 'forum_id', true );
            $replacement =  automatorwp_asgaros_forum_get_forum_title( absint( $forum_id ) );
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_asgaros_forum_get_trigger_general_tag_replacement', 10, 6 );

/**
 * Get Asgaros Forum trigger topic tags
 */
function automatorwp_asgaros_forum_get_topic_tags() {
    return array(
        'topic_content' => array(
            'label'   => __( 'Topic content', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => 'Topic content',
        ),
        'topic_link' => array(
            'label'   => __( 'Post Link', 'automatorwp-asgaros-forum' ),
            'type'    => 'url',
            'preview' => 'https://example.com/forum/topic/post',
        ),
    );
}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_asgaros_forum_get_trigger_topic_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'asgaros_forum' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'topic_content':
        case 'topic_link':
            $replacement = automatorwp_get_log_meta( $log->id, $tag_name, true );
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_asgaros_forum_get_trigger_topic_tag_replacement', 10, 6 );

/**
 * Get Asgaros Forum trigger reply tags
 */
function automatorwp_asgaros_forum_get_reply_tags() {
    return array(
        'reply_content' => array(
            'label'   => __( 'Reply content', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => 'Reply content',
        ),
        'reply_link' => array(
            'label'   => __( 'Reply Link', 'automatorwp-asgaros-forum' ),
            'type'    => 'url',
            'preview' => 'https://example.com/forum/topic/post',
        ),
    );
}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_asgaros_forum_get_trigger_reply_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'asgaros_forum' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'reply_content':
        case 'reply_link':
            $replacement = automatorwp_get_log_meta( $log->id, $tag_name, true );
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_asgaros_forum_get_trigger_reply_tag_replacement', 10, 6 );

/**
 * Get Asgaros Forum trigger reaction tags
 */
function automatorwp_asgaros_forum_get_reaction_tags() {
    return array(
        'reaction' => array(
            'label'   => __( 'Reaction', 'automatorwp-asgaros-forum' ),
            'type'    => 'text',
            'preview' => 'Post reaction',
        ),
    );
}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_asgaros_forum_get_trigger_reaction_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'asgaros_forum' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'reaction':
            $replacement = automatorwp_get_log_meta( $log->id, $tag_name, true );
            break;
    }

    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_asgaros_forum_get_trigger_reaction_tag_replacement', 10, 6 );