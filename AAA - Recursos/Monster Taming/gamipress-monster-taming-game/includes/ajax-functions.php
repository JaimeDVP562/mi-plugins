<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\Monster_Taming_Game\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for update resources
 *
 * @since 1.0.0
 */
function gamipress_monster_taming_game_ajax_update_resources() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check user capabilities
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-monster-taming-game' ) );
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        wp_send_json_error( __( 'Invalid license.', 'gamipress-monster-taming-game' ) );
    }

    $result = gamipress_monster_taming_game_fetch_resources();

    if( is_wp_error( $result ) )
        wp_send_json_error( $result->get_error_message() );

    // Return a success message
    wp_send_json_success( __( 'Resources updated! Refreshing the page...', 'gamipress-monster-taming-game' ) );
}
add_action( 'wp_ajax_gamipress_monster_taming_game_update_resources', 'gamipress_monster_taming_game_ajax_update_resources' );

/**
 * AJAX handler for import points
 *
 * @since 1.0.0
 */
function gamipress_monster_taming_game_ajax_import_points() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check user capabilities
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-monster-taming-game' ) );
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        wp_send_json_error( __( 'Invalid license.', 'gamipress-monster-taming-game' ) );
    }

    // Setup options
    $options = $_REQUEST['options'];
    $config = isset( $options['points_config'] ) ? absint( $options['points_config'] ) : 1;
    $style = isset( $options['points_style'] ) ? $options['points_style'] : 'gachapon';
    $multiplier = isset( $options['points_multiplier'] ) ? absint( $options['points_multiplier'] ) : 1;

    $indexes = array();

    switch( $config ) {
        case 1: $indexes = array( 0 ); break;
        case 5: $indexes = array( 1, 2, 3, 4, 5 ); break;
        case 6: $indexes = array( 0, 1, 2, 3, 4, 5 ); break;
    }

    // TODO: Register point(s) images as attachments
    // TODO: 1 - Move image from RESOURCES_DIR to wp upload dir/year/month (check wp_upload_bits())
    // TODO: 2 - Register attachment and get the ID
    // NOTA, lo ideal seria que en base a los indexes de antes, importar las imagenes requeridas, ver funcion gamipress_monster_taming_gaming_get_points_types_details()
    $attachment_id = gamipress_monster_taming_gaming_move_to_attachment( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . '/points/coin.png' );

    foreach( $indexes as $i ) {
        $type = isset( $options['points_type_'.$i] ) ? $options['points_type_'.$i] : '';

        if( $type === '' ) {
            // Create new, so all true
            $titles = true;
            $slugs = true;
            $images = true;
        } else {
            // Get from checkboxes
            $titles = isset( $options['points_type_' . $i . '_titles'] ) ? true : false;
            $slugs = isset( $options['points_type_' . $i . '_slugs'] ) ? true : false;
            $images = isset( $options['points_type_' . $i . '_images'] ) ? true : false;
        }

        // TODO: Create (or update) the points types (use previous attachment ID for the post thumbnail)
    }




    // Return a success message
    wp_send_json_success( array(
        'message' => __( 'Points types created successfully!', 'gamipress-monster-taming-game' ),
        'run_again' => false,
    ) );

}
add_action( 'wp_ajax_gamipress_monster_taming_game_import_points', 'gamipress_monster_taming_game_ajax_import_points' );

/**
 * AJAX handler for import achievements
 *
 * @since 1.0.0
 */
function gamipress_monster_taming_game_ajax_import_achievements() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check user capabilities
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-monster-taming-game' ) );
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        wp_send_json_error( __( 'Invalid license.', 'gamipress-monster-taming-game' ) );
    }

    // TODO: Leer el CSV, ejemplo usando wp filesystem
    $wp_filesystem = gamipress_monster_taming_game_get_filesystem();
    $data = $wp_filesystem->get_contents( GAMIPRESS_MONSTER_TAMING_GAME_DIR . '/database.csv' );

    // Migrar por grupos

    // Return a success message
    wp_send_json_success( array(
        'message' => __( 'Achievements created successfully!', 'gamipress-monster-taming-game' ),
        'run_again' => false,
    ) );

}
add_action( 'wp_ajax_gamipress_monster_taming_game_import_achievements', 'gamipress_monster_taming_game_ajax_import_achievements' );

/**
 * AJAX handler for the import finish
 *
 * @since 1.0.0
 */
function gamipress_monster_taming_game_ajax_import_finish() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check user capabilities
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-monster-taming-game' ) );
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        wp_send_json_error( __( 'Invalid license.', 'gamipress-monster-taming-game' ) );
    }

    // TODO: Return a success message with links (with target="_blank") to the points type to edit and the achievment type edit screen

    // Return a success message
    wp_send_json_success( array(
        'message' => __( 'Finished!', 'gamipress-monster-taming-game' ),
        'run_again' => false,
    ) );

}
add_action( 'wp_ajax_gamipress_monster_taming_game_import_finish', 'gamipress_monster_taming_game_ajax_import_finish' );