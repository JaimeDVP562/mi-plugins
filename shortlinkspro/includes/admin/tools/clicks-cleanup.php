<?php
/**
 * Clicks Cleanup Tool
 *
 * @package     ShortLinksPro\Admin\Settings\Clicks_Cleanup_Tool
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * General Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function shortlinkspro_tools_clicks_cleanup_meta_boxes( $meta_boxes ) {

    $meta_boxes['clicks_cleanup_tools'] = array(
        'title' => shortlinkspro_dashicon( 'trash' ) . __( 'Clicks Cleanup', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_clicks_cleanup_tools_fields', array(
            'clicks_cleanup_30' => array(
                'name'      => __( 'Delete clicks older than 30 days', 'shortlinkspro' ),
                'desc'      => __( 'This will delete all clicks entries that are older than 30 days.', 'shortlinkspro' ),
                'type'      => 'title',
                'render_row_cb' => 'shortlinkspro_clicks_cleanup_button',
            ),
            'clicks_cleanup_90' => array(
                'name'      => __( 'Delete clicks older than 90 days', 'shortlinkspro' ),
                'desc'      => __( 'This will delete all clicks entries that are older than 90 days.', 'shortlinkspro' ),
                'type'      => 'title',
                'render_row_cb' => 'shortlinkspro_clicks_cleanup_button',
            ),
            'clicks_cleanup_all' => array(
                'name'      => __( 'Delete all clicks', 'shortlinkspro' ),
                'desc'      => __( 'This will delete all clicks entries.', 'shortlinkspro' ),
                'type'      => 'title',
                'render_row_cb' => 'shortlinkspro_clicks_cleanup_button',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_tools_general_meta_boxes', 'shortlinkspro_tools_clicks_cleanup_meta_boxes' );

/**
 * Helper function to render the clicks cleanup button
 *
 * @param $field_args
 * @param $field
 */
function shortlinkspro_clicks_cleanup_button( $field_args, $field ) {
    $id          = $field->args( 'id' );
    $desc        = $field->args( 'desc' );
    $label       = $field->args( 'name' );

    $classes = 'button shortlinkspro-clicks-cleanup-button';

    if( $id === 'clicks_cleanup_all' ) {
        $classes .= ' shortlinkspro-button-danger';
    } else {
        $classes .= ' shortlinkspro-button-danger-outline';
    }

    echo '<div class="cmb-row shortlinkspro-clicks-cleanup-row">'
        . '<a id="' . esc_attr( $id ) . '" href="#" class="' . esc_attr( $classes ) . '">' . esc_html( $label ) . '</a>'
        . cmb_tooltip_get_html( esc_html( $desc ) )
        . '<span class="shortlinkspro-tool-response shortlinkspro-clicks-cleanup-response" style="display: none;">' . esc_html( __( 'Clicks deleted successfully!', 'shortlinkspro' ) ) . '</span>'
        . '<span class="spinner"></span>'
        . '</div>';
}

/**
 * Clicks cleanup through ajax
 *
 * @since   1.0.0
 */
function shortlinkspro_ajax_clicks_cleanup() {
    global $wpdb;

    // Security check, forces to die if not security passed
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $id = sanitize_text_field( $_POST['id'] );

    // Check parameters
    if( empty( $id ) ) {
        wp_send_json_error( __( 'Invalid clicks cleanup option.', 'shortlinkspro' ) );
    }

    $days = 0;

    switch ( $id ) {
        case 'clicks_cleanup_30':
            $days = 30;
            break;
        case 'clicks_cleanup_90':
            $days = 90;
            break;
    }

    $clicks = ShortLinksPro()->db->clicks;
    $clicks_meta = ShortLinksPro()->db->clicks_meta;

    if( $days !== 0 ) {
        $date = gmdate( 'Y-m-d', strtotime( "-{$days} day", current_time( 'timestamp' ) ) );

        // Delete old clicks
        $wpdb->query( "DELETE FROM {$clicks} WHERE created_at < '{$date}'" );

    } else {
        // Delete all clicks
        $wpdb->query( "DELETE FROM {$clicks} WHERE 1=1" );
    }

    // Delete orphaned clicks metas
    $wpdb->query( "DELETE cm FROM {$clicks_meta} cm INNER JOIN {$clicks} c ON c.id = cm.id WHERE c.id IS NULL" );

    wp_send_json_success( __( 'Clicks deleted successfully!', 'shortlinkspro' ) );
}
add_action( 'wp_ajax_shortlinkspro_clicks_cleanup', 'shortlinkspro_ajax_clicks_cleanup' );