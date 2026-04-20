<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Eventin\Tags
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
function automatorwp_eventin_get_order_tags() {

    return array(
        'event_id' => array(
            'label'     => __( 'Event ID', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The event ID',
        ),
        'customer_first_name' => array(
            'label'     => __( 'Customer first name', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer first name',
        ),
        'customer_last_name' => array(
            'label'     => __( 'Customer last name', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer last name',
        ),
        'customer_email' => array(
            'label'     => __( 'Customer email', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The customer email',
        ),
        'order_status' => array(
            'label'     => __( 'Order status', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The order status',
        ),
        'order_total_price' => array(
            'label'     => __( 'Total price', 'automatorwp' ),
            'type'      => 'text',
            'preview'   => 'The order total price',
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
function automatorwp_eventin_get_trigger_order_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {


    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'eventin' ) {
        return $replacement;
    }
    
    $order_id = absint( automatorwp_get_log_meta( $log->id, 'order_id', true ) );

    $order_data = automatorwp_eventin_get_order_data( $order_id );

    switch( $tag_name ) {
        case 'event_id':
            $replacement = $order_data['event_id'];
            break;
        case 'customer_first_name':
            $replacement = $order_data['customer_fname'];
            break;
        case 'customer_last_name':
            $replacement = $order_data['customer_lname'];
            break;
        case 'customer_email':
            $replacement = $order_data['customer_email'];
            break;
        case 'order_status':
            $replacement = $order_data['status'];
            break;
        case 'order_total_price':
            $replacement = $order_data['total_price'];
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_eventin_get_trigger_order_tag_replacement', 10, 6 );
