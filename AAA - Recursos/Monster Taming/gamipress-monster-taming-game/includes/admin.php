<?php
/**
 * Admin
 *
 * @package     GamiPress\Monster_Taming_Game\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_monster_taming_game_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-monster-taming-game-license'] = array(
        'title' => __( 'GamiPress Monster Taming Game', 'gamipress-monster-taming-game' ),
        'fields' => array(
            'gamipress_monster_taming_game_license' => array(
                'name' => __( 'License', 'gamipress-monster-taming-game' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_MONSTER_TAMING_GAME_FILE,
                'item_name' => 'Monster Taming Game',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_monster_taming_game_licenses_meta_boxes' );