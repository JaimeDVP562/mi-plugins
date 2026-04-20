<?php
/**
 * 0001 - Migrate SendPulse options to autoload='no'
 * Placed under includes/admin/upgrades/ to follow repository upgrade structure.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Integrate the migration into AutomatorWP's upgrade flow.
 * This will be called by the `automatorwp_process_upgrades` filter.
 *
 * @param string $stored_version The stored AutomatorWP version (not modified here)
 * @return string                Unmodified stored version
 */
function automatorwp_sendpulse_process_upgrades( $stored_version ) {
    // Only run in admin and when current user can manage options
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return $stored_version;
    }

    // Only run once
    if ( get_option( 'automatorwp_sendpulse_autoload_migrated', false ) ) {
        return $stored_version;
    }

    global $wpdb;

    $option_names = array(
        'automatorwp_sendpulse_application_id',
        'automatorwp_sendpulse_application_secret',
        'automatorwp_sendpulse_access_token',
        'automatorwp_sendpulse_page_id',
        'automatorwp_sendpulse_access_valid',
    );

    foreach ( $option_names as $opt ) {
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $opt ) );
        if ( $exists ) {
            $wpdb->update( $wpdb->options, array( 'autoload' => 'no' ), array( 'option_name' => $opt ), array( '%s' ), array( '%s' ) );
        }
    }

    // Mark migration as done using the plugin helper so the marker is also non-autoloaded
    if ( function_exists( 'automatorwp_sendpulse_set_option_noautoload' ) ) {
        automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_autoload_migrated', '1' );
    } else {
        // Fallback: ensure migration flag exists
        add_option( 'automatorwp_sendpulse_autoload_migrated', '1', '', 'no' );
    }

    return $stored_version;
}

add_filter( 'automatorwp_process_upgrades', 'automatorwp_sendpulse_process_upgrades', 10 );
