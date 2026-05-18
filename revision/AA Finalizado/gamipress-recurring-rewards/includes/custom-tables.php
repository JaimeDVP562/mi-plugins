<?php
/**
 * Custom Tables
 *
 * @package     GamiPress\Recurring_Rewards\Custom_Tables
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/custom-tables/recurring-rewards.php';
require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/custom-tables/recurring-reward-users.php';

/**
 * Register all plugin Custom DB Tables
 *
 * @since  1.0.0
 *
 * @return void
 */
function gamipress_recurring_rewards_register_custom_tables() {

    $manager_capability = function_exists( 'gamipress_get_manager_capability' )
        ? gamipress_get_manager_capability()
        : 'manage_options';

    ct_register_table( 'gamipress_recurring_rewards', array(
        'singular' => __( 'Recurring Reward', 'gamipress-recurring-rewards' ),
        'plural' => __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
        'labels' => array(
            'list_menu_title' => __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
        ),
        'show_ui' => true,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => $manager_capability,
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'parent_slug' => 'gamipress',
            ),
            'add' => true,
            'edit' => array(
                'show_in_menu' => false,
            ),
        ),
        'schema' => array(
            'recurring_reward_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'title' => array(
                'type' => 'text',
            ),
            'points_amount' => array(
                'type' => 'bigint',
                'length' => '20',
            ),
            'points_type' => array(
                'type' => 'varchar',
                'length' => '50',
            ),
            'cycle_amount' => array(
                'type' => 'int',
                'length' => '11',
            ),
            'cycle_type' => array(
                'type' => 'varchar',
                'length' => '20',
            ),
            'cycle_day' => array(
                'type' => 'int',
                'length' => '2',
            ),
            'cycle_month_day' => array(
                'type' => 'int',
                'length' => '2',
            ),
            'requirements' => array(
                'type' => 'longtext',
            ),
        ),
    ) );

    ct_register_table( 'gamipress_recurring_reward_users', array(
        'show_ui' => false,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => $manager_capability,
        'supports' => array( 'meta' ),
        'schema' => array(
            'recurring_reward_user_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'recurring_reward_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'user_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'date_unlocked' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
            'active' => array(
                'type' => 'tinyint',
                'length' => '1',
                'default' => '1',
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_recurring_rewards_register_custom_tables', 5 );
