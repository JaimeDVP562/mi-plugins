<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Integrations\FluentCart\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register and enqueue admin scripts
 *
 * @since 1.0.0
 *
 * @param string $hook
 */
function automatorwp_fluentcart_admin_enqueue_scripts( $hook ) {

    if( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    wp_enqueue_script(
        'automatorwp-fluentcart',
        AUTOMATORWP_FLUENTCART_URL . 'assets/js/automatorwp-fluentcart.js',
        array( 'jquery' ),
        AUTOMATORWP_FLUENTCART_VER,
        true
    );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_fluentcart_admin_enqueue_scripts' );