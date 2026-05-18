<?php
/**
 * Blocks
 *
 * @package     GamiPress\Daily_Login_Rewards\Blocks
 * @since       1.0.4
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Turn select2 fields into 'post' or 'user' field types
 *
 * @since 1.0.4
 *
 * @param array                 $fields
 * @param GamiPress_Shortcode   $shortcode
 *
 * @return array
 */
function gamipress_daily_login_rewards_block_fields( $fields, $shortcode ) {

    switch ( $shortcode->slug ) {
        case 'gamipress_rewards_calendar':
        case 'gamipress_rewards_current_day':
            // Rewards Calendar ID
            $fields['id']['type'] = 'post';
            $fields['id']['post_type'] = 'rewards-calendar';
            break;
    }

    return $fields;

}
add_filter( 'gamipress_get_block_fields', 'gamipress_daily_login_rewards_block_fields', 11, 2 );
