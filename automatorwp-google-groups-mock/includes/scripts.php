<?php
/**
 * Asset Management (Scripts and Styles) for Google Groups
 *
 * @package     AutomatorWP\GoogleGroups\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register administrative assets
 *
 * @since 1.0.0
 */
function automatorwp_googlegroups_admin_register_scripts() {

    // Register administrative stylesheets
    wp_register_style( 
        'automatorwp-googlegroups-css',
        AUTOMATORWP_GOOGLEGROUPS_URL . 'assets/css/automatorwp-googlegroups.css',
        array(),
        AUTOMATORWP_GOOGLEGROUPS_VER,
        'all'
    );

    // Register administrative javascript
    wp_register_script(
        'automatorwp-googlegroups-admin-js',
        AUTOMATORWP_GOOGLEGROUPS_URL . 'assets/js/automatorwp-googlegroups-admin.js',
        array( 'jquery', 'automatorwp-ajax-selector' ),
        AUTOMATORWP_GOOGLEGROUPS_VER,
        true
    );

}
add_action( 'admin_init', 'automatorwp_googlegroups_admin_register_scripts' );

/**
 * Enqueue administrative assets with environment shielding
 *
 * @since 1.0.0
 * @param string $hook The current admin page hook.
 */
function automatorwp_googlegroups_admin_enqueue_scripts( $hook ) {

    // Contextual shielding: list of permitted administrative screens 
    $allowed_hooks = array( 'automatorwp_settings', 'post.php', 'post-new.php' );
    $is_allowed    = false;
    
    foreach ( $allowed_hooks as $screen ) {
        if ( strpos( $hook, $screen ) !== false ) {
            $is_allowed = true;
            break;
        }
    }

    if ( ! $is_allowed ) {
        return;
    }

    // Execution shielding: ensure asset loading only for automation post types 
    if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
        
        global $post;
        
        if ( ! isset( $post ) || 'automatorwp_automation' !== $post->post_type ) {
            return;
        }
    }

    wp_enqueue_style( 'automatorwp-googlegroups-css' );
    wp_enqueue_script( 'automatorwp-googlegroups-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_googlegroups_admin_enqueue_scripts', 100 );
