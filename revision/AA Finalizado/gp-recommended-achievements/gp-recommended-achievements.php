<?php
/**
 * Plugin Name: [GP] Recommended Achievements
 * Plugin URI:  https://github.com/
 * Description: Muestra un listado de logros recomendados cuando se visita un logro (página o shortcode single achievement).
 * Version:     1.0.0
 * Author:      GamiPress
 * License:     GPL-2.0+
 * Text Domain: gp-recommended-achievements
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// Constants
// -------------------------------------------------------------------------
define( 'GP_RA_VERSION',     '1.0.0' );
define( 'GP_RA_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'GP_RA_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'GP_RA_PLUGIN_FILE', __FILE__ );

// -------------------------------------------------------------------------
// Load files
// -------------------------------------------------------------------------
require_once GP_RA_PLUGIN_DIR . 'includes/settings.php';
require_once GP_RA_PLUGIN_DIR . 'includes/query.php';
require_once GP_RA_PLUGIN_DIR . 'includes/template.php';
require_once GP_RA_PLUGIN_DIR . 'includes/hooks.php';

// -------------------------------------------------------------------------
// Activation / Deactivation
// -------------------------------------------------------------------------
register_activation_hook( __FILE__, 'gp_ra_activate' );
function gp_ra_activate() {
    // Set default options on first activation
    if ( false === get_option( 'gp_ra_max_achievements' ) ) {
        update_option( 'gp_ra_max_achievements', 3 );
    }
    if ( false === get_option( 'gp_ra_same_type' ) ) {
        update_option( 'gp_ra_same_type', '0' );
    }
}

register_deactivation_hook( __FILE__, 'gp_ra_deactivate' );
function gp_ra_deactivate() {
    // Nothing to do on deactivation
}
