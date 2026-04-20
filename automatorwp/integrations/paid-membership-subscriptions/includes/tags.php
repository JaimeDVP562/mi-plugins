<?php

/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\Paid_Membership_Subscriptions\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Subscription tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_pms_get_subscription_tags() {

    return array(
        'subscription_plan_id' => array(
            'label'     => __( 'Subscription ID', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The subscription ID',
        ),
        'subscription_plan_name' => array(
            'label'     => __( 'Subscription name', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The subscription name',
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
function automatorwp_pms_get_trigger_subscription_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

    $trigger_args = automatorwp_get_trigger( $trigger->type );


    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'paid_membership_subscriptions' ) {
        return $replacement;
    }

    switch($tag_name) {
        case 'subscription_plan_id':
            $replacement = automatorwp_get_log_meta( $log->id, 'subscription_plan_id', true );
            break;
        case 'subscription_plan_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'subscription_plan_name', true );
            break;
    }
    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_pms_get_trigger_subscription_tag_replacement', 10, 6 );