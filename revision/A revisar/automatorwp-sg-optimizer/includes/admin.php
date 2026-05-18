<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\SGOptimizer\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_sg_optimizer_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_sg_optimizer_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}
