<?php
/**
 * Ajax Functions
 *
 * @package     ShortLinksPro\Ajax_Functions
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Find link by slug through ajax
 *
 * @since   1.0.0
 */
function shortlinkspro_ajax_get_link_by_slug() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $slug = sanitize_text_field( $_POST['slug'] );

    // Check parameters
    if( empty( $slug ) ) {
        wp_send_json_error( __( 'Empty slug.', 'shortlinkspro' ) );
    }

    $link = shortlinkspro_get_link_by_slug( $slug );

    if( $link ) {
        wp_send_json_success( array(
            'message' => __( 'Link found!', 'shortlinkspro' ),
            'link' => $link,
        ) );
    } else {
        wp_send_json_error( __( 'Link not found.', 'shortlinkspro' ) );
    }
}
add_action( 'wp_ajax_shortlinkspro_get_link_by_slug', 'shortlinkspro_ajax_get_link_by_slug' );

/**
 * Clicks chart info
 *
 * @since   1.0.0
 */
function shortlinkspro_ajax_clicks_chart() {

    global $wpdb;

    // Security check, forces to die if not security passed
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $period = sanitize_text_field( $_POST['period'] );
    $period_start = sanitize_text_field( $_POST['period_start'] );
    $period_end = sanitize_text_field( $_POST['period_end'] );

    // Check parameters
    if( empty( $period ) ) {
        wp_send_json_error( __( 'No period provided.', 'shortlinkspro' ) );
    }

    if( $period === 'custom' && empty( $period_start ) && empty( $period_end ) ) {
        wp_send_json_error( __( 'No custom period provided.', 'shortlinkspro' ) );
    }

    if( $period === 'custom' ) {
        $range = array(
            'start' => gmdate( 'Y-m-d 00:00:00', strtotime( $period_start ) ),
            'end' => gmdate( 'Y-m-d 23:59:59', strtotime( $period_end ) ),
        );
    } else {
        $range = shortlinkspro_get_period_range( $period );
    }

    if( empty( $range['start'] ) && empty( $range['end'] ) ) {
        wp_send_json_error( __( 'Invalid period range.', 'shortlinkspro' ) );
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    $where = '';
    $group_by = '';
    $query_args = array();

    $group_by_index = shortlinkspro_chart_group_by( $period, $range );
    $date_format = shortlinkspro_chart_label_date_format( $period, $range );

    if( $group_by_index === 'd' ) {
        $group_by = 'YEAR(c.created_at), MONTH(c.created_at), DAY(c.created_at)';
    } else {
        $group_by = 'YEAR(c.created_at), MONTH(c.created_at)';
    }

    // Where between date range
    $where .= " c.created_at >= %s AND c.created_at <= %s";
    $query_args[] = $range['start'];
    $query_args[] = $range['end'];

    // Filter by field
    $ip = sanitize_text_field( $_POST['ip'] );
    $country = sanitize_text_field( $_POST['country'] );
    $browser = sanitize_text_field( $_POST['browser'] );
    $os = sanitize_text_field( $_POST['os'] );
    $device = sanitize_text_field( $_POST['device'] );
    $link_id = absint( $_POST['link_id'] );

    if( ! empty( $ip ) ) {
        $where .= " AND c.ip = %s";
        $query_args[] = $ip;
    }

    if( ! empty( $country ) ) {
        $where .= " AND c.country = %s";
        $query_args[] = $country;
    }

    if( ! empty( $browser ) ) {
        $where .= " AND c.browser = %s";
        $query_args[] = $browser;
    }

    if( ! empty( $os ) ) {
        $where .= " AND c.os = %s";
        $query_args[] = $os;
    }

    if( ! empty( $device ) ) {
        $where .= " AND c.device = %s";
        $query_args[] = $device;
    }

    if( $link_id !== 0 ) {
        $where .= " AND c.link_id = %s";
        $query_args[] = $link_id;
    }

    // Get clicks reports
    $clicks_count = $wpdb->get_results( $wpdb->prepare(
        "SELECT DATE(c.created_at) AS date, COUNT(c.id) AS count
        FROM {$ct_table->db->table_name} AS c
        WHERE {$where}
        GROUP BY {$group_by}",
        $query_args
    ) );

    // Parse clicks count
    $clicks = array();

    foreach( $clicks_count as $click ) {
        $date = gmdate( $date_format, strtotime( $click->date ) );

        $clicks[$date] = $click->count;
    }

    // Get unique clicks reports
    $where .= " AND c.first_click = 1";
    $unique_clicks_count = $wpdb->get_results( $wpdb->prepare(
        "SELECT DATE(c.created_at) AS date, COUNT(c.id) AS count
        FROM {$ct_table->db->table_name} AS c
        WHERE {$where}
        GROUP BY {$group_by}",
        $query_args
    ) );

    // Parse unique clicks count
    $unique_clicks = array();

    foreach( $unique_clicks_count as $unique_click ) {
        $date = gmdate( $date_format, strtotime( $unique_click->date ) );

        $unique_clicks[$date] = $unique_click->count;
    }

    // Setup chart parameters
    $labels = shortlinkspro_get_range_period( $range, $group_by_index, $date_format );
    $clicks_data = array();
    $unique_clicks_data = array();

    // Ensure that each dataset has all the required values
    foreach( $labels as $label ) {
        $clicks_data[] = ( isset( $clicks[$label] ) ? absint( $clicks[$label] ) : 0 );
        $unique_clicks_data[] = ( isset( $unique_clicks[$label] ) ? absint( $unique_clicks[$label] ) : 0 );
    }

    $data = array(
        'labels' => $labels,
        'datasets' => array(
            array(
                'label' => __( 'Clicks', 'shortlinkspro' ),
                'data' => $clicks_data,
            ),
            array(
                'label' => __( 'Unique Clicks', 'shortlinkspro' ),
                'data' => $unique_clicks_data,
            ),
        ),
    );

    ct_reset_setup_table();

    wp_send_json_success( $data );

}
add_action( 'wp_ajax_shortlinkspro_clicks_chart', 'shortlinkspro_ajax_clicks_chart' );

/**
 * Helper function to get the labels date format
 *
 * @since 1.0.0
 *
 * @param string $period
 * @param array $range
 **/
function shortlinkspro_chart_label_date_format( $period, $range ) {

    switch( $period ) {
        case 'this-week':
        case 'past-week':
            return 'Y-m-d';
        case 'this-month':
        case 'past-month':
            return 'Y-m-d';
        case 'this-year':
        case 'past-year':
            return 'F';
        case 'custom':
            // 6 months
            if( ( strtotime( $range['end'] ) - strtotime( $range['start'] ) ) < 10540800 ) {
                return 'Y-m-d';
            } else {
                return 'F';
            }
    }

}

/**
 * Helper function to meet to what should group by
 *
 * @since 1.0.0
 *
 * @param string $period
 * @param array $range
 **/
function shortlinkspro_chart_group_by( $period, $range ) {

    switch( $period ) {
        case 'this-week':
        case 'past-week':
        case 'this-month':
        case 'past-month':
            return 'd';
        case 'this-year':
        case 'past-year':
            return 'm';
        case 'custom':
            // 6 months
            if( ( strtotime( $range['end'] ) - strtotime( $range['start'] ) ) < 10540800 ) {
                return 'd';
            } else {
                return 'm';
            }
    }

}