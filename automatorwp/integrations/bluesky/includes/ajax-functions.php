<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Bluesky\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_bluesky_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'automatorwp' ) );
    }

    $prefix = 'automatorwp_bluesky_';

    $user_handle = automatorwp_bluesky_validate_name_account( sanitize_text_field( $_POST["user_handle"] ) );
    $user_password = sanitize_text_field( $_POST['user_password'] );

    if( empty( $user_handle ) || empty( $user_password ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Bluesky', 'automatorwp' ) ) );
        return;
    }
    
    $status = automatorwp_bluesky_check_settings_status(['user_handle' => $user_handle, 'user_password' => $user_password]);

    if ( empty( $status ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API user_handle and API user_password
    $settings[$prefix . 'user_handle'] = $user_handle;
    $settings[$prefix . 'user_password'] = $user_password;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-bluesky' );

    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with Bluesky', 'automatorwp' ),
        'redirect_url' => $admin_url
    ) );
}
add_action( 'wp_ajax_automatorwp_bluesky_authorize',  'automatorwp_bluesky_ajax_authorize' );