<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Manual_Triggers\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin AJAX handler: Run a manual trigger from the admin panel ("Run Now" button)
 *
 * @since 1.0.0
 */
function automatorwp_manual_trigger_admin_run() {

    // Security: verify nonce
    check_ajax_referer( 'automatorwp_manual_trigger_admin', 'nonce' );

    // Security: check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'automatorwp-manual-triggers' ) ) );
    }

    $trigger_id = isset( $_POST['trigger_id'] ) ? absint( $_POST['trigger_id'] ) : 0;
    $user_id    = isset( $_POST['user_id'] )    ? absint( $_POST['user_id'] )    : 0;

    if ( $trigger_id === 0 ) {
        wp_send_json_error( array( 'message' => __( 'No trigger ID provided.', 'automatorwp-manual-triggers' ) ) );
    }

    // If no user ID given, use the current admin user
    if ( $user_id === 0 ) {
        $user_id = get_current_user_id();
    }

    // Run the trigger
    automatorwp_run_trigger( $trigger_id, $user_id );

    wp_send_json_success( array( 'message' => __( 'Trigger executed successfully.', 'automatorwp-manual-triggers' ) ) );

}
add_action( 'wp_ajax_automatorwp_manual_trigger_admin_run', 'automatorwp_manual_trigger_admin_run' );
