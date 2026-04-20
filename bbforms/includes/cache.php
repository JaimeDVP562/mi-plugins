<?php
/**
 * Cache
 *
 * Used to store commonly used query results
 *
 * @package     BBForms\Cache
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get a cached element.
 *
 * @since 1.0.0
 *
 * @param string    $key        Cache key
 * @param mixed     $default    Default value in case the cache is not found
 * @param bool      $stored     Whatever if the cache has been stored previously in the database or not
 *
 * @return mixed
 */
function bbforms_get_cache( $key = '', $default = null, $stored = true ) {

    if( isset( BBForms()->cache[$key] ) ) {
        return BBForms()->cache[$key];
    } else if( $stored ) {

        $cached = get_option( 'bbforms_cache_' . $key );

        // If has been cached on options, then return the cached value
        if( $cached !== false ) {

            BBForms()->cache[$key] = $cached;

            return BBForms()->cache[$key];

        }

    }

    return $default;

}

/**
 * Set a cached element.
 *
 * @since 1.0.0
 *
 * @param string    $key
 * @param mixed     $value
 * @param bool      $save
 *
 * @return bool
 */
function bbforms_set_cache( $key = '', $value = '', $save = false ) {

    // Just keep value on a floating cache
    // To make it persistent pass $save as true or use bbforms_save_cache() function
    BBForms()->cache[$key] = $value;

    if( $save === true ) {
        return bbforms_save_cache( $key, $value );
    }

    return true;

}

/**
 * Save a cached element.
 *
 * @since 1.6.1
 *
 * @param string    $key
 * @param mixed     $value
 *
 * @return bool
 */
function bbforms_save_cache( $key = '', $value = '' ) {

    // Allow to make value optional but just if element has been already cached
    if( empty( $value ) && isset( BBForms()->cache[$key] ) ) {
        $value = BBForms()->cache[$key];
    }

    // Update the floating cache
    BBForms()->cache[$key] = $value;

    return update_option( 'bbforms_cache_' . $key, $value, false );

}

/**
 * Delete a cached element.
 *
 * @since 1.6.1
 *
 * @param string    $key
 *
 * @return bool
 */
function bbforms_delete_cache( $key = '' ) {

    return delete_option( 'bbforms_cache_' . $key );

}