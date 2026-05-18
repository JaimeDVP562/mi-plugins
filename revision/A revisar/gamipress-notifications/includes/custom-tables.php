<?php
/**
 * Custom Tables
 *
 * @package     GamiPress\Notifications\Custom_Tables
 * @since       1.6.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Custom Tables
require_once GAMIPRESS_NOTIFICATIONS_DIR . 'includes/custom-tables/notifications.php';

/**
 * Register all GamiPress Notifications Custom DB Tables
 *
 * @since   1.6.0
 *
 * @return void
 */
function gamipress_notifications_register_custom_tables() {

    // Log for debugging
    error_log( "GamiPress Notifications: Registering custom tables" );

    // Notifications Table
    ct_register_table( 'gamipress_notifications', array(
        'show_ui' => false, // Hidden table, not visible in admin
        'show_in_rest' => false,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => gamipress_get_manager_capability(),
        'supports' => array( 'meta' ),
        'schema' => array(
            'notification_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'user_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'user_earning_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'timestamp' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
            'read' => array(
                'type' => 'tinyint',
                'length' => '1',
                'default' => '0',
                'key' => true,
            )
        ),
    ) );

}
add_action( 'ct_init', 'gamipress_notifications_register_custom_tables' );