<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\W3TC\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Check if W3 Total Cache is available
 *
 * @since 1.0.0
 *
 * @return bool True if W3TC is available and the API functions exist
 */
function automatorwp_w3tc_is_available()
{
    return function_exists('w3tc_flush_all');
}

/**
 * Purge all W3 Total Cache caches
 *
 * @since 1.0.0
 *
 * @return bool True on success, false on failure
 */
function automatorwp_w3tc_purge_all()
{

    if (!function_exists('w3tc_flush_all')) {
        return false;
    }

    w3tc_flush_all();

    return true;
}

/**
 * Purge the W3 Total Cache for a specific post
 *
 * @since 1.0.0
 *
 * @param int $post_id The post ID to purge cache for
 *
 * @return bool True on success, false on failure
 */
function automatorwp_w3tc_purge_post($post_id)
{

    if (!function_exists('w3tc_flush_post')) {
        return false;
    }

    $post_id = absint($post_id);

    if (empty($post_id)) {
        return false;
    }

    // Check if the post exists
    $post = get_post($post_id);

    if (!$post) {
        return false;
    }

    w3tc_flush_post($post_id);

    return true;
}

/**
 * Purge the W3 Total Cache for a specific URL
 *
 * @since 1.0.0
 *
 * @param string $url The URL to purge cache for
 *
 * @return bool True on success, false on failure
 */
function automatorwp_w3tc_purge_url($url)
{

    if (!function_exists('w3tc_flush_url')) {
        return false;
    }

    $url = esc_url_raw(trim($url));

    if (empty($url)) {
        return false;
    }

    w3tc_flush_url($url);

    return true;
}
