<?php
/**
 * Template Functions
 *
 * @package GamiPress\Daily_Login_Rewards\Template_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @since 1.0.0
 *
 * @param array $file_paths
 *
 * @return array
 */
function gamipress_daily_login_rewards_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/daily_login_rewards/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/daily_login_rewards/';
    $file_paths[] =  GAMIPRESS_DAILY_LOGIN_REWARDS_DIR . 'templates/';

    return $file_paths;

}
add_filter( 'gamipress_template_paths', 'gamipress_daily_login_rewards_template_paths' );