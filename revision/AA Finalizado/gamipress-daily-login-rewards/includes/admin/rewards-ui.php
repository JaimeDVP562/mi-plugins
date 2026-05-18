<?php
/**
 * Rewards UI
 *
 * @package     GamiPress\Daily_login_Rewards\Admin\Rewards_UI
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add rewards meta boxes
 *
 * @since  1.0.5
 *
 * @return void
 */
function gamipress_daily_login_rewards_add_rewards_ui_meta_box() {

    // Points Awards
    add_meta_box( 'gamipress-rewards-ui', __( 'Rewards', 'gamipress-daily-login-rewards' ), 'gamipress_daily_login_rewards_rewards_ui_meta_box', 'rewards-calendar', 'advanced', 'default' );

}
add_action( 'add_meta_boxes', 'gamipress_daily_login_rewards_add_rewards_ui_meta_box' );

/**
 * Renders the HTML for meta box, refreshes whenever a new point award is added
 *
 * @since 1.0.0
 *
 * @param WP_Post $post     The current post object.
 * @param array   $metabox  With metabox id, title, callback, and args elements.
 *
 * @return void
 */
function gamipress_daily_login_rewards_rewards_ui_meta_box( $post, $metabox ) {

    $calendar_rewards = get_posts( array(
        'post_type' => 'calendar-reward',
        'post_parent' => $post->ID,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'numberposts' => -1,
        'suppress_filters' => false
    ) );
    ?>

    <ul class="gamipress-daily-login-rewards-rewards-list">

        <?php foreach ( $calendar_rewards as $index => $calendar_reward ) :

            if( absint( $calendar_reward->menu_order ) !== ( $index + 1 ) ) {
                $calendar_reward->menu_order = $index + 1;

                wp_update_post( $calendar_reward );
            }

            gamipress_daily_login_rewards_rewards_ui_html( $calendar_reward, $post );

        endforeach; ?>

        <li class="gamipress-daily-login-rewards-add-new-reward">
            <span class="dashicons dashicons-plus"></span>
            <span><?php _e( 'Add New Reward', 'gamipress-daily-login-rewards' ); ?></span>
        </li>

    </ul>

    <?php // Render our buttons ?>
    <input class="button-primary gamipress-daily-login-rewards-save-rewards" type="button" value="<?php _e( 'Save All Rewards', 'gamipress-daily-login-rewards' ); ?>">
    <span class="spinner rewards-spinner"></span>

    <?php
}

/**
 * Helper function for generating the HTML output for configuring a given reward
 *
 * @since  1.0.0
 *
 * @param  integer|WP_post $reward  The given calendar reward
 * @param  integer|WP_Post $post    The given parent post
 *
 * @return string           The concatenated HTML input for the reward
 */
function gamipress_daily_login_rewards_rewards_ui_html( $reward, $post ) {

    // Our prefix
    $prefix = '_gamipress_daily_login_rewards_';

    // Setup reward and rewards calendar post objects
    $reward = get_post( $reward );
    $post = get_post( $post );

    // Get GamiPress types
    $points_types = gamipress_get_points_types();

    // Setup rewards object
    $reward_object = gamipress_daily_login_rewards_get_reward_object( $reward->ID );

    ?>
    <li class="reward-row reward-<?php echo $reward->ID; ?>">

        <input type="hidden" name="reward_id" value="<?php echo $reward->ID; ?>" />
        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
        <input type="hidden" name="order" value="<?php echo $reward->menu_order; ?>">

        <div class="reward-header">

            <h3>Day <?php echo $reward->menu_order; ?></h3>

            <div class="delete-reward">
                <span class="dashicons dashicons-no-alt"></span>
            </div>

        </div>

        <div class="reward-thumbnail <?php if( $reward_object['thumbnail_id'] ) : ?>has-thumbnail<?php endif; ?>">

            <input type="hidden" name="reward_thumbnail" value="<?php echo ( $reward_object['thumbnail_id'] ) ? $reward_object['thumbnail_id'] : ''; ?>" />

            <span class="dashicons dashicons-camera"></span>

            <span class="dashicons dashicons-no-alt remove-reward-thumbnail" <?php if( ! $reward_object['thumbnail_id'] ) : ?>style="display: none;"<?php endif; ?>></span>

            <?php if( $reward_object['thumbnail_id'] ) : ?>
                <img src="<?php echo wp_get_attachment_image_url( $reward_object['thumbnail_id'], 'full' ); ?>" alt="" style="max-width:100%;">
            <?php endif; ?>

        </div>

        <span class="reward-thumbnail-desc reward-field-desc"><?php _e( 'Click the image to edit or update.', 'gamipress-daily-login-rewards' ); ?></span>

        <input type="text" placeholder="<?php _e( 'Label', 'gamipress-daily-login-rewards' ) ?>" class="reward-label" value="<?php echo $reward->post_title; ?>">

        <div class="reward-types-list">

            <div class="reward-type-row">
                <label for="reward-type-none-<?php echo $reward->ID; ?>">
                    <input type="radio" id="reward-type-none-<?php echo $reward->ID; ?>" class="reward-type" value="none" <?php checked( $reward_object['reward_type'], 'none' ); ?>>
                    <span class="dashicons dashicons-marker"></span>
                    <span><?php _e( 'Nothing', 'gamipress-daily-login-rewards' ); ?></span>
                </label>

                <div class="reward-type-form reward-type-none-form" <?php if( $reward_object['reward_type'] !== 'none' ) : ?>style="display:none"<?php endif; ?>>

                    <span class="reward-nothing-desc"><?php _e( 'That\'s okay, a day that user needs to log in but without get rewarded.', 'gamipress-daily-login-rewards' ); ?></span>

                </div>
            </div>

            <div class="reward-type-row">
                <label for="reward-type-points-<?php echo $reward->ID; ?>">
                    <input type="radio" id="reward-type-points-<?php echo $reward->ID; ?>" class="reward-type" value="points" <?php checked( $reward_object['reward_type'], 'points' ); ?>>
                    <span class="dashicons dashicons-star-filled"></span>
                    <span><?php _e( 'Points', 'gamipress-daily-login-rewards' ); ?></span>
                </label>

                <div class="reward-type-form reward-type-points-form" <?php if( $reward_object['reward_type'] !== 'points' ) : ?>style="display:none"<?php endif; ?>>

                    <input type="number" class="reward-points-amount" min="0" placeholder="0" value="<?php echo $reward_object['points']; ?>">

                    <select class="reward-points-type">
                        <?php foreach( $points_types as $points_type => $points_type_data ) : ?>
                            <option value="<?php echo $points_type; ?>" <?php selected( $reward_object['points_type'], $points_type ); ?>><?php echo $points_type_data['plural_name']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>
                        <input type="checkbox" class="reward-points-image" <?php checked( $reward_object['points_type_thumbnail'], 1 ); ?>>
                        <span><?php _e( 'Use points type image as image.' ) ?></span>
                    </label>

                </div>
            </div>

            <div class="reward-type-row">
                <label for="reward-type-achievement-<?php echo $reward->ID; ?>">
                    <input type="radio" id="reward-type-achievement-<?php echo $reward->ID; ?>" class="reward-type" value="achievement" <?php checked( $reward_object['reward_type'], 'achievement' ); ?>>
                    <span class="dashicons dashicons-awards"></span>
                    <span><?php _e( 'Achievement', 'gamipress-daily-login-rewards' ); ?></span>
                </label>

                <div class="reward-type-form reward-type-achievement-form" <?php if( $reward_object['reward_type'] !== 'achievement' ) : ?>style="display:none"<?php endif; ?>>

                    <select class="reward-achievement">
                        <?php if( $reward_object['achievement'] ) : ?>
                            <option value="<?php echo $reward_object['achievement']; ?>" selected="selected"><?php echo gamipress_get_post_field( 'post_title', $reward_object['achievement'] ) . ' (#' . $reward_object['achievement'] . ')'; ?></option>
                        <?php endif; ?>
                    </select>

                    <label>
                        <input type="checkbox" class="reward-achievement-image" <?php checked( $reward_object['achievement_thumbnail'], 1 ); ?>>
                        <span><?php _e( 'Use achievement image as image.' ) ?></span>
                    </label>

                </div>
            </div>

            <div class="reward-type-row">
                <label for="reward-type-rank-<?php echo $reward->ID; ?>">
                    <input type="radio" id="reward-type-rank-<?php echo $reward->ID; ?>" class="reward-type" value="rank" <?php checked( $reward_object['reward_type'], 'rank' ); ?>>
                    <span class="dashicons dashicons-rank"></span>
                    <span><?php _e( 'Rank', 'gamipress-daily-login-rewards' ); ?></span>
                </label>

                <div class="reward-type-form reward-type-rank-form" <?php if( $reward_object['reward_type'] !== 'rank' ) : ?>style="display:none"<?php endif; ?>>

                    <select class="reward-rank">
                        <?php if( $reward_object['rank'] ) : ?>
                            <option value="<?php echo $reward_object['rank']; ?>" selected="selected"><?php echo gamipress_get_post_field( 'post_title', $reward_object['rank'] ) . ' (#' . $reward_object['rank'] . ')'; ?></option>
                        <?php endif; ?>
                    </select>

                    <label>
                        <input type="checkbox" class="reward-rank-image" <?php checked( $reward_object['rank_thumbnail'], 1 ); ?>>
                        <span><?php _e( 'Use rank image as image.' ) ?></span>
                    </label>

                </div>
            </div>

        </div>

    </li>
    <?php

}

/**
 * AJAX Handler for adding a new reward
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_ajax_add_reward() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    global $wpdb;

    $posts  = GamiPress()->db->posts;

    // Get higher menu order
    $last = $wpdb->get_var( $wpdb->prepare(
        "SELECT p.menu_order
			FROM {$posts} AS p
			WHERE p.post_type = %s
			 AND p.post_status = %s
			 AND p.post_parent = %s
			ORDER BY menu_order DESC
			LIMIT 1",
        'calendar-reward',
        'publish',
        $_POST['post_id']
    ) );

    $last = absint( $last ) + 1;

    // Create a new reward post and grab it's ID
    $reward_id = wp_insert_post( array(
        'post_type'   => 'calendar-reward',
        'post_status' => 'publish',
        'post_parent' => $_POST['post_id'],
        'menu_order'  => $last
    ) );

    gamipress_daily_login_rewards_rewards_ui_html( $reward_id, $_POST['post_id'] );

    // Die here, because it's AJAX
    die;
}
add_action( 'wp_ajax_gamipress_daily_login_rewards_add_reward', 'gamipress_daily_login_rewards_ajax_add_reward' );

/**
 * AJAX Handler for deleting a reward
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_ajax_delete_reward() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    wp_delete_post( $_POST['reward_id'] );
    die;
}
add_action( 'wp_ajax_gamipress_daily_login_rewards_delete_reward', 'gamipress_daily_login_rewards_ajax_delete_reward' );

/**
 * AJAX Handler for updating rewards
 *
 * @since 1.0.0
 */
function gamipress_daily_login_rewards_ajax_update_rewards() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Only continue if we have any requirements
    if ( isset( $_POST['rewards'] ) ) {

        // Grab our $wpdb global
        global $wpdb;

        // Loop through each of the created requirements
        foreach ( $_POST['rewards'] as $key => $reward ) {

            // Grab all of the relevant values of that requirement
            $reward_id              = $reward['reward_id'];
            $reward_type            = $reward['reward_type'];
            $order                  = absint( $reward['order'] );
            $label                  = $reward['label'];
            $thumbnail              = $reward['thumbnail'];

            // Update our relevant meta
            update_post_meta( $reward_id, '_gamipress_reward_type', $reward_type );

            // Specific reward type data
            if( $reward_type === 'points' ) {

                $points                     = ( ! empty( $reward['points'] ) ) ? absint( $reward['points'] ) : 1;
                $points_type                = ( ! empty( $reward['points_type'] ) ) ? $reward['points_type'] : '';
                $points_type_thumbnail      = ( ! empty( $reward['points_type_thumbnail'] ) ) ? absint( $reward['points_type_thumbnail'] ) : 0;

                update_post_meta( $reward_id, '_gamipress_points', $points );
                update_post_meta( $reward_id, '_gamipress_points_type', $points_type );
                update_post_meta( $reward_id, '_gamipress_points_type_thumbnail', $points_type_thumbnail );

            } else if( $reward_type === 'achievement' ) {

                $achievement            = ( ! empty( $reward['achievement'] ) ) ? absint( $reward['achievement'] ) : 0;
                $achievement_thumbnail  = ( ! empty( $reward['achievement_thumbnail'] ) ) ? absint( $reward['achievement_thumbnail'] ) : 0;

                update_post_meta( $reward_id, '_gamipress_achievement', $achievement );
                update_post_meta( $reward_id, '_gamipress_achievement_thumbnail', $achievement_thumbnail );

            } else if( $reward_type === 'rank' ) {

                $rank           = ( ! empty( $reward['rank'] ) ) ? absint( $reward['rank'] ) : 0;
                $rank_thumbnail = ( ! empty( $reward['rank_thumbnail'] ) ) ? absint( $reward['rank_thumbnail'] ) : 0;

                update_post_meta( $reward_id, '_gamipress_rank', $rank );
                update_post_meta( $reward_id, '_gamipress_rank_thumbnail', $rank_thumbnail );

            }

            // Action to store custom requirement data
            do_action( 'gamipress_daily_login_rewards_ajax_update_reward', $reward_id, $reward );

            if( $thumbnail ) {
                set_post_thumbnail( $reward_id, $thumbnail );
            } else {
                delete_post_meta( $reward_id, '_thumbnail_id' );
            }

            // Update our original post
            wp_update_post( array(
                'ID' => $reward_id,
                'post_title' => $label,
                'menu_order' => $order,
            ) );

        }

        // Build a cache of calendar rewards ordered by menu_order
        $posts = GamiPress()->db->posts;

        $rewards = $wpdb->get_col( $wpdb->prepare(
            "SELECT p.ID
                FROM {$posts} AS p
                WHERE p.post_type = %s
                 AND p.post_status = %s
                 AND p.post_parent = %d
                ORDER BY p.menu_order ASC",
            'calendar-reward',
            'publish',
            $_POST['post_id']
        ) );

        gamipress_update_post_meta( $_POST['post_id'], '_gamipress_daily_login_rewards_rewards_cache', $rewards );

    }

    // We're done here.
    die;

}
add_action( 'wp_ajax_gamipress_daily_login_rewards_update_rewards', 'gamipress_daily_login_rewards_ajax_update_rewards' );