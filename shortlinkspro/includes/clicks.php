<?php
/**
 * Clicks
 *
 * @package     ShortLinksPro\Clicks
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Reset link clicks
 *
 * @since 1.0.0
 *
 * @param int $link_id The link id
 */
function shortlinkspro_reset_link_clicks( $link_id ) {

    global $wpdb;

    $link_id = absint( $link_id );

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    // Query search
    $wpdb->query( $wpdb->prepare(
        "DELETE
        FROM {$ct_table->db->table_name} AS c
        WHERE c.link_id = %d",
        $link_id,
    ) );

    ct_reset_setup_table();

}

/**
 * Get link clicks count
 *
 * @since 1.0.0
 *
 * @param int $link_id The link id
 *
 * @return int
 */
function shortlinkspro_get_link_clicks( $link_id ) {

    global $wpdb;

    $link_id = absint( $link_id );

    // Check for execution cache
    $cache = shortlinkspro_get_cache( 'link_clicks', array(), false );

    if( isset( $cache[$link_id] ) ) {
        return $cache[$link_id];
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    // Query search
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*)
        FROM {$ct_table->db->table_name} AS c
        WHERE c.link_id = %d",
        $link_id,
    ) );

    ct_reset_setup_table();

    $cache[$link_id] = $count;
    shortlinkspro_set_cache( 'link_clicks', $cache, false );

    return absint( $count );

}

/**
 * Get link clicks count
 *
 * @since 1.0.0
 *
 * @param int $link_id The link id
 *
 * @return int
 */
function shortlinkspro_get_link_unique_clicks( $link_id ) {

    global $wpdb;

    $link_id = absint( $link_id );

    // Check for execution cache
    $cache = shortlinkspro_get_cache( 'link_unique_clicks', array(), false );

    if( isset( $cache[$link_id] ) ) {
        return $cache[$link_id];
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    // Query search
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*)
        FROM {$ct_table->db->table_name} AS c
        WHERE c.link_id = %d
        AND first_click = 1",
        $link_id,
    ) );

    ct_reset_setup_table();

    $cache[$link_id] = $count;
    shortlinkspro_set_cache( 'link_unique_clicks', $cache, false );

    return absint( $count );

}

/**
 * Helper function to get a click meta
 *
 * @since 1.0.0
 *
 * @param int       $click_id
 * @param string    $meta_key
 * @param bool      $single
 *
 * @return string
 */
function shortlinkspro_get_click_meta( $click_id, $meta_key, $single = false ) {

    global $wpdb, $ct_table;

    $click_id = absint( $click_id );

    if( $click_id === 0 ) {
        return '';
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    // Get the meta value
    $meta_value = ct_get_object_meta( $click_id, $meta_key, $single );

    ct_reset_setup_table();

    return $meta_value;

}

/**
 * Helper function to update a click meta
 *
 * @since 1.0.0
 *
 * @param int       $click_id
 * @param string    $meta_key
 * @param mixed     $meta_value
 *
 * @return int|bool
 */
function shortlinkspro_update_click_meta( $click_id, $meta_key, $meta_value ) {

    global $wpdb, $ct_table;

    $click_id = absint( $click_id );

    if( $click_id === 0 ) {
        return false;
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    // Get the meta value
    $result = ct_update_object_meta( $click_id, $meta_key, $meta_value );

    ct_reset_setup_table();

    return $result;

}