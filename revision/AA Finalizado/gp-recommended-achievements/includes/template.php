<?php
/**
 * Template – renders the recommended achievements grid.
 *
 * Outputs a responsive column grid using only inline styles so it works
 * without any theme dependency. A developer can override the template by
 * copying it to their theme at:
 *   {theme}/gp-recommended-achievements/recommended.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// Enqueue front-end stylesheet
// -------------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'gp_ra_enqueue_styles' );
function gp_ra_enqueue_styles() {
    wp_register_style(
        'gp-recommended-achievements',
        GP_RA_PLUGIN_URL . 'assets/css/recommended-achievements.css',
        array(),
        GP_RA_VERSION
    );
    // Only enqueue when actually needed (lazy – hooked in render function).
}

// -------------------------------------------------------------------------
// Main render function
// -------------------------------------------------------------------------
/**
 * Renders the recommended achievements block.
 *
 * @param int $achievement_id  Post ID of the achievement currently shown.
 * @return string              HTML output (empty string if nothing to show).
 */
function gp_ra_render( $achievement_id ) {
    if ( ! $achievement_id ) {
        return '';
    }

    $user_id      = get_current_user_id(); // 0 if not logged in.
    $achievements = gp_ra_get_recommendations( $achievement_id, $user_id );

    if ( empty( $achievements ) ) {
        return '';
    }

    // Enqueue CSS now that we know we'll render something.
    wp_enqueue_style( 'gp-recommended-achievements' );

    $max     = (int) get_option( 'gp_ra_max_achievements', 3 );
    $columns = min( count( $achievements ), $max );

    ob_start();

    // Allow theme/child-plugin to override template.
    $template = gp_ra_locate_template( 'recommended.php' );

    if ( $template ) {
        // Variables available inside the template:
        // $achievements (WP_Post[]), $columns (int), $achievement_id (int)
        include $template;
    } else {
        gp_ra_default_template( $achievements, $columns );
    }

    return ob_get_clean();
}

// -------------------------------------------------------------------------
// Default template (inline – no external file required)
// -------------------------------------------------------------------------
/**
 * @param WP_Post[] $achievements
 * @param int       $columns
 */
function gp_ra_default_template( $achievements, $columns ) {
    ?>
    <div class="gp-recommended-achievements">
        <h3 class="gp-ra-title">
            <?php esc_html_e( 'Recommended Achievements', 'gp-recommended-achievements' ); ?>
        </h3>
        <div class="gp-ra-grid gp-ra-cols-<?php echo esc_attr( $columns ); ?>">
            <?php foreach ( $achievements as $achievement ) : ?>
                <?php gp_ra_render_card( $achievement ); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

// -------------------------------------------------------------------------
// Single achievement card
// -------------------------------------------------------------------------
/**
 * @param WP_Post $achievement
 */
function gp_ra_render_card( $achievement ) {
    $link       = get_permalink( $achievement->ID );
    $title      = get_the_title( $achievement );
    $thumb_id   = get_post_thumbnail_id( $achievement->ID );
    $thumb_url  = $thumb_id
        ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' )
        : '';

    // GamiPress helper for achievement image if no featured image set.
    if ( ! $thumb_url && function_exists( 'gamipress_get_achievement_post_thumbnail' ) ) {
        $thumb_url = gamipress_get_achievement_post_thumbnail( $achievement->ID, array( 80, 80 ) );
    }

    // Points required (GamiPress meta).
    $points_label = '';
    $points       = get_post_meta( $achievement->ID, '_gamipress_points', true );
    if ( $points ) {
        $points_type  = get_post_meta( $achievement->ID, '_gamipress_points_type', true );
        $points_label = sprintf(
            /* translators: 1: points amount, 2: points type */
            esc_html__( '%1$s %2$s', 'gp-recommended-achievements' ),
            number_format_i18n( (int) $points ),
            esc_html( $points_type )
        );
    }
    ?>
    <div class="gp-ra-card">
        <a href="<?php echo esc_url( $link ); ?>" class="gp-ra-card-link">
            <?php if ( $thumb_url ) : ?>
                <div class="gp-ra-card-thumb">
                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="<?php echo esc_attr( $title ); ?>"
                         width="80" height="80" loading="lazy" />
                </div>
            <?php endif; ?>
            <div class="gp-ra-card-body">
                <span class="gp-ra-card-title"><?php echo esc_html( $title ); ?></span>
                <?php if ( $points_label ) : ?>
                    <span class="gp-ra-card-points"><?php echo esc_html( $points_label ); ?></span>
                <?php endif; ?>
            </div>
        </a>
    </div>
    <?php
}

// -------------------------------------------------------------------------
// Template locator (theme override support)
// -------------------------------------------------------------------------
/**
 * Looks for the template in:
 *   1. {child-theme}/gp-recommended-achievements/{file}
 *   2. {theme}/gp-recommended-achievements/{file}
 *   3. {plugin}/templates/{file}
 *
 * @param  string      $file
 * @return string|false  Full path or false if not found.
 */
function gp_ra_locate_template( $file ) {
    $locations = array(
        get_stylesheet_directory() . '/gp-recommended-achievements/' . $file,
        get_template_directory()   . '/gp-recommended-achievements/' . $file,
        GP_RA_PLUGIN_DIR           . 'templates/' . $file,
    );

    foreach ( $locations as $path ) {
        if ( file_exists( $path ) ) {
            return $path;
        }
    }

    return false;
}
