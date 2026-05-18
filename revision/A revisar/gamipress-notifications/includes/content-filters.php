<?php
/**
 * Content Filters
 *
 * @package     GamiPress\Notifications\Content_Filters
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Process the achievement notification content
 *
 * @since 1.0.2
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $achievement
 *
 * @return string
 */
function gamipress_notification_process_achievement_notification( $content, $earning, $achievement ) {

    // Bail if not is an achievement
    if( ! in_array( $earning->post_type, gamipress_get_achievement_types_slugs() ) ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered as achievement
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Bail if achievements notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_achievements', gamipress_notifications_get_option( 'disable_achievements', false ), $earning, $achievement ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'achievement_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'achievement_content_pattern', '' );

    // Force Spanish pattern for all achievements (fallback for legacy stored values)
    if ( empty( $title_pattern ) || strpos( $title_pattern, 'Congratulations {user}! You unlocked the' ) !== false ) {
        $title_pattern = 'Felicidades {user}! Has desbloqueado {title}';
    }

    // Filters to allow override patterns
    $title_pattern      = apply_filters( 'gamipress_notifications_achievement_title_pattern', $title_pattern, $earning, $achievement );
    $content_pattern    = apply_filters( 'gamipress_notifications_achievement_content_pattern', $content_pattern, $earning, $achievement );

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_achievement_pattern( $title_pattern, $earning );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_achievement_pattern( $content_pattern, $earning );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Setup achievements template args
    $template_args = array(
        'user_id' => get_current_user_id()
    );

    $original_achievement_fields = GamiPress()->shortcodes['gamipress_achievement']->fields;

    // Remove achievement id field
    unset( $original_achievement_fields['id'] );

    foreach( $original_achievement_fields as $field_id => $field_args ) {

        if( $field_args['type'] === 'checkbox' ) {
            $template_args[$field_id] = ( (bool) gamipress_notifications_get_option( $field_id, false ) ) ? 'yes' : 'no';
        } else {
            $template_args[$field_id] = gamipress_notifications_get_option( $field_id, isset( $field_args['default'] ) ? $field_args['default'] : '' );
        }

    }

    $template_args = apply_filters( 'gamipress_notifications_achievement_template_args', $template_args, $earning, $achievement );

    $gamipress_notifications_template_args['template_args'] = $template_args;

    // Try to load notification-achievement-{achievement-type}.php, if not exists then load notification-achievement.php
    ob_start();
        gamipress_get_template_part( 'notification-achievement', $achievement->post_type );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_achievement_notification', 10, 3 );

/**
 * Process the step notification content
 *
 * @since 1.0.2
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $step
 *
 * @return string
 */
function gamipress_notification_process_step_notification( $content, $earning, $step ) {

    // Bail if not is a step
    if( $earning->post_type !== 'step' ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered as achievement
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Get the step achievement to allow specific achievement type template
    $achievement = gamipress_get_parent_of_achievement( $earning->ID );

    // Bail if step has not an achievement
    if( ! $achievement ) {
        return $content;
    }

    // Bail if steps notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_steps', gamipress_notifications_get_option( 'disable_steps', false ), $earning, $step, $achievement ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'achievement' => $achievement,
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'step_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'step_content_pattern', '' );

    // Filters to allow override patterns
    $title_pattern      = apply_filters( 'gamipress_notifications_step_title_pattern', $title_pattern, $earning, $step, $achievement );
    $content_pattern    = apply_filters( 'gamipress_notifications_step_content_pattern', $content_pattern, $earning, $step, $achievement );

    // Bail if no title and content setup
    if( empty( $title_pattern ) && empty( $content_pattern ) ) {
        return $content;
    }

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_step_pattern( $title_pattern, $earning, $achievement );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_step_pattern( $content_pattern, $earning, $achievement );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Try to load notification-step-{achievement-type}.php, if not exists then load notification-step.php
    ob_start();
        gamipress_get_template_part( 'notification-step', $achievement->post_type );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_step_notification', 10, 3 );

/**
 * Process the points award notification content
 *
 * @since 1.0.2
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $points_award
 *
 * @return string
 */
function gamipress_notification_process_points_award_notification( $content, $earning, $points_award ) {

    // Bail if not is a points award
    if( $earning->post_type !== 'points-award' ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Get the points type to allow specific points type template
    $points_type = gamipress_get_points_award_points_type( $earning->ID );

    // Bail if points award has not a points type
    if( ! $points_type ) {
        return $content;
    }

    // Bail if points awards notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_points_awards', gamipress_notifications_get_option( 'disable_points_awards', false ), $earning, $points_award, $points_type ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'points_type' => $points_type,
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'points_award_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'points_award_content_pattern', '' );

    // Filters to allow override patterns
    $title_pattern      = apply_filters( 'gamipress_notifications_points_award_title_pattern', $title_pattern, $earning, $points_award, $points_type );
    $content_pattern    = apply_filters( 'gamipress_notifications_points_award_content_pattern', $content_pattern, $earning, $points_award, $points_type );

    // Bail if no title and content setup
    if( empty( $title_pattern ) && empty( $content_pattern ) ) {
        return $content;
    }

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_points_award_pattern( $title_pattern, $earning, $points_type );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_points_award_pattern( $content_pattern, $earning, $points_type );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Try to load notification-points-award-{points-type}.php, if not exists then load notification-points-award.php
    ob_start();
    gamipress_get_template_part( 'notification-points-award', $points_type->post_name );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_points_award_notification', 10, 3 );

/**
 * Process the points deduct notification content
 *
 * @since 1.0.3
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $points_deduct
 *
 * @return string
 */
function gamipress_notification_process_points_deduct_notification( $content, $earning, $points_deduct ) {

    // Bail if not is a points deduct
    if( $earning->post_type !== 'points-deduct' ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Get the points type to allow specific points type template
    $points_type = gamipress_get_points_deduct_points_type( $earning->ID );

    // Bail if points deduct has not a points type
    if( ! $points_type ) {
        return $content;
    }

    // Bail if points deducts notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_points_deducts', gamipress_notifications_get_option( 'disable_points_deducts', false ), $earning, $points_deduct, $points_type ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'points_type' => $points_type,
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'points_deduct_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'points_deduct_content_pattern', '' );

    // Filters to allow override patterns
    $title_pattern      = apply_filters( 'gamipress_notifications_points_deduct_title_pattern', $title_pattern, $earning, $points_deduct, $points_type );
    $content_pattern    = apply_filters( 'gamipress_notifications_points_deduct_content_pattern', $content_pattern, $earning, $points_deduct, $points_type );

    // Bail if no title and content setup
    if( empty( $title_pattern ) && empty( $content_pattern ) ) {
        return $content;
    }

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_points_deduct_pattern( $title_pattern, $earning, $points_type );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_points_deduct_pattern( $content_pattern, $earning, $points_type );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Try to load notification-points-deduct-{points-type}.php, if not exists then load notification-points-deduct.php
    ob_start();
    gamipress_get_template_part( 'notification-points-deduct', $points_type->post_name );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_points_deduct_notification', 10, 3 );

/**
 * Process the rank notification content
 *
 * @since 1.0.2
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $rank
 *
 * @return string
 */
function gamipress_notification_process_rank_notification( $content, $earning, $rank ) {

    // Bail if not is a rank
    if( ! in_array( $earning->post_type, gamipress_get_rank_types_slugs() ) ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered as rank
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Bail if rank notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_ranks', gamipress_notifications_get_option( 'disable_ranks', false ), $earning, $rank ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'rank_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'rank_content_pattern', '' );

    // Force Spanish pattern for all ranks (fallback for legacy stored values)
    if ( empty( $title_pattern ) || strpos( $title_pattern, 'Congratulations' ) !== false ) {
        $title_pattern = '¡Increíble! Has ascendido al rango: {title}';
    }

    // Filters to allow override patterns
    $title_pattern      = apply_filters( 'gamipress_notifications_rank_title_pattern', $title_pattern, $earning, $rank );
    $content_pattern    = apply_filters( 'gamipress_notifications_rank_content_pattern', $content_pattern, $earning, $rank );

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_rank_pattern( $title_pattern, $earning );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_rank_pattern( $content_pattern, $earning );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Setup ranks template args
    $template_args = array(
        'user_id' => get_current_user_id()
    );

    $original_rank_fields = GamiPress()->shortcodes['gamipress_rank']->fields;

    // Remove rank id field
    unset( $original_rank_fields['id'] );

    foreach( $original_rank_fields as $field_id => $field_args ) {

        // Need to add prefix 'rank_' to avoid issues with achievement fields

        if( $field_args['type'] === 'checkbox' ) {
            $template_args[$field_id] = ( (bool) gamipress_notifications_get_option( 'rank_' .$field_id, false ) ) ? 'yes' : 'no';
        } else {
            $template_args[$field_id] = gamipress_notifications_get_option( 'rank_' . $field_id, isset( $field_args['default'] ) ? $field_args['default'] : '' );
        }

    }

    $template_args = apply_filters( 'gamipress_notifications_rank_template_args', $template_args, $earning, $rank );

    $gamipress_notifications_template_args['template_args'] = $template_args;

    // Try to load notification-rank-{rank-type}.php, if not exists then load notification-rank.php
    ob_start();
    gamipress_get_template_part( 'notification-rank', $rank->post_type );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_rank_notification', 10, 3 );

/**
 * Process the rank requirement notification content
 *
 * @since 1.0.2
 *
 * @param string $content
 * @param object $earning
 * @param WP_Post $rank_requirement
 *
 * @return string
 */
function gamipress_notification_process_rank_requirement_notification( $content, $earning, $rank_requirement ) {

    // Bail if not is a rank requirement
    if( $earning->post_type !== 'rank-requirement' ) {
        return $content;
    }
    
    // Exclude the notifications page itself from being rendered
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && $earning->post_id === (int) $page_id ) {
        return '';
    }

    // Get the rank requirement rank to allow specific rank type template
    $rank = gamipress_get_rank_requirement_rank( $earning->ID );

    // Bail if rank requirement has not a rank
    if( ! $rank ) {
        return $content;
    }

    // Bail if rank requirements notifications are disabled
    if( (bool) apply_filters( 'gamipress_notifications_disable_rank_requirements', gamipress_notifications_get_option( 'disable_rank_requirements', false ), $earning, $rank_requirement, $rank ) ) {
        return $content;
    }

    global $gamipress_notifications_template_args;

    // Initialize template args
    $gamipress_notifications_template_args = array(
        'rank' => $rank,
        'earning' => $earning,
    );

    // Get the patterns
    $title_pattern      = gamipress_notifications_get_option( 'rank_requirement_title_pattern', '' );
    $content_pattern    = gamipress_notifications_get_option( 'rank_requirement_content_pattern', '' );

    // Filters to allow override
    $title_pattern      = apply_filters( 'gamipress_notifications_rank_requirement_title_pattern', $title_pattern, $earning, $rank_requirement, $rank );
    $content_pattern    = apply_filters( 'gamipress_notifications_rank_requirement_content_pattern', $content_pattern, $earning, $rank_requirement, $rank );

    // Bail if no title and content setup
    if( empty( $title_pattern ) && empty( $content_pattern ) ) {
        return $content;
    }

    // Parse the notification patterns
    $gamipress_notifications_template_args['notification_title'] = gamipress_notifications_parse_rank_requirement_pattern( $title_pattern, $earning, $rank );
    $gamipress_notifications_template_args['notification_content'] = gamipress_notifications_parse_rank_requirement_pattern( $content_pattern, $earning, $rank );

    // Text formatting and shortcode execution
    $gamipress_notifications_template_args['notification_content'] = wpautop( $gamipress_notifications_template_args['notification_content'] );
    $gamipress_notifications_template_args['notification_content'] = do_shortcode( $gamipress_notifications_template_args['notification_content'] );

    // Try to load notification-rank-requirement-{rank-type}.php, if not exists then load notification-rank-requirement.php
    ob_start();
    gamipress_get_template_part( 'notification-rank-requirement', $rank->post_type );
    $content = ob_get_clean();

    return $content;

}
add_filter( 'gamipress_notification_process_notification_content', 'gamipress_notification_process_rank_requirement_notification', 10, 3 );