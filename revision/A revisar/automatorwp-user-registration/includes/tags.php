<?php
/**
 * Tags
 *
 * @package     AutomatorWP\User_Registration\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;



/**
 * Custom tags
 *
 * @since 1.0.0
 *
 * @param array $tags The global tags
 *
 * @return array
 */
function automatorwp_user_registration_get_tags( $tags ) {

    foreach( automatorwp_user_registration_get_registration_fields() as $field ) {

        $tags['user_registration']['tags']['user_registration' . $field] = array(
            'label'     => automatorwp_user_registration_get_registration_field_label( $field ),
            'type'      => 'text',
            'preview'   => automatorwp_user_registration_get_registration_field_preview( $field ),
        );

    }

    return $tags;

}
add_filter( 'automatorwp_get_tags', 'automatorwp_user_registration_get_tags' );



/**
 * Tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_user_registration_get_webhook_tags() {

    return array(
        'user_login' => array(
            'label'     => __( 'user_name', 'automatorwp-user-registration' ),
            'type'      => 'text',
            'preview'   => 'Name of the user',
        ),
        'user_email' => array(
            'label'     => __( 'user_email', 'automatorwp-user-registration' ),
            'type'      => 'email',
            'preview'   => 'Email of the user',
        ),
        'first_name' => array(
            'label'=> __( 'first_name', 'automatorwp-user-registration'),
            'type'=> 'first_name',
            'preview' => 'First name of user',
        ),
        'last_name' => array(
            'label'     => __( 'last_name', 'automatorwp-user-registration'),
            'type'      => 'text',
            'preview'  => 'Last name of user',
        ),
        'nickname' => array(
            'label'     => __( 'nickname', 'automatorwp-user-registration'),
            'type'      => 'text',
            'preview'  => 'Nickname of user',
        ),
        'display_name' => array(
            'label'     => __( 'display_name', 'automatorwp-user-registration'),
            'type'      => 'text',
            'preview'  => 'Display name of user',
        ),
        'user_url' => array(
            'label'     => __( 'user_url', 'automatorwp-user-registration'),
            'type'      => 'url',
            'preview'  => 'Website URL of user',
        ),
        'description' => array(
            'label'     => __( 'description', 'automatorwp-user-registration'),
            'type'      => 'text',
            'preview'  => 'Bio of user',
        ),
        'user_profile' => array(
            'label'=> __( 'perfil',  'automatorwp-user-registration'),
            'type'      => 'text',
            'preview'  => 'Bio of user', 
        ),
 
    );

}



/**
 * Filter tags displayed on the tag selector
 *
 * @since 1.0.0
 *
 * @param array     $tags       The tags
 * @param stdClass  $automation The automation object
 * @param stdClass  $object     The trigger/action object
 * @param string    $item_type  The item type (trigger|action)
 *
 * @return array
 */
function automatorwp_user_registration_tags_selector_html_tags( $tags, $automation, $object, $item_type ) {

    // Remove tags on anonymous user action
    if( $automation->type === 'anonymous' && $object->type === 'automatorwp_anonymous_user' ) {
        if( isset( $tags['user_registration'] ) ) {
            unset( $tags['user_registration'] );
        }
    }

    return $tags;

}
add_filter( 'automatorwp_tags_selector_html_tags', 'automatorwp_user_registration_tags_selector_html_tags', 10, 4 );

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
function automatorwp_user_registration_get_trigger_registration_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {


    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'user_registration' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'user_login':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_login', true );
            break;
        case 'user_email':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_email', true );
            break;
        case 'first_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'first_name', true );
            break;
        case 'last_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'last_name', true );
            break;
        case 'nickname':
            $replacement = automatorwp_get_log_meta( $log->id, 'nickname', true );
            break;
        case 'display_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'display_name', true );
            break;
        case 'user_url':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_url', true );
        case 'description':
            $replacement = automatorwp_get_log_meta( $log->id, 'description', true );
        case 'user_profile':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_profile', true );
        
            
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_user_registration_get_trigger_registration_tag_replacement', 10, 6 );