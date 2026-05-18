<?php
/**
 * Plugin Name:     ShortLinksPro - Auto-Create Links
 * Plugin URI:      https://shortlinkspro.com/
 * Description:     Add-on for ShortLinks Pro that automatically generates short links for posts, pages, and custom post types.
 * Version:         1.0.0
 * Author:          ShortLinks Pro
 * Author URI:      https://shortlinkspro.com/
 * Text Domain:     shortlinkspro-auto-create-links
 * Requires PHP:    7.0
 *
 * @package         ShortLinksPro\Auto_Create_Links
 * @author          ShortLinksPro <contact@shortlinkspro.com>
 * @copyright       Copyright (c) ShortLinks Pro
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add-on constants
 */
define( 'SLP_AUTO_CREATE_LINKS_VER', '1.0.0' );
define( 'SLP_AUTO_CREATE_LINKS_FILE', __FILE__ );
define( 'SLP_AUTO_CREATE_LINKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLP_AUTO_CREATE_LINKS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if ShortLinks Pro is active
 *
 * We verify this by checking if the main ShortLinksPro class exists.
 * If it doesn't, it means the base plugin is not installed/active.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function slp_auto_create_links_is_slp_active() {
    return class_exists( 'ShortLinksPro' );
}

/**
 * Show an admin notice if ShortLinks Pro is not active
 *
 * @since 1.0.0
 */
function slp_auto_create_links_admin_notice_missing_slp() {

    // Only show the notice if SLP is not active
    if( slp_auto_create_links_is_slp_active() ) {
        return;
    }

    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php echo wp_kses_post( sprintf(
                /* translators: %1$s: Opening strong tag. %2$s: Closing strong tag. %3$s: Opening strong tag. %4$s: Closing strong tag. */
                __( '%1$sShortLinksPro - Auto-Create Links%2$s requires %3$sShortLinks Pro%4$s to be installed and active.', 'shortlinkspro-auto-create-links' ),
                '<strong>', '</strong>',
                '<strong>', '</strong>'
            ) ); ?>
        </p>
    </div>
    <?php

}
add_action( 'admin_notices', 'slp_auto_create_links_admin_notice_missing_slp' );

/**
 * Initialize the add-on
 *
 * Hooked into 'shortlinkspro_init' which is fired by the base ShortLinks Pro plugin.
 * This ensures that all SLP core functions and classes are available when
 * we load our add-on files.
 *
 * @since 1.0.0
 */
function slp_auto_create_links_init() {

    // Include AJAX functions (needed for both admin and admin-ajax.php requests)
    require_once SLP_AUTO_CREATE_LINKS_DIR . 'includes/ajax-auto-create-links.php';

    if( is_admin() ) {
        // Include admin settings
        require_once SLP_AUTO_CREATE_LINKS_DIR . 'includes/admin/settings/auto-create-links.php';

        // Include meta boxes
        require_once SLP_AUTO_CREATE_LINKS_DIR . 'includes/admin/meta-boxes/auto-create-links.php';
    }

    // Enqueue scripts (only after SLP is confirmed loaded)
    add_action( 'admin_enqueue_scripts', 'slp_auto_create_links_admin_enqueue_scripts' );

}
add_action( 'shortlinkspro_init', 'slp_auto_create_links_init' );

/**
 * Enqueue admin scripts and styles
 *
 * This is only hooked AFTER 'shortlinkspro_init' fires, so we are guaranteed
 * that all SLP functions (like shortlinkspro_get_admin_nonce()) are available.
 *
 * @since 1.0.0
 *
 * @param string $hook The current admin page hook
 */
function slp_auto_create_links_admin_enqueue_scripts( $hook ) {

    // Fix for incorrect prefix that SLP sometimes uses
    $hook = str_replace( 'shortlinks-pro', 'shortlinkspro', $hook );

    // Only load on the SLP Settings page
    if( $hook !== 'shortlinkspro_page_shortlinkspro_settings' ) {
        return;
    }

    // Register and enqueue the Auto-Create Links JS
    wp_register_script(
        'shortlinkspro-auto-create-links-js',
        SLP_AUTO_CREATE_LINKS_URL . 'assets/js/shortlinkspro-auto-create-links.js',
        array( 'jquery' ),
        SLP_AUTO_CREATE_LINKS_VER,
        true
    );

    // Localize with data the JS needs
    wp_localize_script( 'shortlinkspro-auto-create-links-js', 'shortlinkspro_auto_create', array(
        'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                 => shortlinkspro_get_admin_nonce(),
        'processing_text'       => __( 'Processing...', 'shortlinkspro-auto-create-links' ),
        'confirm_delete_text'   => __( 'Are you sure you want to delete all auto-created links for this post type? This action cannot be undone.', 'shortlinkspro-auto-create-links' ),
        'error_text'            => __( 'An error occurred. Please try again.', 'shortlinkspro-auto-create-links' ),
        'created_text'          => __( 'links created successfully!', 'shortlinkspro-auto-create-links' ),
        'updated_text'          => __( 'links updated successfully!', 'shortlinkspro-auto-create-links' ),
        'deleted_text'          => __( 'links deleted successfully!', 'shortlinkspro-auto-create-links' ),
    ) );

    wp_enqueue_script( 'shortlinkspro-auto-create-links-js' );

}
