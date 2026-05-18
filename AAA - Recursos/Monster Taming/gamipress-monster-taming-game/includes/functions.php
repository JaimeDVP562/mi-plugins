<?php
/**
 * Functions
 *
 * @package     GamiPress\Monster_Taming_Game\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_monster_taming_gaming_get_points_types_details( $style = '' ) {
    return array(
        0 => array(
            'singular' => __( 'Monster Coin', 'gamipress-monster-taming-game' ),
            'plural' => __( 'Monster Coins', 'gamipress-monster-taming-game' ),
            'slug' => 'monster-coins',
            'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/coin.png',
        ),
        1 => gamipress_monster_taming_gaming_get_styled_points_type( $style, 0 ),
        2 => gamipress_monster_taming_gaming_get_styled_points_type( $style, 1 ),
        3 => gamipress_monster_taming_gaming_get_styled_points_type( $style, 2 ),
        4 => gamipress_monster_taming_gaming_get_styled_points_type( $style, 3 ),
        5 => gamipress_monster_taming_gaming_get_styled_points_type( $style, 4 ),
    );
}

function gamipress_monster_taming_gaming_get_styled_points_type( $style, $index ) {

    $points_types = array(
        'gachapon' => array(
            0 => array(
                'singular' => __( 'Blue Gachapon', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Blue Gachapons', 'gamipress-monster-taming-game' ),
                'slug' => 'blue-gachapons',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/gachapon/1.png',
            ),
            1 => array(
                'singular' => __( 'Red Gachapon', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Red Gachapons', 'gamipress-monster-taming-game' ),
                'slug' => 'red-gachapons',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/gachapon/2.png',
            ),
            2 => array(
                'singular' => __( 'Purple Gachapon', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Purple Gachapons', 'gamipress-monster-taming-game' ),
                'slug' => 'purple-gachapons',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/gachapon/3.png',
            ),
            3 => array(
                'singular' => __( 'Green Gachapon', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Green Gachapons', 'gamipress-monster-taming-game' ),
                'slug' => 'green-gachapons',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/gachapon/4.png',
            ),
            4 => array(
                'singular' => __( 'Yellow Gachapon', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Yellow Gachapons', 'gamipress-monster-taming-game' ),
                'slug' => 'yellow-gachapons',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/gachapon/5.png',
            ),
        ),
        'potion' => array(
            0 => array(
                'singular' => __( 'Blue Potion', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Blue Potions', 'gamipress-monster-taming-game' ),
                'slug' => 'blue-potions',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/potion/1.png',
            ),
            1 => array(
                'singular' => __( 'Red Potion', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Red Potions', 'gamipress-monster-taming-game' ),
                'slug' => 'red-potions',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/potion/2.png',
            ),
            2 => array(
                'singular' => __( 'Purple Potion', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Purple Potions', 'gamipress-monster-taming-game' ),
                'slug' => 'purple-potions',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/potion/3.png',
            ),
            3 => array(
                'singular' => __( 'Green Potion', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Green Potions', 'gamipress-monster-taming-game' ),
                'slug' => 'green-potions',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/potion/4.png',
            ),
            4 => array(
                'singular' => __( 'Yellow Potion', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Yellow Potions', 'gamipress-monster-taming-game' ),
                'slug' => 'yellow-potions',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/potion/5.png',
            ),
        ),
        'food' => array(
            0 => array(
                'singular' => __( 'Fish', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Fish', 'gamipress-monster-taming-game' ),
                'slug' => 'fish',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/food/1.png',
            ),
            1 => array(
                'singular' => __( 'Meat', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Meat', 'gamipress-monster-taming-game' ),
                'slug' => 'meat',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/food/2.png',
            ),
            2 => array(
                'singular' => __( 'Vegetable', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Vegetables', 'gamipress-monster-taming-game' ),
                'slug' => 'vegetables',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/food/3.png',
            ),
            3 => array(
                'singular' => __( 'Fruit', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Fruits', 'gamipress-monster-taming-game' ),
                'slug' => 'Fruits',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/food/4.png',
            ),
            4 => array(
                'singular' => __( 'Lacteal', 'gamipress-monster-taming-game' ),
                'plural' => __( 'Lacteal', 'gamipress-monster-taming-game' ),
                'slug' => 'lacteal',
                'image' => GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/food/5.png',
            ),
        ),
    );

    return $points_types[$style][$index];

}