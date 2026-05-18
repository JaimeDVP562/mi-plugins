<?php
/**
 * AJAX Functions
 *
 * @package GamiPress\Recurring_Rewards\Ajax_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Search posts for select2
 *
 * @since 1.0.0
 */
function gamipress_recurring_rewards_search_posts() {

    check_ajax_referer( 'grr_admin_nonce', '_wpnonce' );

    if ( ! function_exists( 'gamipress_recurring_rewards_current_user_can_manage' ) || ! gamipress_recurring_rewards_current_user_can_manage() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-recurring-rewards' ), 403 );
    }

    $request_data = wp_unslash( $_REQUEST );

    $type = isset( $request_data['type'] ) ? sanitize_text_field( $request_data['type'] ) : '';
    $search = isset( $request_data['q'] ) ? sanitize_text_field( $request_data['q'] ) : '';

    if ( empty( $type ) ) {
        wp_send_json( array() );
        return;
    }

    $results = array();

    if ( $type === 'achievement' || $type === 'rank' ) {
        $items = $type === 'achievement' ? gamipress_get_achievements() : gamipress_get_ranks();
        $parents = array();
        $ungrouped = array();

        foreach ( $items as $item ) {
            if ( !empty($search) && stripos($item->post_title, $search) === false ) {
                continue;
            }

            $parent_id = absint( isset( $item->post_parent ) ? $item->post_parent : 0 );
            if ( $parent_id ) {
                if ( ! isset( $parents[ $parent_id ] ) ) {
                    $parent_post = get_post( $parent_id );
                    if ( $parent_post ) {
                        $parents[ $parent_id ] = array(
                            'text' => $parent_post->post_title,
                            'children' => array(),
                        );
                    }
                }

                if ( isset( $parents[ $parent_id ] ) ) {
                    $parents[ $parent_id ]['children'][] = array(
                        'id' => $item->ID,
                        'text' => $item->post_title,
                    );
                } else {
                    $ungrouped[] = array(
                        'id' => $item->ID,
                        'text' => $item->post_title,
                    );
                }
            } else {
                $ungrouped[] = array(
                    'id' => $item->ID,
                    'text' => $item->post_title,
                );
            }
        }

        $sorted_parents = array_values( $parents );
        usort( $sorted_parents, function( $a, $b ) {
            return strcmp( $a['text'], $b['text'] );
        } );

        foreach ( $sorted_parents as $group ) {
            if ( empty( $group['children'] ) ) {
                continue;
            }
            usort( $group['children'], function( $a, $b ) {
                return strcmp( $a['text'], $b['text'] );
            } );
            $results[] = array(
                'id' => '',
                'text' => $group['text'],
                'disabled' => true,
            );
            $results = array_merge( $results, $group['children'] );
        }

        if ( ! empty( $ungrouped ) ) {
            usort( $ungrouped, function( $a, $b ) {
                return strcmp( $a['text'], $b['text'] );
            } );
            $results = array_merge( $ungrouped, $results );
        }
    }

    wp_send_json( $results );

}
add_action( 'wp_ajax_gamipress_recurring_rewards_search_posts', 'gamipress_recurring_rewards_search_posts' );
