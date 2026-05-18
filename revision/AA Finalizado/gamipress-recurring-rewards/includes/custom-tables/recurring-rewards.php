<?php
/**
 * Recurring Rewards
 *
 * @package     GamiPress\Recurring_Rewards\Custom_Tables\Recurring_Rewards
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom Table Labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_recurring_rewards_labels() {

    return array(
        'singular' => __( 'Recurring Reward', 'gamipress-recurring-rewards' ),
        'plural' => __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
        'labels' => array(
            'list_menu_title' => __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
        ),
    );

}
add_filter( 'ct_gamipress_recurring_rewards_labels', 'gamipress_recurring_rewards_labels' );

/**
 * Recurring Rewards Table Columns
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function gamipress_recurring_rewards_columns( $columns ) {

    return array(
        'cb' => $columns['cb'],
        'title' => __( 'Title', 'gamipress-recurring-rewards' ),
        'points_amount' => __( 'Points Amount', 'gamipress-recurring-rewards' ),
        'points_type' => __( 'Points Type', 'gamipress-recurring-rewards' ),
        'cycle' => __( 'Cycle', 'gamipress-recurring-rewards' ),
    );

}
add_filter( 'manage_gamipress_recurring_rewards_columns', 'gamipress_recurring_rewards_columns' );

/**
 * Recurring Rewards Table Custom Column Content
 *
 * @since 1.0.0
 *
 * @param string $column    Current column key.
 * @param int    $object_id Current recurring reward ID.
 *
 * @return void
 */
function gamipress_recurring_rewards_custom_column( $column, $object_id ) {
    global $wpdb;

    if ( ! function_exists( 'gamipress_recurring_rewards_get_table_name' ) ) {
        echo '—';
        return;
    }

    $table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_rewards' );

    if ( empty( $table_name ) ) {
        echo '—';
        return;
    }

    $object = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE recurring_reward_id = %d",
        $object_id
    ) );

    if ( ! $object ) {
        echo '—';
        return;
    }

    switch ( $column ) {
        case 'points_amount':
            echo esc_html( $object->points_amount ? $object->points_amount : '—' );
            break;
        case 'points_type':
            if ( $object->points_type ) {
                $points_types = gamipress_get_points_types();
                echo esc_html( isset( $points_types[$object->points_type] ) ? $points_types[$object->points_type]['plural_name'] : $object->points_type );
            } else {
                echo '—';
            }
            break;
        case 'cycle':
            if ( $object->cycle_amount && $object->cycle_type ) {
                $cycle_labels = array(
                    'day' => __( 'Day', 'gamipress-recurring-rewards' ),
                    'week' => __( 'Week', 'gamipress-recurring-rewards' ),
                    'month' => __( 'Month', 'gamipress-recurring-rewards' ),
                    'year' => __( 'Year', 'gamipress-recurring-rewards' ),
                );
                $label = isset( $cycle_labels[$object->cycle_type] ) ? $cycle_labels[$object->cycle_type] : $object->cycle_type;
                echo esc_html( $object->cycle_amount . ' ' . strtolower( $label ) );
            } else {
                echo '—';
            }
            break;
    }

}
add_action( 'manage_gamipress_recurring_rewards_custom_column', 'gamipress_recurring_rewards_custom_column', 10, 2 );
