<?php
/**
 * Calendar Generator
 *
 * Renders the Generate Calendar modal on the rewards-calendar list page
 * and handles the AJAX request to create the calendar and its rewards.
 *
 * @package GamiPress\Daily_Login_Rewards\Admin\Calendar_Generator
 * @since   1.0.0
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the modal HTML in the admin footer.
 * Only on the rewards-calendar list screen.
 */
function gamipress_daily_login_rewards_calendar_generator_footer() {

    global $post_type, $pagenow;

    if( $pagenow !== 'edit.php' || $post_type !== 'rewards-calendar' ) {
        return;
    }

    $points_types = gamipress_get_points_types();

    $points_type_options = '';
    foreach( $points_types as $slug => $data ) {
        $points_type_options .= '<option value="points:' . esc_attr( $slug ) . '">'
            . esc_html( $data['plural_name'] )
            . ' (' . esc_html__( 'Points', 'gamipress-daily-login-rewards' ) . ')'
            . '</option>';
    }

    ?>
    <div id="gamipress-calendar-generator-overlay" class="gamipress-calendar-generator-overlay" style="display:none;">
        <div id="gamipress-calendar-generator-modal" class="gamipress-calendar-generator-modal">

            <div class="gcg-modal-header">
                <h2><?php _e( 'Generate Calendar', 'gamipress-daily-login-rewards' ); ?></h2>
                <button type="button" class="gcg-close-btn gcg-cancel-btn">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <div class="gcg-modal-body">

                <div class="gcg-modal-left">

                    <div class="gcg-field-group">
                        <label for="gcg-num-days">
                            <strong><?php _e( 'Number of Days', 'gamipress-daily-login-rewards' ); ?></strong>
                        </label>
                        <input type="number" id="gcg-num-days" min="1" max="365" value="30" class="small-text">
                    </div>

                    <div class="gcg-rewards-section">
                        <h3><?php _e( 'Rewards', 'gamipress-daily-login-rewards' ); ?></h3>
                        <p class="description"><?php _e( 'Add reward groups. Use repetitions = 0 to fill remaining days.', 'gamipress-daily-login-rewards' ); ?></p>
                        <div class="gcg-rewards-list" id="gcg-rewards-list"></div>
                        <button type="button" class="button" id="gcg-add-reward-btn">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e( 'Add Reward', 'gamipress-daily-login-rewards' ); ?>
                        </button>
                    </div>

                </div><!-- .gcg-modal-left -->

                <div class="gcg-modal-right">

                    <div class="gcg-preview-section">
                        <div class="gcg-preview-header">
                            <h3><?php _e( 'Preview', 'gamipress-daily-login-rewards' ); ?></h3>
                            <button type="button" class="button" id="gcg-reorder-btn">
                                <span class="dashicons dashicons-image-rotate"></span>
                                <?php _e( 'Reorder', 'gamipress-daily-login-rewards' ); ?>
                            </button>
                        </div>
                        <div id="gcg-preview-grid" class="gcg-preview-grid">
                            <p class="gcg-preview-placeholder"><?php _e( 'Add rewards and click Reorder to see a preview.', 'gamipress-daily-login-rewards' ); ?></p>
                        </div>
                    </div>

                </div><!-- .gcg-modal-right -->

            </div><!-- .gcg-modal-body -->

            <div class="gcg-modal-footer">
                <span class="spinner gcg-spinner" id="gcg-spinner"></span>
                <button type="button" class="button gcg-cancel-btn"><?php _e( 'Cancel', 'gamipress-daily-login-rewards' ); ?></button>
                <button type="button" class="button button-primary" id="gcg-generate-btn"><?php _e( 'Generate', 'gamipress-daily-login-rewards' ); ?></button>
            </div>

        </div><!-- .gamipress-calendar-generator-modal -->
    </div><!-- #gamipress-calendar-generator-overlay -->

    <?php // Reward group template (rendered by JS) ?>
    <script type="text/html" id="gcg-reward-group-template">
        <div class="gcg-reward-group" data-index="{{INDEX}}">
            <div class="gcg-reward-group-header">
                <span><?php _e( 'Reward', 'gamipress-daily-login-rewards' ); ?> <span class="gcg-group-num">{{NUM}}</span></span>
                <button type="button" class="gcg-remove-reward-btn"><span class="dashicons dashicons-no-alt"></span></button>
            </div>
            <div class="gcg-reward-group-body">

                <div class="gcg-field-group">
                    <label><strong><?php _e( 'Reward Type', 'gamipress-daily-login-rewards' ); ?></strong></label>
                    <select class="gcg-reward-type">
                        <option value="none"><?php _e( 'Empty Day', 'gamipress-daily-login-rewards' ); ?></option>
                        <?php echo $points_type_options; ?>
                        <option value="achievement"><?php _e( 'Achievement', 'gamipress-daily-login-rewards' ); ?></option>
                        <option value="rank"><?php _e( 'Rank', 'gamipress-daily-login-rewards' ); ?></option>
                    </select>
                </div>

                <div class="gcg-field-group">
                    <label>
                        <strong><?php _e( 'Repetitions', 'gamipress-daily-login-rewards' ); ?></strong>
                        <span class="description"><?php _e( '(0 = fill remaining slots)', 'gamipress-daily-login-rewards' ); ?></span>
                    </label>
                    <input type="number" class="gcg-reward-repetitions small-text" min="0" value="0">
                </div>

                <?php // Points fields ?>
                <div class="gcg-type-fields gcg-points-fields" style="display:none;">
                    <div class="gcg-field-row">
                        <div class="gcg-field-half">
                            <label><strong><?php _e( 'Min', 'gamipress-daily-login-rewards' ); ?></strong></label>
                            <input type="number" class="gcg-points-min small-text" min="0" value="100">
                        </div>
                        <div class="gcg-field-half">
                            <label><strong><?php _e( 'Max', 'gamipress-daily-login-rewards' ); ?></strong></label>
                            <input type="number" class="gcg-points-max small-text" min="0" value="500">
                        </div>
                    </div>
                </div>

                <?php // Achievement fields ?>
                <div class="gcg-type-fields gcg-achievement-fields" style="display:none;">
                    <div class="gcg-field-group">
                        <label><strong><?php _e( 'Selection', 'gamipress-daily-login-rewards' ); ?></strong></label>
                        <select class="gcg-achievement-mode">
                            <option value="random"><?php _e( 'Random Achievement', 'gamipress-daily-login-rewards' ); ?></option>
                            <option value="specific"><?php _e( 'Specific Achievement', 'gamipress-daily-login-rewards' ); ?></option>
                        </select>
                    </div>
                    <div class="gcg-achievement-specific" style="display:none;">
                        <select class="gcg-achievement-select" style="width:100%;"></select>
                    </div>
                </div>

                <?php // Rank fields ?>
                <div class="gcg-type-fields gcg-rank-fields" style="display:none;">
                    <div class="gcg-field-group">
                        <label><strong><?php _e( 'Selection', 'gamipress-daily-login-rewards' ); ?></strong></label>
                        <select class="gcg-rank-mode">
                            <option value="random"><?php _e( 'Random Rank', 'gamipress-daily-login-rewards' ); ?></option>
                            <option value="specific"><?php _e( 'Specific Rank', 'gamipress-daily-login-rewards' ); ?></option>
                        </select>
                    </div>
                    <div class="gcg-rank-specific" style="display:none;">
                        <select class="gcg-rank-select" style="width:100%;"></select>
                    </div>
                </div>

            </div><!-- .gcg-reward-group-body -->
        </div><!-- .gcg-reward-group -->
    </script>
    <?php
}
add_action( 'admin_footer', 'gamipress_daily_login_rewards_calendar_generator_footer' );

/**
 * AJAX handler: generate a calendar and its rewards from the JS-built array.
 *
 * Expects $_POST['calendar'] as an indexed array of day objects.
 * Each day object shape:
 * {
 *   day:         int,
 *   reward_type: 'none'|'points'|'achievement'|'rank',
 *   label:       string,
 *   // points
 *   points:      int,
 *   points_type: string,
 *   // achievement
 *   achievement_mode: 'random'|'specific',
 *   achievement_id:   int,
 *   // rank
 *   rank_mode: 'random'|'specific',
 *   rank_id:   int,
 * }
 */
function gamipress_daily_login_rewards_ajax_generate_calendar() {

    check_ajax_referer( 'gamipress_admin', 'nonce' );

    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gamipress-daily-login-rewards' ) ) );
    }

    // wp_unslash before sanitizing — WordPress magic-quotes $_POST
    $raw_calendar = isset( $_POST['calendar'] ) ? wp_unslash( $_POST['calendar'] ) : array();

    if( empty( $raw_calendar ) || ! is_array( $raw_calendar ) ) {
        wp_send_json_error( array( 'message' => __( 'No calendar data provided.', 'gamipress-daily-login-rewards' ) ) );
    }

    // Create the parent calendar post as a draft so the user can review it
    $calendar_id = wp_insert_post( array(
        'post_type'   => 'rewards-calendar',
        'post_status' => 'draft',
        'post_title'  => __( 'Generated Calendar', 'gamipress-daily-login-rewards' ),
    ) );

    if( is_wp_error( $calendar_id ) ) {
        wp_send_json_error( array( 'message' => $calendar_id->get_error_message() ) );
    }

    $reward_ids = array();

    foreach( $raw_calendar as $day_data ) {

        // Guard: ensure required keys exist
        if( ! isset( $day_data['day'], $day_data['reward_type'] ) ) {
            continue;
        }

        $day         = absint( $day_data['day'] );
        $reward_type = sanitize_text_field( $day_data['reward_type'] );
        $label       = isset( $day_data['label'] )
            ? sanitize_text_field( $day_data['label'] )
            : sprintf( __( 'Day %d', 'gamipress-daily-login-rewards' ), $day );

        // Create the calendar-reward child post
        $reward_id = wp_insert_post( array(
            'post_type'   => 'calendar-reward',
            'post_status' => 'publish',
            'post_parent' => $calendar_id,
            'post_title'  => $label,
            'menu_order'  => $day,
        ) );

        if( is_wp_error( $reward_id ) ) {
            continue;
        }

        update_post_meta( $reward_id, '_gamipress_reward_type', $reward_type );

        $thumbnail_id = 0;

        if( $reward_type === 'points' ) {

            $points      = isset( $day_data['points'] )     ? absint( $day_data['points'] )                       : 1;
            $points_type = isset( $day_data['points_type'] ) ? sanitize_text_field( $day_data['points_type'] )    : '';

            update_post_meta( $reward_id, '_gamipress_points',           max( 1, $points ) );
            update_post_meta( $reward_id, '_gamipress_points_type',      $points_type );
            // Use points type image as reward image (per spec)
            update_post_meta( $reward_id, '_gamipress_points_type_thumbnail', 1 );

            // Inherit the points type featured image
            $all_points_types = gamipress_get_points_types();
            if( isset( $all_points_types[ $points_type ]['post_id'] ) ) {
                $thumbnail_id = absint( get_post_thumbnail_id( $all_points_types[ $points_type ]['post_id'] ) );
            }

        } elseif( $reward_type === 'achievement' ) {

            $achievement_mode = isset( $day_data['achievement_mode'] )
                ? sanitize_text_field( $day_data['achievement_mode'] )
                : 'random';
            $achievement_id   = 0;

            if( $achievement_mode === 'random' ) {
                // Pick a random published achievement
                $achievement_types = gamipress_get_achievement_types();
                $achievement_slugs = array_keys( $achievement_types );

                if( ! empty( $achievement_slugs ) ) {
                    $random = get_posts( array(
                        'post_type'        => $achievement_slugs,
                        'post_status'      => 'publish',
                        'numberposts'      => 1,
                        'orderby'          => 'rand',
                        'suppress_filters' => false,
                    ) );
                    $achievement_id = ! empty( $random ) ? absint( $random[0]->ID ) : 0;
                }
            } else {
                $achievement_id = isset( $day_data['achievement_id'] ) ? absint( $day_data['achievement_id'] ) : 0;
            }

            update_post_meta( $reward_id, '_gamipress_achievement',           $achievement_id );
            // Use achievement image as reward image (per spec)
            update_post_meta( $reward_id, '_gamipress_achievement_thumbnail', 1 );

            if( $achievement_id ) {
                $thumbnail_id = absint( get_post_thumbnail_id( $achievement_id ) );
            }

        } elseif( $reward_type === 'rank' ) {

            $rank_mode = isset( $day_data['rank_mode'] )
                ? sanitize_text_field( $day_data['rank_mode'] )
                : 'random';
            $rank_id   = 0;

            if( $rank_mode === 'random' ) {
                // Pick a random published rank
                $rank_types = gamipress_get_rank_types();
                $rank_slugs = array_keys( $rank_types );

                if( ! empty( $rank_slugs ) ) {
                    $random = get_posts( array(
                        'post_type'        => $rank_slugs,
                        'post_status'      => 'publish',
                        'numberposts'      => 1,
                        'orderby'          => 'rand',
                        'suppress_filters' => false,
                    ) );
                    $rank_id = ! empty( $random ) ? absint( $random[0]->ID ) : 0;
                }
            } else {
                $rank_id = isset( $day_data['rank_id'] ) ? absint( $day_data['rank_id'] ) : 0;
            }

            update_post_meta( $reward_id, '_gamipress_rank',           $rank_id );
            // Use rank image as reward image (per spec)
            update_post_meta( $reward_id, '_gamipress_rank_thumbnail', 1 );

            if( $rank_id ) {
                $thumbnail_id = absint( get_post_thumbnail_id( $rank_id ) );
            }

        }
        // 'none' type needs no extra meta

        // Set the featured image if we found one from the GP type
        if( $thumbnail_id ) {
            set_post_thumbnail( $reward_id, $thumbnail_id );
        }

        $reward_ids[] = $reward_id;
    }

    // Warm the rewards cache so the calendar works immediately
    gamipress_update_post_meta( $calendar_id, '_gamipress_daily_login_rewards_rewards_cache', $reward_ids );

    wp_send_json_success( array(
        'redirect'    => get_edit_post_link( $calendar_id, 'raw' ),
        'calendar_id' => $calendar_id,
    ) );
}
add_action( 'wp_ajax_gamipress_daily_login_rewards_generate_calendar', 'gamipress_daily_login_rewards_ajax_generate_calendar' );