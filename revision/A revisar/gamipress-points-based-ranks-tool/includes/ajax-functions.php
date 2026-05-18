<?php
/**
 * AJAX Functions
 *
 * @package GamiPress\Points_Based_Ranks\AJAX
 *
 * This file contains AJAX handlers for the Points-Based Ranks Tool plugin.
 * Handles rank creation, requirement setup, and user awarding operations.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler for creating points-based ranks.
 *
 * Processes rank creation requests with configurable points thresholds.
 * Creates ranks in batches of 20 to avoid timeout issues, returning a
 * flag to indicate if additional processing is needed.
 *
 * @since 1.0.0
 *
 * Expected POST parameters:
 * - points_type   : Slug of the points type (required)
 * - rank_type     : Slug of the rank type (required)
 * - points_step   : Points increment between ranks (required, > 0)
 * - rank_count    : Total number of ranks to create (required, > 0)
 * - name_pattern  : Rank name with {number} and/or {letter} placeholders (required)
 * - loop          : Current batch iteration number (default: 0)
 * - badge_images  : Array of base64-encoded badge images (optional)
 *
 * @return void Outputs JSON response and exits.
 */
function gprbt_ajax_points_based_ranks_tool() {
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    if ( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( 'You do not have permission to perform this action.' );
    }

    $points_type   = isset( $_POST['points_type'] ) ? sanitize_text_field( wp_unslash( $_POST['points_type'] ) ) : '';
    $rank_type     = isset( $_POST['rank_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rank_type'] ) ) : '';
    $points_step   = isset( $_POST['points_step'] ) ? absint( $_POST['points_step'] ) : 0;
    $rank_count    = isset( $_POST['rank_count'] ) ? absint( $_POST['rank_count'] ) : 0;
    $name_pattern  = isset( $_POST['name_pattern'] ) ? sanitize_text_field( wp_unslash( $_POST['name_pattern'] ) ) : '';
    $loop          = isset( $_POST['loop'] ) ? absint( $_POST['loop'] ) : 0;
    $badge_images  = isset( $_POST['badge_images'] ) && is_array( $_POST['badge_images'] ) ? array_map( 'gprbt_sanitize_base64_image', wp_unslash( $_POST['badge_images'] ) ) : array();

    $points_types = gamipress_get_points_types();
    $rank_types   = gamipress_get_rank_types();

    if ( '' === $points_type || ! isset( $points_types[ $points_type ] ) ) {
        wp_send_json_error( 'Please select a valid points type.' );
    }

    if ( '' === $rank_type || ! isset( $rank_types[ $rank_type ] ) ) {
        wp_send_json_error( 'Please select a valid rank type.' );
    }

    if ( $points_step <= 0 ) {
        wp_send_json_error( 'Points step must be a number greater than zero.' );
    }

    if ( $rank_count <= 0 ) {
        wp_send_json_error( 'The number of ranks to create must be greater than zero.' );
    }

    if ( '' === trim( $name_pattern ) ) {
        wp_send_json_error( 'Name pattern cannot be empty.' );
    }

    $limit = 20;
    $offset = $loop * $limit;
    $current_position = $offset + 1;
    $max_position = min( $rank_count, $offset + $limit );
    $created = 0;
    $created_rank_ids = array();

    while ( $current_position <= $max_position ) {
        $rank_name = str_replace(
            array( '{number}', '{letter}' ),
            array( $current_position, gprbt_number_to_letters( $current_position ) ),
            $name_pattern
        );

        $rank_id = wp_insert_post( array(
            'post_title'  => $rank_name,
            'post_type'   => $rank_type,
            'post_status' => 'publish',
            'menu_order'  => $current_position,
        ), true );

        if ( is_wp_error( $rank_id ) ) {
            wp_send_json_error( $rank_id->get_error_message() );
        }

        if ( $current_position > 1 ) {
            $points_required = $points_step * ( $current_position - 1 );
            $requirement_title = sprintf( 'Requirement for %s', $rank_name );

            $requirement_id = wp_insert_post( array(
                'post_title'  => $requirement_title,
                'post_type'   => 'rank-requirement',
                'post_status' => 'publish',
                'post_parent' => $rank_id,
                'menu_order'  => 0,
            ), true );

            if ( is_wp_error( $requirement_id ) ) {
                wp_delete_post( $rank_id, true );
                wp_send_json_error( $requirement_id->get_error_message() );
            }

            gamipress_update_post_meta( $requirement_id, '_gamipress_points_condition', 'greater_or_equal' );
            gamipress_update_post_meta( $requirement_id, '_gamipress_points_required', $points_required );
            gamipress_update_post_meta( $requirement_id, '_gamipress_points_type_required', $points_type );
            gamipress_update_post_meta( $requirement_id, '_gamipress_trigger_type', 'points-balance' );
            gamipress_update_post_meta( $requirement_id, '_gamipress_achievement_type', $rank_type );
            gamipress_update_post_meta( $requirement_id, '_gamipress_limit_type', 'unlimited' );
            gamipress_update_post_meta( $requirement_id, '_gamipress_optional', '0' );
            gamipress_update_post_meta( $requirement_id, '_gamipress_count', 1 );
            gamipress_update_post_meta( $requirement_id, '_gamipress_limit', 1 );

            gamipress_delete_transient( "gamipress_{$points_type}_based_achievements" );
        }

        if ( isset( $badge_images[ $created ] ) && '' !== $badge_images[ $created ] ) {
            gprbt_save_base64_image_as_post_thumbnail( $rank_id, $badge_images[ $created ] );
        }

        $created_rank_ids[] = $rank_id;
        $created++;
        $current_position++;
    }

    if ( $offset + $created < $rank_count ) {
        $remaining = $rank_count - ( $offset + $created );
        wp_send_json_success( array(
            'run_again' => true,
            'message'   => sprintf( '%d rank remaining', $remaining ),
            'rank_ids'  => $created_rank_ids,
        ) );
    }

    wp_send_json_success( array(
        'message'  => sprintf( '%d points-based ranks have been created.', $rank_count ),
        'rank_ids' => $created_rank_ids,
    ) );
}

/**
 * Check whether a user qualifies for a generated rank.
 *
 * Evaluates all requirements for a rank to determine if the user meets
 * the criteria based on their current points balance.
 *
 * @since 1.0.0
 *
 * @param int $user_id WordPress user ID.
 * @param int $rank_id Rank post ID.
 * @return bool True if user qualifies for the rank, false otherwise.
 */
function gprbt_user_qualifies_for_rank( $user_id, $rank_id ) {
    $requirements = gamipress_get_rank_requirements( $rank_id );

    if ( empty( $requirements ) ) {
        return true;
    }

    foreach ( $requirements as $requirement ) {
        if ( (bool) gamipress_get_post_meta( $requirement->ID, '_gamipress_optional' ) ) {
            continue;
        }

        $trigger_type     = gamipress_get_post_meta( $requirement->ID, '_gamipress_trigger_type' );
        $points_required  = absint( gamipress_get_post_meta( $requirement->ID, '_gamipress_points_required' ) );
        $points_type      = gamipress_get_post_meta( $requirement->ID, '_gamipress_points_type_required' );
        $points_condition = gamipress_get_post_meta( $requirement->ID, '_gamipress_points_condition' );

        if ( empty( $points_condition ) ) {
            $points_condition = 'greater_or_equal';
        }

        if ( 'points-balance' === $trigger_type ) {
            $points_balance = absint( gamipress_get_user_points( $user_id, $points_type ) );

            if ( ! gamipress_number_condition_matches( $points_balance, $points_required, $points_condition ) ) {
                return false;
            }

            continue;
        }

        return false;
    }

    return true;
}

/**
 * AJAX handler for awarding users the generated ranks.
 *
 * Processes users in batches of 50 to avoid timeout issues. For each user,
 * determines which ranks they qualify for based on their points balance
 * and awards all qualifying ranks (not just the highest one).
 *
 * @since 1.0.0
 *
 * Expected POST parameters:
 * - rank_ids     : Array of rank post IDs to consider (required)
 * - batch        : Current batch iteration number (default: 0)
 * - points_type  : Filter users by points type (optional)
 *
 * @return void Outputs JSON response and exits.
 */
function gprbt_ajax_award_users_with_ranks_tool() {
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    if ( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( 'You do not have permission to perform this action.' );
    }

    $rank_ids    = isset( $_POST['rank_ids'] ) && is_array( $_POST['rank_ids'] ) ? array_filter( array_map( 'absint', wp_unslash( $_POST['rank_ids'] ) ) ) : array();
    $batch       = isset( $_POST['batch'] ) ? absint( $_POST['batch'] ) : 0;
    $points_type = isset( $_POST['points_type'] ) ? sanitize_text_field( wp_unslash( $_POST['points_type'] ) ) : '';

    if ( empty( $rank_ids ) ) {
        wp_send_json_error( 'No ranks have been provided to award.' );
    }

    $rank_ids = gprbt_sort_rank_ids_by_menu_order( $rank_ids );
    $rank_type = gamipress_get_post_type( $rank_ids[0] );

    $per_page = 50;
    $args = array(
        'number'      => $per_page,
        'offset'      => $batch * $per_page,
        'fields'      => 'ID',
        'count_total' => true,
    );

    if ( $points_type && isset( gamipress_get_points_types()[ $points_type ] ) ) {
        $args['meta_query'] = array(
            array(
                'key'     => '_gamipress_' . $points_type . '_points',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ),
        );
    }

    $query = new WP_User_Query( $args );
    $users = $query->get_results();

    if ( empty( $users ) ) {
        wp_send_json_success( array(
            'message' => 'Operation completed. No more users to process.',
        ) );
    }

    foreach ( $users as $user_id ) {
        $current_rank = gamipress_get_user_rank( $user_id, $rank_type );
        $current_priority = $current_rank ? gamipress_get_rank_priority( $current_rank ) : 0;
        $best_priority = $current_priority;

        $qualifying_ranks = array();

        foreach ( $rank_ids as $rank_id ) {
            if ( ! gprbt_user_qualifies_for_rank( $user_id, $rank_id ) ) {
                continue;
            }

            $priority = gamipress_get_rank_priority( $rank_id );
            $qualifying_ranks[ $rank_id ] = $priority;

            if ( $priority > $best_priority ) {
                $best_priority = $priority;
            }
        }

        foreach ( $qualifying_ranks as $rank_id => $priority ) {
            if ( $priority > $current_priority ) {
                gamipress_update_user_rank( $user_id, $rank_id );
                $current_priority = $priority;
            }
        }
    }

    $next_batch = $batch + 1;
    $total_users = $query->get_total();

    if ( $total_users > $next_batch * $per_page ) {
        wp_send_json_success( array(
            'run_again' => true,
            'batch'     => $next_batch,
            'message'   => sprintf( 'Processed %d users. Continuing...', min( $total_users, $next_batch * $per_page ) ),
        ) );
    }

    wp_send_json_success( array(
        'message' => sprintf( 'Processed %d users. Award completed.', count( $users ) ),
    ) );
}
