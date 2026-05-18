<?php
/**
 * Plugin Name:       GamiPress Points-Based Ranks Tool
 * Description:       Adds an administration tool to create ranks based on point thresholds in GamiPress.
 * Version:           1.0.0
 * Author:            GamiPress
 * Text Domain:       gamipress-points-based-ranks-tool
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GPRBT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPRBT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPRBT_PLUGIN_VERSION', '1.0.0' );

add_action( 'plugins_loaded', 'gprbt_init', 11 );

/**
 * Main initialization function.
 *
 * Checks for GamiPress availability and loads required components.
 *
 * @since 1.0.0
 */
function gprbt_init() {
    if ( ! function_exists( 'gamipress_get_points_types' ) || ! function_exists( 'gamipress_get_rank_types' ) ) {
        return;
    }

    require_once GPRBT_PLUGIN_DIR . 'includes/functions.php';

    require_once GPRBT_PLUGIN_DIR . 'includes/ajax-functions.php';
    add_action( 'wp_ajax_gprbt_points_based_ranks_tool', 'gprbt_ajax_points_based_ranks_tool' );
    add_action( 'wp_ajax_gprbt_award_users_with_ranks_tool', 'gprbt_ajax_award_users_with_ranks_tool' );

    require_once GPRBT_PLUGIN_DIR . 'includes/admin/admin.php';
    GPRBT_Admin::instance();
}
