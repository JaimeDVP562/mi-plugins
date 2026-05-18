<?php
/**
 * Notifications
 *
 * @package     GamiPress\Notifications\Custom_Tables\Notifications
 * @since       1.6.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom Table Labels
 *
 * @since 1.6.0
 *
 * @return array
 */
function gamipress_notifications_labels() {

    return array(
        'singular' => __( 'Notification', 'gamipress-notifications' ),
        'plural' => __( 'Notifications', 'gamipress-notifications' ),
        'labels' => array(
            'not_found' => __( 'This user has no notifications', 'gamipress-notifications' ),
            'list_menu_title' => __( 'Notifications', 'gamipress-notifications' ),
        ),
    );

}
add_filter( 'ct_gamipress_notifications_labels', 'gamipress_notifications_labels' );

/**
 * Parse query args for notifications
 *
 * @since  1.6.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function gamipress_notifications_notifications_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'gamipress_notifications' )
        return $where;

    $table_name = $ct_table->db->table_name;

    // Shorthand
    $qv = $ct_query->query_vars;

    // User ID
    $where .= gamipress_custom_table_where( $qv, 'user_id', 'user_id', 'integer' );

    // User Earning ID
    $where .= gamipress_custom_table_where( $qv, 'user_earning_id', 'user_earning_id', 'integer' );

    // Read status
    $where .= gamipress_custom_table_where( $qv, 'read', 'read', 'integer' );

    // Date
    if( isset( $qv['date'] ) && ! empty( $qv['date'] ) ) {
        $where .= " AND {$table_name}.date = '" . esc_sql( $qv['date'] ) . "'";
    }

    // Before date
    if( isset( $qv['before'] ) && ! empty( $qv['before'] ) ) {
        $where .= " AND {$table_name}.timestamp < '" . esc_sql( $qv['before'] ) . "'";
    }

    // After date
    if( isset( $qv['after'] ) && ! empty( $qv['after'] ) ) {
        $where .= " AND {$table_name}.timestamp > '" . esc_sql( $qv['after'] ) . "'";
    }

    return $where;
}
add_filter( 'ct_query_where', 'gamipress_notifications_notifications_query_where', 10, 2 );