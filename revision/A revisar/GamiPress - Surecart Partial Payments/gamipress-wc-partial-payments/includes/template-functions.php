<?php
/**
 * Template Functions
 *
 * @package GamiPress\WooCommerce\Partial_Payments\Template_Functions
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
function gamipress_wc_partial_payments_template_paths($file_paths) {

    $file_paths[] = trailingslashit(get_stylesheet_directory()) . 'gamipress/wc-partial-payments/';
    $file_paths[] = trailingslashit(get_template_directory()) . 'gamipress/wc-partial-payments/';
    $file_paths[] = GAMIPRESS_WC_PARTIAL_PAYMENTS_DIR . 'templates/';

    return $file_paths;

}

add_filter('gamipress_template_paths', 'gamipress_wc_partial_payments_template_paths');