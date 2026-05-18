<?php
/**
 * Links
 *
 * @package     ShortLinksPro\Links
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Find link by slug
 *
 * @since 1.0.0
 *
 * @param string $slug The slug to search
 *
 * @return array
 */
function shortlinkspro_get_link_by_slug( $slug ) {

    global $wpdb;

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    $slug_slash = $slug . '/';

    // Query search
    $link = $wpdb->get_row( $wpdb->prepare(
        "SELECT *
        FROM {$ct_table->db->table_name} AS l
        WHERE l.slug IN (%s, %s)",
        $slug,
        $slug_slash
    ) );

    ct_reset_setup_table();

    return $link;

}

/**
 * Generate a unique link slug
 *
 * @since 1.0.0
 *
 * @param string $prefix
 * @param int $length
 *
 * @return string
 */
function shortlinkspro_generate_link_slug( $prefix = '', $length = 4 ) {

    $slug = $prefix . shortlinkspro_generate_random_string( $length );

    if( shortlinkspro_get_link_by_slug( $slug ) ) {
        return shortlinkspro_generate_link_slug( $prefix, $length );
    }

    return $slug;

}

/**
 * Generate a random string
 *
 * @since 1.0.0
 *
 * @param int $length
 *
 * @return string
 */
function shortlinkspro_generate_random_string( $length = 4 ) {

    $characters = '0123456789' . 'abcdefghijklmnopqrstuvwxyz';

    $random_string = substr( str_shuffle( str_repeat( $characters, ceil( $length / strlen( $characters ) ) ) ), 1, $length );

    return $random_string;

}

/**
 * Helper function to get a link title
 *
 * @since 1.0.0
 *
 * @param int $link_id
 *
 * @return string
 */
function shortlinkspro_get_link_title( $link_id ) {

    global $wpdb;

    $link_id = absint( $link_id );

    if( $link_id === 0 ) {
        return __( '(no title)', 'shortlinkspro' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    // Query search
    $link_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT l.title
        FROM {$ct_table->db->table_name} AS l
        WHERE l.id = %d
        LIMIT 1",
        $link_id,
    ) );

    ct_reset_setup_table();

    if( empty( $link_title ) ) {
        return __( '(no title)', 'shortlinkspro' );
    } else {
        return $link_title;
    }

}

/**
 * Helper function to get a link meta
 *
 * @since 1.0.0
 *
 * @param int       $link_id
 * @param string    $meta_key
 * @param bool      $single
 *
 * @return string
 */
function shortlinkspro_get_link_meta( $link_id, $meta_key, $single = false ) {

    global $wpdb, $ct_table;

    $link_id = absint( $link_id );

    if( $link_id === 0 ) {
        return '';
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    // Get the meta value
    $meta_value = ct_get_object_meta( $link_id, $meta_key, $single );

    ct_reset_setup_table();

    return $meta_value;

}

/**
 * Helper function to update a link meta
 *
 * @since 1.0.0
 *
 * @param int       $link_id
 * @param string    $meta_key
 * @param mixed     $meta_value
 *
 * @return int|bool
 */
function shortlinkspro_update_link_meta( $link_id, $meta_key, $meta_value ) {

    global $wpdb, $ct_table;

    $link_id = absint( $link_id );

    if( $link_id === 0 ) {
        return false;
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    // Get the meta value
    $result = ct_update_object_meta( $link_id, $meta_key, $meta_value );

    ct_reset_setup_table();

    return $result;

}

/**
 * Helper function to get a link edit link
 *
 * @since 1.0.0
 *
 * @param int $link_id
 *
 * @return string
 */
function shortlinkspro_get_link_edit_link( $link_id ) {

    $link_id = absint( $link_id );

    if( $link_id === 0 ) {
        return __( '(no title)', 'shortlinkspro' );
    }

    $link_title = shortlinkspro_get_link_title( $link_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'shortlinkspro' ), $link_title );
    $link_edit_url = ct_get_edit_link( 'shortlinkspro_links', $link_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'shortlinkspro' ), $link_edit_url, $a_title, $link_title );

}

/**
 * Helper function to get a link category
 *
 * @since 1.0.0
 *
 * @param int $link_category_id
 *
 * @return string
 */
function shortlinkspro_get_link_category_name( $link_category_id ) {

    global $wpdb;

    $link_category_id = absint( $link_category_id );

    if( $link_category_id === 0 ) {
        return __( '(no name)', 'shortlinkspro' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_link_categories' );

    // Query search
    $link_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT l.name
        FROM {$ct_table->db->table_name} AS l
        WHERE l.id = %d
        LIMIT 1",
        $link_category_id,
    ) );

    ct_reset_setup_table();

    if( empty( $link_title ) ) {
        return __( '(no title)', 'shortlinkspro' );
    } else {
        return $link_title;
    }

}

/**
 * Helper function to get a link category link
 *
 * @since 1.0.0
 *
 * @param int $link_category_id
 *
 * @return string
 */
function shortlinkspro_get_link_category_edit_link( $link_category_id ) {

    $link_category_id = absint( $link_category_id );

    if( $link_category_id === 0 ) {
        return __( '(no name)', 'shortlinkspro' );
    }

    $name = shortlinkspro_get_link_category_name( $link_category_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'shortlinkspro' ), $name );
    $edit_url = ct_get_edit_link( 'shortlinkspro_link_categories', $link_category_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'shortlinkspro' ), $edit_url, $a_title, $name );

}

/**
 * Helper function to get a link tag
 *
 * @since 1.0.0
 *
 * @param int $link_tag_id
 *
 * @return string
 */
function shortlinkspro_get_link_tag_name( $link_tag_id ) {

    global $wpdb;

    $link_tag_id = absint( $link_tag_id );

    if( $link_tag_id === 0 ) {
        return __( '(no name)', 'shortlinkspro' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_link_tags' );

    // Query search
    $link_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT l.name
        FROM {$ct_table->db->table_name} AS l
        WHERE l.id = %d
        LIMIT 1",
        $link_tag_id,
    ) );

    ct_reset_setup_table();

    if( empty( $link_title ) ) {
        return __( '(no title)', 'shortlinkspro' );
    } else {
        return $link_title;
    }

}

/**
 * Helper function to get a link tag link
 *
 * @since 1.0.0
 *
 * @param int $link_tag_id
 *
 * @return string
 */
function shortlinkspro_get_link_tag_edit_link( $link_tag_id ) {

    $link_tag_id = absint( $link_tag_id );

    if( $link_tag_id === 0 ) {
        return __( '(no name)', 'shortlinkspro' );
    }

    $name = shortlinkspro_get_link_tag_name( $link_tag_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'shortlinkspro' ), $name );
    $edit_url = ct_get_edit_link( 'shortlinkspro_link_tags', $link_tag_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'shortlinkspro' ), $edit_url, $a_title, $name );

}