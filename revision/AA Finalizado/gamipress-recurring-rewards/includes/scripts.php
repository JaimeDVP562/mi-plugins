<?php
/**
 * Scripts
 *
 * @package GamiPress\Recurring_Rewards\Scripts
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue admin scripts and styles
 *
 * @since 1.0.0
 */
function gamipress_recurring_rewards_admin_enqueue_scripts() {

    $screen = get_current_screen();

    if ( ! $screen ) {
        return;
    }

    $is_grr_page = (
        $screen->id === 'gamipress_recurring_rewards' ||
        $screen->post_type === 'gamipress_recurring_rewards' ||
        ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'gamipress_recurring_rewards' ) ) ||
        ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'ct-edit_gamipress_recurring_rewards' ) )
    );

    if ( ! $is_grr_page ) {
        return;
    }

    wp_enqueue_script( 'gamipress-select2-js' );
    wp_enqueue_style( 'gamipress-select2-css' );

    wp_enqueue_script(
        'gamipress-recurring-rewards-admin',
        GAMIPRESS_RECURRING_REWARDS_URL . 'assets/js/admin.js',
        array( 'jquery', 'gamipress-select2-js' ),
        GAMIPRESS_RECURRING_REWARDS_VER . '-2',
        true
    );

    wp_enqueue_style(
        'gamipress-recurring-rewards-admin',
        GAMIPRESS_RECURRING_REWARDS_URL . 'assets/css/admin.css',
        array(),
        GAMIPRESS_RECURRING_REWARDS_VER
    );

    wp_localize_script(
        'gamipress-recurring-rewards-admin',
        'grrAdminData',
        array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'grr_admin_nonce' ),
            'strings' => array(
                'selectType'  => __( 'Select type', 'gamipress-recurring-rewards' ),
                'selectFirst' => __( 'Select type first...', 'gamipress-recurring-rewards' ),
                'loading'     => __( 'Loading...', 'gamipress-recurring-rewards' ),
                'select'      => __( 'Select...', 'gamipress-recurring-rewards' ),
                'search'      => __( 'Search...', 'gamipress-recurring-rewards' ),
                'achievement' => __( 'Achievement', 'gamipress-recurring-rewards' ),
                'rank'        => __( 'Rank', 'gamipress-recurring-rewards' ),
                'remove'      => __( 'Remove', 'gamipress-recurring-rewards' ),
            ),
        )
    );

}
add_action( 'admin_enqueue_scripts', 'gamipress_recurring_rewards_admin_enqueue_scripts' );
