<?php
/**
 * Upgrades
 *
 * @package     AutomatorWP\Schedule_Actions\Upgrades
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check for any available upgrade
 *
 * @since 1.1.0
 */
function automatorwp_schedule_actions_check_upgrades() {

    global $wpdb;

    // Get the stored version
    $stored_version = get_option( 'automatorwp_schedule_actions_version', '1.0.0' );

    // 1.1.0 Upgrade
    if ( version_compare( $stored_version, '1.1.0', '<' ) ) {

        $ct = ct_setup_table( 'automatorwp_actions' );

        $actions_meta = $ct->meta->db->table_name;

        // The metas that will be renamed
        $meta_keys = array(
            'schedule_action' => 'delay_action',
            'schedule_action_amount' => 'delay_action_amount',
            'schedule_action_period' => 'delay_action_period',
        );

        // Update metas in database
        foreach( $meta_keys as $old_key => $new_key ) {
            $wpdb->query( "UPDATE {$actions_meta} SET meta_key='{$new_key}' WHERE meta_key='{$old_key}'" );
        }

        ct_reset_setup_table();

    }

    // Update the stored version
    if( $stored_version !== AUTOMATORWP_SCHEDULE_ACTIONS_VER ) {
        update_option( 'automatorwp_schedule_actions_version', AUTOMATORWP_SCHEDULE_ACTIONS_VER );
    }

}
add_action( 'init', 'automatorwp_schedule_actions_check_upgrades' );