<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\Amelia\Includes\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Amelia services
 *
 * @since 1.0.0
 * 
 * @param int $order_id
 *
 * @return array|false
 */
function automatorwp_eventin_get_order_data( $order_id ) {

    // Empty title if no ID provided
    if( absint( $order_id ) === 0 ) {
        return false;
    }

    // Get the data related to order
    $order_metas = get_post_meta( $order_id );
    $order_data = array();

    if (!empty($order_metas)) {
        foreach ($order_metas as $meta_key => $meta_value) {
            $order_data[$meta_key] = $meta_value[0];
        }
    }

    return $order_data;

}
