<?php
/**
 * Tags
 *
 * @package     AutomatorWP\ShortLinksPro\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Link tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_shortlinkspro_get_link_tags() {

    return array(
         'link_id' => array(
            'label' => __('Link ID', 'automatorwp'),
            'type' => 'integer',
            'preview' => '123'
        ),
        'link_title' => array(
            'label' => __('Link title', 'automatorwp'),
            'type' => 'text',
            'preview' => 'The link title'
        ),
        'link_url' => array(
            'label' => __('Link url', 'automatorwp'),
            'type' => 'text',
            'preview' => 'The link url'
        ),
        'link_slug' => array(
            'label' => __('Link slug', 'automatorwp'),
            'type' => 'text',
            'preview' => 'The link slug'
        ),
        'redirect_type' => array(
            'label' => __('Link redirect type', 'automatorwp'),
            'type' => 'text',
            'preview' => 'The link redirect type'
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
function automatorwp_shortlinkspro_get_trigger_link_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'shortlinkspro' ) {
        return $replacement;
    }

    $link_slug = automatorwp_get_log_meta( $log->id, 'link_slug', true);
    $link = shortlinkspro_get_link_by_slug( $link_slug );
    
    switch($tag_name) {
        case 'link_id':
            $replacement = $link->id;
            break;
        case 'link_title':
            $replacement = ! empty( $link->title ) ? $link->title : '(no title)';
            break;
        case 'link_url':
            $replacement = $link->url;
            break;
        case 'link_slug':
            $replacement = $link->slug;
            break;
        case 'redirect_type':
            $replacement = $link->redirect_type;
            break;
    }
    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_shortlinkspro_get_trigger_link_tag_replacement', 10, 6 );