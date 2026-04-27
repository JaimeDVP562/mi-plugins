<?php
/**
 * Tags
 *
 * @package     AutomatorWP\FluentCart\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Order tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_get_order_tags() {

    return array(
        'order_id' => array(
            'label'     => __( 'Order ID', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => '123',
        ),
        'order_payment_method' => array(
            'label'     => __( 'Order payment method', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The order payment method',
        ),
        'order_type' => array(
            'label'     => __( 'Order type', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'subscription',
        ),
        'order_currency' => array(
            'label'     => __( 'Order currency', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'USD',
        ),
        'order_status' => array(
            'label'     => __( 'Order status', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'completed',
        ),
        'order_subtotal' => array(
            'label'     => __( 'Order subtotal', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => '100',
        ),
        'order_total' => array(
            'label'     => __( 'Order total', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => '100',
        ),
        'order_note' => array(
            'label'     => __( 'Order note', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The order note',
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
function automatorwp_fluentcart_get_trigger_order_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {


    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'fluentcart' ) {
        return $replacement;
    }

    $order_id = absint( automatorwp_get_log_meta( $log->id, 'order_id', true ) );

    $order = \FluentCart\App\Models\Order::find( $order_id );
    
    switch( $tag_name ) {
        case 'order_id':
            $replacement = $order->id;
            break;
        case 'order_payment_method':
            $replacement = $order->payment_method;
            break;
        case 'order_type':
            $replacement = $order->type;
            break;
        case 'order_currency':
            $replacement = $order->currency;
            break;
        case 'order_status':
            $replacement = $order->status;
            break;
        case 'order_subtotal':
            $replacement = number_format( $order->subtotal / 100, 2, '.', '' );
            break;
        case 'order_total':
            $replacement = number_format( $order->total_amount / 100, 2, '.', '' );
            break;
        case 'order_note':
            $replacement = $order->note;
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_fluentcart_get_trigger_order_tag_replacement', 10, 6 );
