<?php
/**
 * Plugin Name:       SLP Dynamic Redirects – Parameter Redirect
 * Plugin URI:        https://shortlinkspro.com/add-ons/dynamic-redirects/
 * Description:       Adds a "Parameter" redirect type to the Dynamic Redirects add-on.
 *                    Redirect visitors to different URLs based on GET/POST parameter values.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            ShortLinks Pro
 * Author URI:        https://shortlinkspro.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slp-dynamic-redirects
 *
 * @package SLP_Dynamic_Redirects_Parameter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define( 'SLP_DR_PARAMETER_VERSION',  '1.0.0' );
define( 'SLP_DR_PARAMETER_DIR',      plugin_dir_path( __FILE__ ) );
define( 'SLP_DR_PARAMETER_URL',      plugin_dir_url( __FILE__ ) );

// ---------------------------------------------------------------------------
// Dependency check
// ---------------------------------------------------------------------------

/**
 * Show an admin notice if the Dynamic Redirects add-on is not active.
 */
function slp_dr_parameter_missing_dependency_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                /* translators: %s: plugin name */
                esc_html__( '%s requires the ShortLinks Pro Dynamic Redirects add-on to be installed and active.', 'slp-dynamic-redirects' ),
                '<strong>SLP Dynamic Redirects – Parameter Redirect</strong>'
            );
            ?>
        </p>
    </div>
    <?php
}

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

/**
 * Load the plugin after all other plugins are loaded, so we can safely check
 * whether Dynamic Redirects is available.
 */
function slp_dr_parameter_init() {

    /*
     * The Dynamic Redirects add-on defines the filter 'slp_dynamic_redirects_types'.
     * If that filter doesn't exist the add-on isn't active — bail out.
     */
    if ( ! has_filter( 'slp_dynamic_redirects_types' ) ) {
        add_action( 'admin_notices', 'slp_dr_parameter_missing_dependency_notice' );
        return;
    }

    // Load the main class.
    require_once SLP_DR_PARAMETER_DIR . 'includes/class-parameter-redirect.php';

    // Instantiate it (constructor registers all hooks).
    new SLP_Dynamic_Redirects_Parameter();

    // Enqueue admin script.
    add_action( 'admin_enqueue_scripts', 'slp_dr_parameter_enqueue_admin_assets' );
}
add_action( 'plugins_loaded', 'slp_dr_parameter_init' );

// ---------------------------------------------------------------------------
// Assets
// ---------------------------------------------------------------------------

/**
 * Enqueue admin-side JavaScript for the parameter field UI.
 *
 * Only loaded on the link edit screen to avoid unnecessary overhead.
 *
 * @param string $hook Current admin page hook.
 */
function slp_dr_parameter_enqueue_admin_assets( $hook ) {

    // Only load on post edit screens (where the metabox lives).
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    // Only load on the correct post type.
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'shortlink' ) {
        return;
    }

    wp_enqueue_script(
        'slp-dr-parameter-admin',
        SLP_DR_PARAMETER_URL . 'assets/js/admin-parameter.js',
        array( 'jquery' ),
        SLP_DR_PARAMETER_VERSION,
        true
    );
}
