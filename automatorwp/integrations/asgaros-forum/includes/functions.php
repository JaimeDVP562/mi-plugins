<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Asgaros_Forum\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Options callback for select2 fields assigned to forums
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_asgaros_forum_options_cb_forum( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any forum', 'automatorwp-asgaros-forum' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $forum_id ) {

            // Skip option none
            if( $forum_id === $none_value ) {
                continue;
            }

            $options[$forum_id] = automatorwp_asgaros_forum_get_forum_title( $forum_id );
        }
    }

    return $options;

}

/**
 * Get forums
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_asgaros_forum_get_forums() {

    global $wpdb;
    $forums = array();
    
    $results = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}forum_forums" );
    
    foreach ( $results as $forum ){   

        $forums[] = array(
            'id'    => $forum->id,
            'name'  => $forum->name,
        );         

    }

    return $forums;

}

/**
 * Get the forum title
 *
 * @since 1.0.0
 *
 * @param int $forum_id
 *
 * @return string|null
 */
function automatorwp_asgaros_forum_get_forum_title( $forum_id ) {

    global $wpdb;

    // Empty title if no ID provided
    if( absint( $forum_id ) === 0 ) {
        return '';
    }

    $forum_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}forum_forums WHERE id=%d",
            $forum_id ) );

    return $forum_name;    

}

/**
 * Options callback for select2 fields assigned to topic
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_asgaros_forum_options_cb_topic( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any topic', 'automatorwp-asgaros-forum' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $topic_id ) {

            // Skip option none
            if( $topic_id === $none_value ) {
                continue;
            }

            $options[$topic_id] = automatorwp_asgaros_forum_get_topic_title( $topic_id );
        }
    }

    return $options;

}

/**
 * Get topics
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_asgaros_forum_get_topics( ) {

    global $wpdb;
    $topics = array();

    $results = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}forum_topics" );
    
    foreach ( $results as $topic ){   

        $topics[] = array(
            'id'    => $topic->id,
            'name'  => $topic->name,
        );         

    }

    return $topics;

}

/**
 * Get the forum title
 *
 * @since 1.0.0
 *
 * @param int $forum_id
 *
 * @return string|null
 */
function automatorwp_asgaros_forum_get_topic_title( $topic_id ) {

    global $wpdb;

    // Empty title if no ID provided
    if( absint( $topic_id ) === 0 ) {
        return '';
    }

    $topic_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}forum_topics WHERE id=%d",
            $topic_id ) );

    return $topic_name;    

}

/**
 * Get forum by topic
 *
 * @since 1.0.0
 *
 * @return int|false
 */
function automatorwp_asgaros_forum_get_forum_by_topic( $topic_id ) {

    global $wpdb;
    // Empty if no ID provided
    if( absint( $topic_id ) === 0 ) {
        return '';
    }

    $forum_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT parent_id FROM {$wpdb->prefix}forum_topics WHERE id=%d",
            $topic_id ) );

    return $forum_id;

}

/**
 * Get topic by post
 *
 * @since 1.0.0
 *
 * @return int|false
 */
function automatorwp_asgaros_forum_get_topic_and_forum_by_post( $post_id ) {

    global $wpdb;
    // Empty if no ID provided
    if( absint( $post_id ) === 0 ) {
        return '';
    }

    $data_post = array();

    $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT parent_id, forum_id FROM {$wpdb->prefix}forum_posts WHERE id=%d",
            $post_id ) );

    foreach ( $results as $result ){   

        $data_post[] = array(
            'topic_id'  => $result->parent_id,
            'forum_id'  => $result->forum_id,
        );         

    }

    return $data_post;

}

/**
 * Get post author
 *
 * @since 1.0.0
 *
 * @return int|false
 */
function automatorwp_asgaros_forum_get_post_author( $post_id ) {

    global $wpdb;
    // Empty if no ID provided
    if( absint( $post_id ) === 0 ) {
        return '';
    }

    $data_post = array();

    $author_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}forum_posts WHERE id=%d",
            $post_id ) );

    return $author_id;

}