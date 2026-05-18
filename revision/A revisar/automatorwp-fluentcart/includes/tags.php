<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\FluentCart\Tags
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_fluentcart_get_create_coupon_response_tags() {
    return array(
        'coupon_code' => array(
            'label'   => __( 'Created coupon code', 'automatorwp-fluentcart' ),
            'type'    => 'text',
            'preview' => 'WELCOME10',
        ),
    );
}

function automatorwp_fluentcart_get_add_order_note_response_tags() {
    return array(
        'order_id' => array(
            'label'   => __( 'Order ID the note was added to', 'automatorwp-fluentcart' ),
            'type'    => 'integer',
            'preview' => '123',
        ),
    );
}

function automatorwp_fluentcart_get_cancel_subscription_response_tags() {
    return array(
        'subscription_id' => array(
            'label'   => __( 'Cancelled subscription ID', 'automatorwp-fluentcart' ),
            'type'    => 'integer',
            'preview' => '7',
        ),
    );
}

/**
 * Replace action response tags for all FluentCart actions.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_get_action_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {
    $action_args = automatorwp_get_action( $action->type );
    if ( empty( $action_args ) || $action_args['integration'] !== 'fluentcart' ) {
        return $replacement;
    }
    switch ( $tag_name ) {
        case 'coupon_code':
            if ( $action->type === 'fluentcart_create_coupon' ) {
                $replacement = automatorwp_get_log_meta( $log->id, 'coupon_code', true );
            }
            break;
        case 'order_id':
            if ( $action->type === 'fluentcart_add_order_note' ) {
                $replacement = automatorwp_get_log_meta( $log->id, 'order_id', true );
            }
            break;
        case 'subscription_id':
            if ( $action->type === 'fluentcart_cancel_subscription' ) {
                $replacement = automatorwp_get_log_meta( $log->id, 'subscription_id', true );
            }
            break;
    }
    return $replacement;
}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_fluentcart_get_action_tag_replacement', 10, 6 );