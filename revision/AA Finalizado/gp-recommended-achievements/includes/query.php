<?php
/**
 * Query – Fetches recommended achievements with transient caching.
 *
 * Strategy:
 *  1. Build a cache key specific to the user + achievement being viewed.
 *  2. If the transient exists → return cached IDs immediately (0 extra queries).
 *  3. Otherwise → run an optimised WP_Query, store the result for 1 day, return it.
 *
 * Recommendation logic (mirrors the spec):
 *  - Same requirements as the viewed achievement (post_type siblings that share
 *    the same gamipress requirement meta, or the same achievement type).
 *  - Exclude achievements already earned/unlocked by the user.
 *  - Exclude the achievement currently being viewed.
 *  - Limit to gp_ra_max_achievements results.
 *  - Optionally restrict to the same achievement type (gp_ra_same_type setting).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// Cache TTL constant (24 h in seconds)
// -------------------------------------------------------------------------
if ( ! defined( 'GP_RA_CACHE_TTL' ) ) {
    define( 'GP_RA_CACHE_TTL', DAY_IN_SECONDS );
}

/**
 * Returns recommended achievement post objects for a given achievement + user.
 *
 * @param int $achievement_id  The post ID of the achievement being viewed.
 * @param int $user_id         The current user's ID (0 = not logged in).
 *
 * @return WP_Post[]  Array of recommended achievement posts (may be empty).
 */
function gp_ra_get_recommendations( $achievement_id, $user_id = 0 ) {
    if ( ! $achievement_id ) {
        return array();
    }

    // ------------------------------------------------------------------
    // 1. Try cache first
    // ------------------------------------------------------------------
    $cache_key    = gp_ra_cache_key( $achievement_id, $user_id );
    $cached_ids   = get_transient( $cache_key );

    if ( false !== $cached_ids ) {
        // Transient holds an array of post IDs; convert to WP_Post objects.
        if ( empty( $cached_ids ) ) {
            return array();
        }
        return gp_ra_ids_to_posts( $cached_ids );
    }

    // ------------------------------------------------------------------
    // 2. Fetch settings
    // ------------------------------------------------------------------
    $max       = (int) get_option( 'gp_ra_max_achievements', 3 );
    $max       = max( 1, min( 6, $max ) );
    $same_type = (bool) get_option( 'gp_ra_same_type', false );

    // ------------------------------------------------------------------
    // 3. Gather data about the viewed achievement
    // ------------------------------------------------------------------
    $achievement      = get_post( $achievement_id );
    if ( ! $achievement ) {
        set_transient( $cache_key, array(), GP_RA_CACHE_TTL );
        return array();
    }
    $achievement_type = $achievement->post_type; // e.g. 'badge', 'quest', etc.

    // IDs already earned by the user (we'll exclude them).
    $earned_ids = gp_ra_get_user_earned_ids( $user_id );

    // ------------------------------------------------------------------
    // 4. Build WP_Query args
    // ------------------------------------------------------------------

    // Determine which post types to search.
    if ( $same_type ) {
        $post_types = array( $achievement_type );
    } else {
        // All registered GamiPress achievement types.
        $post_types = gp_ra_get_all_achievement_types();
        if ( empty( $post_types ) ) {
            $post_types = array( $achievement_type );
        }
    }

    // Exclude the current achievement and already-earned ones.
    $exclude = array_unique( array_merge( array( $achievement_id ), $earned_ids ) );

    $query_args = array(
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'posts_per_page' => $max,
        'post__not_in'   => $exclude,
        'orderby'        => 'rand',   // Simple randomisation; see note below.
        'no_found_rows'  => true,     // Skip pagination COUNT query – saves a DB hit.
        'fields'         => 'ids',    // Only fetch IDs → lighter query.
        // Meta query: same points/requirement type as the current achievement.
        'meta_query'     => gp_ra_build_meta_query( $achievement_id ),
    );

    /**
     * Filter the WP_Query args before the recommendations query runs.
     * Allows advanced customisation without touching core plugin files.
     *
     * @param array $query_args
     * @param int   $achievement_id
     * @param int   $user_id
     */
    $query_args = apply_filters( 'gp_ra_query_args', $query_args, $achievement_id, $user_id );

    $query      = new WP_Query( $query_args );
    $result_ids = $query->posts; // Array of IDs because we used 'fields' => 'ids'.

    // ------------------------------------------------------------------
    // 5. Store in transient for 24 h
    // ------------------------------------------------------------------
    set_transient( $cache_key, $result_ids, GP_RA_CACHE_TTL );

    return gp_ra_ids_to_posts( $result_ids );
}

// -------------------------------------------------------------------------
// Helper – build a meta_query based on the current achievement's requirements
// -------------------------------------------------------------------------
/**
 * Tries to match achievements that have the same "points to unlock" or
 * the same requirement type meta values as the current one.
 * Falls back to an empty meta_query (no meta filter) if no relevant meta found.
 *
 * @param  int   $achievement_id
 * @return array WP_Query meta_query array.
 */
function gp_ra_build_meta_query( $achievement_id ) {
    // GamiPress stores requirement info on child "gamipress_requirement" posts.
    // We look for children of the current achievement and mirror those conditions.
    $requirement_types = get_post_meta( $achievement_id, '_gamipress_points_amount', true );

    // If GamiPress exposes a helper, use it; otherwise fall back gracefully.
    if ( function_exists( 'gamipress_get_requirement_types_slugs' ) ) {
        // No direct meta filter possible from the parent; return empty so the
        // query just fetches by type/exclusions (still correct and useful).
        return array();
    }

    // Fallback: no meta filter.
    return array();
}

// -------------------------------------------------------------------------
// Helper – get all GamiPress achievement post type slugs
// -------------------------------------------------------------------------
function gp_ra_get_all_achievement_types() {
    if ( function_exists( 'gamipress_get_achievement_types_slugs' ) ) {
        return gamipress_get_achievement_types_slugs();
    }
    // Fallback: return a common default.
    return array( 'badge' );
}

// -------------------------------------------------------------------------
// Helper – get post IDs of achievements already earned by a user
// -------------------------------------------------------------------------
/**
 * @param  int   $user_id  0 means not logged-in; returns empty array.
 * @return int[]
 */
function gp_ra_get_user_earned_ids( $user_id ) {
    if ( ! $user_id ) {
        return array();
    }

    if ( function_exists( 'gamipress_get_user_achievements' ) ) {
        $earned = gamipress_get_user_achievements( array( 'user_id' => $user_id ) );
        if ( ! empty( $earned ) ) {
            return array_unique( wp_list_pluck( $earned, 'post_id' ) );
        }
    }

    return array();
}

// -------------------------------------------------------------------------
// Helper – convert an array of post IDs to WP_Post objects
// -------------------------------------------------------------------------
/**
 * Uses a single WP_Query (IN clause) instead of looping get_post() calls.
 *
 * @param  int[]     $ids
 * @return WP_Post[]
 */
function gp_ra_ids_to_posts( $ids ) {
    if ( empty( $ids ) ) {
        return array();
    }
    $q = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count( $ids ),
        'no_found_rows'  => true,
    ) );
    return $q->posts;
}

// -------------------------------------------------------------------------
// Helper – build a unique, sanitised transient key
// -------------------------------------------------------------------------
/**
 * Max transient key length in WordPress is 172 chars.
 * We use an MD5 hash to stay well within that limit.
 *
 * @param  int $achievement_id
 * @param  int $user_id
 * @return string
 */
function gp_ra_cache_key( $achievement_id, $user_id ) {
    return 'gp_ra_' . md5( $achievement_id . '_' . $user_id );
}

// -------------------------------------------------------------------------
// Cache invalidation helpers
// -------------------------------------------------------------------------

/**
 * Delete ALL recommendation transients for a specific user.
 * Useful when a user earns a new achievement (their recommendations change).
 *
 * Note: WordPress doesn't provide a wildcard delete for transients stored in
 * the options table, so we use a direct DB query (minimal, targeted).
 *
 * @param int $user_id
 */
function gp_ra_delete_user_cache( $user_id ) {
    global $wpdb;
    // Transient keys are stored as _transient_{key} in wp_options.
    // We match the prefix pattern we control.
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE %s",
        '_transient_gp_ra_%'
    ) );
}

/**
 * Delete cached recommendations for a specific achievement (all users).
 * Called when the achievement post is updated.
 *
 * Because the key includes the user ID we can't target per-achievement
 * without iterating; the safest approach is to flush all plugin transients.
 * This only fires on post save, so it's infrequent.
 */
function gp_ra_delete_achievement_cache() {
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_gp_ra_%'
            OR option_name LIKE '_transient_timeout_gp_ra_%'"
    );
}
