<?php
/**
 * Template Functions
 *
 * @package GamiPress\Referrals\Template_Functions
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
function gamipress_referrals_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/referrals/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/referrals/';
    $file_paths[] =  GAMIPRESS_REFERRALS_DIR . 'templates/';

    return $file_paths;

}
add_filter( 'gamipress_template_paths', 'gamipress_referrals_template_paths' );