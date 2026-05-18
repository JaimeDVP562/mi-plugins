<?php
/**
 * Custom Tables
 *
 * @package GamiPress\Referrals\Custom_Tables
 * @since 1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register custom tables
 *
 * @since 1.2.0
 */
function gamipress_referrals_register_custom_tables() {

    // Referrals table
    ct_register_table( 'gamipress_referrals_referrals', array(
        'singular'  => 'Referral',
        'plural'    => 'Referrals',
        'labels'    => array(
            'name'          => __( 'Referrals', 'gamipress-referrals' ),
            'singular_name' => __( 'Referral', 'gamipress-referrals' ),
            'all_items'     => __( 'Referrals', 'gamipress-referrals' ),
            'add_new'       => __( 'Add New', 'gamipress-referrals' ),
            'add_new_item'  => __( 'Add New Referral', 'gamipress-referrals' ),
            'edit_item'     => __( 'Referral Details', 'gamipress-referrals' ),
            'new_item'      => __( 'New Referral', 'gamipress-referrals' ),
            'view_item'     => __( 'View Referral', 'gamipress-referrals' ),
            'search_items'  => __( 'Search Referrals', 'gamipress-referrals' ),
            'not_found'     => __( 'No referrals found', 'gamipress-referrals' ),
            'not_found_in_trash' => __( 'No referrals found in trash', 'gamipress-referrals' ),
        ),
        'show_ui'           => true,
        'show_in_menu'      => 'gamipress',
        'primary_key'       => 'referral_id',
        'version'           => 1,
        'global'            => false,
        'capability'        => gamipress_get_manager_capability(),
        'supports'          => array(),
        'views'             => array(
            'list' => array(
                'menu_title' => __( 'Referrals', 'gamipress_referrals_referrals' ),
                'parent_slug' => 'gamipress',
            ),
        ),
        'schema'            => array(
            'referral_id'       => array( 'type' => 'bigint', 'length' => 20, 'auto_increment' => true, 'primary_key' => true, 'unsigned' => true ),
            'user_id'           => array( 'type' => 'bigint', 'length' => 20, 'unsigned' => true, 'key' => true ),
            'referral_user_id'  => array( 'type' => 'bigint', 'length' => 20, 'unsigned' => true, 'key' => true ),
            'referral_ip'       => array( 'type' => 'varchar', 'length' => 50 ),
            'type'              => array( 'type' => 'varchar', 'length' => 50, 'key' => true ),
            'post_id'           => array( 'type' => 'bigint', 'length' => 20, 'unsigned' => true, 'nullable' => true ),
            'post_url'          => array( 'type' => 'text', 'nullable' => true ),
            'referrer'          => array( 'type' => 'text', 'nullable' => true ),
            'integration'       => array( 'type' => 'varchar', 'length' => 50, 'nullable' => true ),
            'date'              => array( 'type' => 'datetime', 'default' => '0000-00-00 00:00:00' ),
        ),
    ) );

}
add_action( 'ct_init', 'gamipress_referrals_register_custom_tables' );

/**
 * Helper function to get the referrals database table
 *
 * @since 1.2.0
 *
 * @return CT_Table|false
 */
function gamipress_referrals_get_table() {

    $ct_table = ct_setup_table( 'gamipress_referrals_referrals' );

    if( ! $ct_table ) {
        return false;
    }

    return $ct_table;
}

/**
 * Insert a referral into the custom table
 *
 * @since 1.2.0
 *
 * @param array $data Referral data
 *
 * @return int|false The referral ID on success, false on failure
 */
function gamipress_referrals_insert_referral( $data ) {

    $ct_table = gamipress_referrals_get_table();

    if( ! $ct_table ) {
        return false;
    }

    // Set the date if not provided
    if( ! isset( $data['date'] ) ) {
        $data['date'] = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
    }

    return $ct_table->db->insert( $data );

}

/**
 * Delete a referral from the custom table
 *
 * @since 1.2.0
 *
 * @param int $referral_id Referral ID
 *
 * @return bool
 */
function gamipress_referrals_delete_referral( $referral_id ) {

    $ct_table = gamipress_referrals_get_table();

    if( ! $ct_table ) {
        return false;
    }

    return $ct_table->db->delete( $referral_id );

}

/**
 * Query referrals from the custom table
 *
 * @since 1.2.0
 *
 * @param array $args Query arguments
 *
 * @return array|int
 */
function gamipress_referrals_query_referrals( $args = array() ) {

    global $wpdb;

    $ct_table = gamipress_referrals_get_table();

    if( ! $ct_table ) {
        return array();
    }

    $table_name = $ct_table->db->table_name;

    $defaults = array(
        'user_id'           => 0,
        'referral_user_id'  => 0,
        'type'              => '',
        'integration'       => '',
        'post_id'           => 0,
        'orderby'           => 'date',
        'order'             => 'DESC',
        'limit'             => -1,
        'offset'            => 0,
        'count'             => false,
    );

    $args = wp_parse_args( $args, $defaults );

    $where = '1=1';

    if( absint( $args['user_id'] ) > 0 ) {
        $where .= $wpdb->prepare( ' AND user_id = %d', absint( $args['user_id'] ) );
    }

    if( absint( $args['referral_user_id'] ) > 0 ) {
        $where .= $wpdb->prepare( ' AND referral_user_id = %d', absint( $args['referral_user_id'] ) );
    }

    if( ! empty( $args['type'] ) ) {
        if( is_array( $args['type'] ) ) {
            $types = implode( "','", array_map( 'sanitize_text_field', $args['type'] ) );
            $where .= " AND type IN ('{$types}')";
        } else {
            $where .= $wpdb->prepare( ' AND type = %s', sanitize_text_field( $args['type'] ) );
        }
    }

    if( ! empty( $args['integration'] ) ) {
        $where .= $wpdb->prepare( ' AND integration = %s', sanitize_text_field( $args['integration'] ) );
    }

    if( absint( $args['post_id'] ) > 0 ) {
        $where .= $wpdb->prepare( ' AND post_id = %d', absint( $args['post_id'] ) );
    }

    // Count query
    if( $args['count'] ) {
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where}" );
    }

    // Order
    $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
    $order_clause = $orderby ? "ORDER BY {$orderby}" : '';

    // Limit
    $limit_clause = '';
    if( $args['limit'] > 0 ) {
        $limit_clause = $wpdb->prepare( 'LIMIT %d', absint( $args['limit'] ) );

        if( $args['offset'] > 0 ) {
            $limit_clause .= $wpdb->prepare( ' OFFSET %d', absint( $args['offset'] ) );
        }
    }

    return $wpdb->get_results( "SELECT * FROM {$table_name} WHERE {$where} {$order_clause} {$limit_clause}" );

}

/**
 * Count referrals from the custom table
 *
 * @since 1.2.0
 *
 * @param array $args Query arguments
 *
 * @return int
 */
function gamipress_referrals_count_referrals( $args = array() ) {

    $args['count'] = true;

    return gamipress_referrals_query_referrals( $args );

}