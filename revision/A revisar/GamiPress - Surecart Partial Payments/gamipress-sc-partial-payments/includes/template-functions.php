<?php
/**
 * Template Functions
 *
 * @package GamiPress\SureCart\Partial_Payments\Template_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @param array $file_paths
 *
 * @return array
 * @since 1.0.0
 *
 */
function gamipress_sc_partial_payments_template_paths($file_paths) {

    $file_paths[] = trailingslashit(get_stylesheet_directory()) . 'gamipress/sc-partial-payments/';
    $file_paths[] = trailingslashit(get_template_directory()) . 'gamipress/sc-partial-payments/';
    $file_paths[] = GAMIPRESS_SC_PARTIAL_PAYMENTS_DIR . 'templates/';

    return $file_paths;

}

add_filter('gamipress_template_paths', 'gamipress_sc_partial_payments_template_paths');
