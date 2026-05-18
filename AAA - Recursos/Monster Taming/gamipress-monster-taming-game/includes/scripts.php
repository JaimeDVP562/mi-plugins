<?php
/**
 * Scripts
 *
 * @package     GamiPress\Monster_Taming_Game\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_monster_taming_game_admin_register_scripts( $hook ) {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-monster-taming-game-admin-css', GAMIPRESS_MONSTER_TAMING_GAME_URL . 'assets/css/gamipress-monster-taming-game-admin' . $suffix . '.css', array(), GAMIPRESS_MONSTER_TAMING_GAME_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-monster-taming-game-admin-js', GAMIPRESS_MONSTER_TAMING_GAME_URL . 'assets/js/gamipress-monster-taming-game-admin' . $suffix . '.js', array( 'jquery', 'gamipress-admin-functions-js', 'gamipress-select2-js' ), GAMIPRESS_MONSTER_TAMING_GAME_VER, true );

}
add_action( 'admin_init', 'gamipress_monster_taming_game_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_monster_taming_game_admin_enqueue_scripts( $hook ) {

    //Scripts

    // Tools screen
    if( $hook === 'gamipress_page_gamipress_tools' ) {

        // Enqueue admin functions
        gamipress_enqueue_admin_functions_script();

        // Stylesheets
        wp_enqueue_style( 'gamipress-monster-taming-game-admin-css' );

        // Scripts

        // Localize script
        wp_localize_script( 'gamipress-monster-taming-game-admin-js', 'gamipress_monster_taming_game_admin', array(
            'resources_url' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL,
            'coins' => __( 'Monster Coins', 'gamipress-monster-taming-game' ),
            'gachapon' => array(
                __( 'Blue Gachapon', 'gamipress-monster-taming-game' ),
                __( 'Red Gachapon', 'gamipress-monster-taming-game' ),
                __( 'Purple Gachapon', 'gamipress-monster-taming-game' ),
                __( 'Green Gachapon', 'gamipress-monster-taming-game' ),
                __( 'Yellow Gachapon', 'gamipress-monster-taming-game' ),
            ),
            'potion' => array(
                __( 'Blue Potion', 'gamipress-monster-taming-game' ),
                __( 'Red Potion', 'gamipress-monster-taming-game' ),
                __( 'Purple Potion', 'gamipress-monster-taming-game' ),
                __( 'Green Potion', 'gamipress-monster-taming-game' ),
                __( 'Yellow Potion', 'gamipress-monster-taming-game' ),
            ),
            'food' => array(
                __( 'Fish', 'gamipress-monster-taming-game' ),
                __( 'Meat', 'gamipress-monster-taming-game' ),
                __( 'Vegetable', 'gamipress-monster-taming-game' ),
                __( 'Fruit', 'gamipress-monster-taming-game' ),
                __( 'Lacteal', 'gamipress-monster-taming-game' ),
            ),
        ) );

        wp_enqueue_script( 'gamipress-monster-taming-game-admin-js' );

    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_monster_taming_game_admin_enqueue_scripts', 100 );