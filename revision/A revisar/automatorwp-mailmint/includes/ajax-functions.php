<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\MailMint\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Return Mail Mint tags as a JSON list for Select2 fields
 *
 * @since 1.0.0
 */
function automatorwp_mailmint_ajax_get_tags()
{
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'automatorwp-mailmint' ) ) );
    }

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

    $like = $wpdb->esc_like( $search );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, title FROM {$wpdb->prefix}mint_contact_groups
         WHERE type = 'tags' AND title LIKE %s
         ORDER BY title ASC LIMIT 50",
        '%' . $like . '%'
    ) );

    $results = array();
    foreach ( $rows as $row ) {
        $results[] = array(
            'id'   => (int) $row->id,
            'text' => esc_html( $row->title ),
        );
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_automatorwp_mailmint_get_tags', 'automatorwp_mailmint_ajax_get_tags' );

/**
 * Return Mail Mint lists as a JSON list for Select2 fields
 *
 * @since 1.0.0
 */
function automatorwp_mailmint_ajax_get_lists()
{
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'automatorwp-mailmint' ) ) );
    }

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    $search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

    $like = $wpdb->esc_like( $search );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, title FROM {$wpdb->prefix}mint_contact_groups
         WHERE type = 'lists' AND title LIKE %s
         ORDER BY title ASC LIMIT 50",
        '%' . $like . '%'
    ) );

    $results = array();
    foreach ( $rows as $row ) {
        $results[] = array(
            'id'   => (int) $row->id,
            'text' => esc_html( $row->title ),
        );
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_automatorwp_mailmint_get_lists', 'automatorwp_mailmint_ajax_get_lists' );
