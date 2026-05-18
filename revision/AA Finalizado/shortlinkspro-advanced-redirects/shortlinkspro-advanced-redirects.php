<?php
/**
 * Plugin Name: ShortLinksPro - Advanced Redirects
 * Plugin URI:  https://gitlab.com/rubengc/practicas-web/-/work_items/122
 * Description: Add-on oficial para ShortLinksPro que añade tipos de redirección avanzados (como el Pixel 1x1).
 * Version:     1.0.0
 * Author:      Andrea Borrás y Paula Lei Gimeno
 * Text Domain: shortlinkspro-advanced
 */

// Salir si se accede directamente
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the add-on components
require_once plugin_dir_path( __FILE__ ) . 'includes/pixel.php';

/**
 * Flush rewrite rules on activation
 */
register_activation_hook( __FILE__, 'slp_advanced_flush_rewrite_rules' );
function slp_advanced_flush_rewrite_rules() {
    flush_rewrite_rules();
}