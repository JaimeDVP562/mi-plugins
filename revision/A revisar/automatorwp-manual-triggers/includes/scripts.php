<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Manual_Triggers\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue admin scripts and styles
 *
 * @since 1.0.0
 *
 * @param string $hook The current admin page hook
 */
function automatorwp_manual_triggers_admin_enqueue_scripts( $hook ) {

    // Only load on AutomatorWP automation edit screens
    global $post_type;

    if ( $post_type !== 'automatorwp-automation' && strpos( $hook, 'automatorwp' ) === false ) {
        // Also allow on post edit screens that might contain automations
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
            return;
        }
    }

    // Admin CSS
    wp_enqueue_style(
        'automatorwp-manual-triggers-admin',
        AUTOMATORWP_MANUAL_TRIGGERS_URL . 'assets/css/admin.css',
        array(),
        AUTOMATORWP_MANUAL_TRIGGERS_VER
    );

    // Admin JS
    wp_enqueue_script(
        'automatorwp-manual-triggers-admin',
        AUTOMATORWP_MANUAL_TRIGGERS_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        AUTOMATORWP_MANUAL_TRIGGERS_VER,
        true
    );

    // Localize script with i18n strings and data
    wp_localize_script( 'automatorwp-manual-triggers-admin', 'automatorwp_manual_triggers', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'automatorwp_manual_trigger_admin' ),
        'i18n'     => array(
            // Run Now section
            'run_now'               => __( 'Run Now', 'automatorwp-manual-triggers' ),
            'user_id'               => __( 'User ID:', 'automatorwp-manual-triggers' ),
            'user_id_placeholder'   => __( 'Leave empty for current user', 'automatorwp-manual-triggers' ),
            'running'               => __( 'Running...', 'automatorwp-manual-triggers' ),
            'done'                  => __( 'Done!', 'automatorwp-manual-triggers' ),
            'error'                 => __( 'Error', 'automatorwp-manual-triggers' ),

            // Code example section
            'code_example'                  => __( 'Code Example', 'automatorwp-manual-triggers' ),
            'code_example_desc_logged_in'   => __( 'Use the following PHP code to launch this trigger programmatically:', 'automatorwp-manual-triggers' ),
            'code_example_desc_anonymous'   => __( 'Use the following PHP code to launch this trigger programmatically (no user ID needed):', 'automatorwp-manual-triggers' ),
            'code_basic_usage'              => __( 'Basic usage', 'automatorwp-manual-triggers' ),
            'code_current_user'             => __( 'Uses the current logged-in user', 'automatorwp-manual-triggers' ),
            'code_specific_user'            => __( 'With a specific user ID', 'automatorwp-manual-triggers' ),
            'code_user_123'                 => __( 'Runs for user with ID 123', 'automatorwp-manual-triggers' ),

            // Shortcode section
            'shortcode'                     => __( 'Shortcode', 'automatorwp-manual-triggers' ),
            'shortcode_desc_logged_in'      => __( 'Use this shortcode to display a button/link that fires this trigger. Use user="" for the current logged-in user or user="123" for a specific user:', 'automatorwp-manual-triggers' ),
            'shortcode_desc_anonymous'      => __( 'Use this shortcode to display a button/link that fires this trigger (no user ID needed):', 'automatorwp-manual-triggers' ),
            'shortcode_current_user'        => __( 'For the current logged-in user:', 'automatorwp-manual-triggers' ),
            'shortcode_specific_user'       => __( 'For a specific user (e.g. ID 123):', 'automatorwp-manual-triggers' ),
            'run_label'                     => __( 'Run', 'automatorwp-manual-triggers' ),
        ),
    ) );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_manual_triggers_admin_enqueue_scripts' );
