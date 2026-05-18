<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Integrations\FluentCart\Tags
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the create coupon action response tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_get_create_coupon_response_tags() {

    return array(
        'coupon_code' => array(
            'label'   => __( 'Created coupon code', 'automatorwp-fluentcart' ),
            'type'    => 'text',
            'preview' => 'WELCOME10',
        ),
    );

}

/**
 * Get the add order note action response tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_get_add_order_note_response_tags() {

    return array(
        'result' => array(
            'label'   => __( 'Result', 'automatorwp-fluentcart' ),
            'type'    => 'text',
            'preview' => 'Note added successfully.',
        ),
    );

}

/**
 * Get the cancel subscription action response tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_get_cancel_subscription_response_tags() {

    return array(
        'result' => array(
            'label'   => __( 'Result', 'automatorwp-fluentcart' ),
            'type'    => 'text',
            'preview' => 'Subscription cancelled successfully.',
        ),
    );

}