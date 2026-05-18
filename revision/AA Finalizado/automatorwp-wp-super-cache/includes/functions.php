<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\WPSuperCache\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Purge all WP Super Cache caches
 *
 * @since 1.0.0
 *
 * @return bool
 */
function automatorwp_wp_super_cache_purge_all() {

    if( ! function_exists( 'wp_cache_clear_cache' ) ) {
        return false;
    }

    global $wpdb;

    wp_cache_clear_cache( $wpdb->blogid );

    return true;

}

/**
 * Purge WP Super Cache cache for a specific post
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return bool
 */
function automatorwp_wp_super_cache_purge_post( $post_id ) {

    if( ! function_exists( 'wp_cache_post_change' ) ) {
        return false;
    }

    if( empty( $post_id ) ) {
        return false;
    }

    wp_cache_post_change( $post_id );

    return true;

}

/**
 * Purge WP Super Cache cache for a specific URL
 *
 * @since 1.0.0
 *
 * @param string $url
 *
 * @return bool
 */
function automatorwp_wp_super_cache_purge_url( $url ) {

    if( ! function_exists( 'wpsc_delete_url_cache' ) ) {
        return false;
    }

    if( empty( $url ) ) {
        return false;
    }

    return wpsc_delete_url_cache( $url );

}
