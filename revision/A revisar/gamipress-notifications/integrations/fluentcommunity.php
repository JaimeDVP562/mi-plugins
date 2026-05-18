<?php
/**
 * Scripts
 *
 * @package     GamiPress\Notifications\Integrations\FluentCommunity
 * @since       1.5.4
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add CSS styles
 *
 * @since       1.5.4
 * @return      void
 */
function gamipress_notifications_styles_to_fluent_community() {

    wp_enqueue_style( 'gamipress-notifications-css');
    gamipress_notifications_enqueue_scripts();
    wp_print_styles( ['gamipress-notifications-css'] );

}
add_action( 'fluent_community/portal_head', 'gamipress_notifications_styles_to_fluent_community' );

/**
 * Add JS
 *
 * @since       1.5.4
 * @return      void
 */
function gamipress_add_notifications_js_to_fluent_community() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    
    wp_enqueue_script('jquery');
    
    // Load Notifications scripts
    gamipress_notifications_enqueue_scripts();

    wp_register_script( 'gamipress-notifications-fluentcommunity-js', GAMIPRESS_NOTIFICATIONS_URL . 'integrations/js/gamipress-notifications-fluentcommunity' . $suffix . '.js', array( 'jquery', 'gamipress-notifications-js' ), GAMIPRESS_NOTIFICATIONS_VER, true );

    wp_enqueue_script( 'gamipress-notifications-fluentcommunity-js' );
    
    wp_print_scripts(['jquery', 'gamipress-notifications-js', 'gamipress-notifications-fluentcommunity-js']);
}
add_action( 'fluent_community/portal_footer', 'gamipress_add_notifications_js_to_fluent_community', 60 );