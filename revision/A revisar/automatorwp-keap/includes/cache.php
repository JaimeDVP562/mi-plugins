<?php
/**
 * Cache Handler
 * Smart caching system for Keap API responses
 *
 * @package     AutomatorWP\Integrations\Keap\Cache
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get cached data or retrieve from API
 *
 * @since 1.0.0
 *
 * @param string   $cache_key     Unique identifier for the cache
 * @param callable $callback      Function to call if cache miss
 * @param int      $expiration    Cache expiration time in seconds (default: 12 hours)
 *
 * @return mixed
 */
function automatorwp_keap_get_cached_data( $cache_key, $callback, $expiration = 43200 ) {
    
    // Check for cached data
    $cached = get_transient( 'automatorwp_keap_' . $cache_key );
    
    if ( false !== $cached ) {
        automatorwp_keap_log( 'Cache hit: ' . $cache_key, 'info' );
        return $cached;
    }

    // Cache miss, execute callback
    automatorwp_keap_log( 'Cache miss: ' . $cache_key, 'info' );
    
    $data = call_user_func( $callback );
    
    if ( is_array( $data ) || is_object( $data ) ) {
        set_transient( 'automatorwp_keap_' . $cache_key, $data, $expiration );
    }
    
    return $data;
}

/**
 * Clear specific cache
 *
 * @since 1.0.0
 *
 * @param string $cache_key Cache identifier (leave empty to clear all)
 *
 * @return void
 */
function automatorwp_keap_clear_cache( $cache_key = '' ) {
    
    if ( empty( $cache_key ) ) {
        // Clear all Keap caches
        global $wpdb;
        $wpdb->query(
            "DELETE FROM $wpdb->options 
            WHERE option_name LIKE '%automatorwp_keap_%' 
            AND option_name LIKE '%_transient%'"
        );
        automatorwp_keap_log( 'All caches cleared', 'info' );
    } else {
        delete_transient( 'automatorwp_keap_' . $cache_key );
        automatorwp_keap_log( 'Cache cleared: ' . $cache_key, 'info' );
    }
}

/**
 * Get cache expiration settings
 *
 * @since 1.0.0
 *
 * @return int Cache expiration in seconds
 */
function automatorwp_keap_get_cache_expiration() {
    return apply_filters( 'automatorwp_keap_cache_expiration', 43200 ); // 12 hours default
}
