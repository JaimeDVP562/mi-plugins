<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\MemberPress\Tags
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Member tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_memberpress_member_tags() {

    $member_tags = array();

    foreach( automatorwp_memberpress_get_member_fields() as $field ) {

        $member_tags['subaccount_' . $field] = array(
            'label'     => sprintf( __( 'Subaccount %s', 'automatorwp-memberpress'  ), automatorwp_memberpress_get_member_field_label( $field ) ),
            'type'      => 'text',
            'preview'   => automatorwp_memberpress_get_member_field_preview( $field ),
        );
    
    }

    foreach( automatorwp_memberpress_get_member_fields() as $field ) {

        $member_tags['parent_account_' . $field] = array(
            'label'     => sprintf( __( 'Parent account %s', 'automatorwp-memberpress'  ), automatorwp_memberpress_get_member_field_label( $field ) ),
            'type'      => 'text',
            'preview'   => automatorwp_memberpress_get_member_field_preview( $field ),
        );

    }

    $membership_tags = array(
        'membership_id' => array(
            'label'     => __( 'Membership ID', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The Membership ID',
        ),
        'membership_title' => array(
            'label'     => __( 'Membership title', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The Membership title',
        ),
    );

    $member_tags = array_merge( $member_tags, $membership_tags );

    /**
     * Filter member tags
     *
     * @since 1.0.0
     *
     * @param array $tags
     *
     * @return array
     */
    return apply_filters( 'automatorwp_memberpress_member_tags', $member_tags );

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
function automatorwp_memberpress_get_trigger_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Bail if no order ID attached
    if( ! $trigger_args ) {
        return $replacement;
    }

    // Bail if trigger is not from this integration
    if( $trigger_args['integration'] !== 'memberpress' ) {
        return $replacement;
    }

    $subaccount_id = (int) automatorwp_get_log_meta( $log->id, 'user_id', true );
    $parent_account_id = (int) automatorwp_get_log_meta( $log->id, 'parent_id', true );

    $subaccount = get_user_by( 'ID', $subaccount_id );
    $parent_account = get_user_by( 'ID', $parent_account_id );

    foreach( automatorwp_memberpress_get_member_fields() as $field ) {

        switch( $tag_name ) {
            case 'subaccount_' . $field:
                $replacement = $subaccount->$field;
                break;
            case 'parent_account_' . $field:
                $replacement = $parent_account->$field;
                break;
        } 

    }

    switch( $tag_name ) {
        case 'membership_id':
            $replacement = $replacement = automatorwp_get_log_meta( $log->id, 'membership_id', true );
            break;
        case 'membership_title':
            $replacement = $replacement = automatorwp_get_log_meta( $log->id, 'membership_title', true );
            break;
    } 

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_memberpress_get_trigger_tag_replacement', 10, 6 );