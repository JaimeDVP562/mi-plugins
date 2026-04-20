<?php
/**
 * Upgrades loader for AutomatorWP - SendPulse
 * Loads all upgrade routines placed under includes/admin/upgrades/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load available upgrade files
$upgrades_dir = AUTOMATORWP_SENDPULSE_DIR . 'includes/admin/upgrades/';

if ( is_dir( $upgrades_dir ) ) {
    foreach ( scandir( $upgrades_dir ) as $file ) {
        if ( in_array( $file, array( '.', '..' ), true ) ) {
            continue;
        }

        $path = $upgrades_dir . $file;
        if ( is_file( $path ) && preg_match( '/^\d+.*\.php$/', $file ) ) {
            require_once $path;
        }
    }
}
