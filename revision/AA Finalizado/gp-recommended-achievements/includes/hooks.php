<?php
/**
 * Hooks – integrates the recommended achievements block into:
 *   1. The single achievement page (after_gamipress_achievement template action).
 *   2. The [gamipress_achievement] shortcode output via the gamipress_achievement filter.
 *   3. Cache invalidation on achievement earn + post save.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// 1. Single achievement PAGE integration
// =========================================================================

/**
 * GamiPress outputs single achievements through its template system.
 * The action `gamipress_achievement_content` fires after the achievement
 * content is rendered. We hook there so our block appears below the
 * achievement – outside and after the main content.
 *
 * Priority 20 places us after the default GamiPress output (priority 10).
 */
add_action( 'gamipress_achievement_content', 'gp_ra_after_achievement_content', 20, 1 );
function gp_ra_after_achievement_content( $achievement_id ) {
    echo gp_ra_render( (int) $achievement_id ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Fallback: If the theme uses the_content() directly on a single achievement
 * post type page, append our block via the_content filter.
 */
add_filter( 'the_content', 'gp_ra_append_to_content', 20 );
function gp_ra_append_to_content( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $post = get_post();
    if ( ! $post ) {
        return $content;
    }

    // Only act on GamiPress achievement post types.
    if ( ! gp_ra_is_achievement_post_type( $post->post_type ) ) {
        return $content;
    }

    // Avoid running twice if the action hook already fired.
    if ( did_action( 'gamipress_achievement_content' ) ) {
        return $content;
    }

    return $content . gp_ra_render( $post->ID );
}

// =========================================================================
// 2. [gamipress_achievement] SHORTCODE integration
// =========================================================================

/**
 * GamiPress renders its single-achievement shortcode and then calls a filter
 * `gamipress_achievement_html` with the complete HTML. We append our block.
 */
add_filter( 'gamipress_achievement_html', 'gp_ra_append_to_shortcode_html', 20, 2 );
function gp_ra_append_to_shortcode_html( $html, $atts ) {
    $achievement_id = isset( $atts['id'] ) ? (int) $atts['id'] : 0;

    if ( ! $achievement_id ) {
        return $html;
    }

    return $html . gp_ra_render( $achievement_id );
}

// =========================================================================
// 3. CACHE INVALIDATION
// =========================================================================

/**
 * When a user earns an achievement, invalidate their recommendation cache
 * so the next page view queries fresh results (the earned achievement is now
 * excluded from recommendations).
 *
 * GamiPress fires `gamipress_award_achievement` after granting an achievement.
 *
 * @param int $user_id
 * @param int $achievement_id
 */
add_action( 'gamipress_award_achievement', 'gp_ra_on_achievement_earned', 10, 2 );
function gp_ra_on_achievement_earned( $user_id, $achievement_id ) {
    gp_ra_delete_user_cache( (int) $user_id );
}

/**
 * When an achievement post is saved/updated, flush all recommendation
 * transients because the achievement data may have changed.
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', 'gp_ra_on_achievement_saved', 10, 2 );
function gp_ra_on_achievement_saved( $post_id, $post ) {
    // Only act on GamiPress achievement post types.
    if ( ! gp_ra_is_achievement_post_type( $post->post_type ) ) {
        return;
    }
    // Skip autosaves and revisions.
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    gp_ra_delete_achievement_cache();
}

// =========================================================================
// Utility
// =========================================================================

/**
 * Checks whether a post type is a registered GamiPress achievement type.
 *
 * @param  string $post_type
 * @return bool
 */
function gp_ra_is_achievement_post_type( $post_type ) {
    $types = gp_ra_get_all_achievement_types();
    return in_array( $post_type, (array) $types, true );
}
