<?php
/**
 * Shortcodes
 *
 * @package     AutomatorWP\Manual_Triggers\Shortcodes
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [automatorwp_manual_trigger]
 *
 * Renders a button or link that, when clicked, fires a manual trigger.
 *
 * Attributes:
 * - trigger (int)    Required. The automation trigger post ID.
 * - user    (string) Optional. The user ID. Use "" (empty) for the current logged-in user,
 *                    or a specific numeric ID like "123". Default: "" (current user).
 * - label   (string) Optional. The button/link text. Default: "Run".
 * - type    (string) Optional. "button" or "link". Default: "button".
 * - class   (string) Optional. Extra CSS classes.
 *
 * Usage examples:
 *   [automatorwp_manual_trigger trigger="228" user="" label="Run Automation"]
 *   [automatorwp_manual_trigger trigger="228" user="123" label="Run for User 123"]
 *   [automatorwp_manual_trigger trigger="228" label="Run" type="link"]
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function automatorwp_manual_trigger_shortcode( $atts ) {

    $atts = shortcode_atts( array(
        'trigger' => 0,
        'user'    => '',
        'label'   => __( 'Run', 'automatorwp-manual-triggers' ),
        'type'    => 'button',
        'class'   => '',
    ), $atts, 'automatorwp_manual_trigger' );

    $trigger_id = absint( $atts['trigger'] );

    // Bail if no trigger ID provided
    if ( $trigger_id === 0 ) {
        return '';
    }

    // Determine the user ID
    $user_id = 0;

    if ( $atts['user'] === '' ) {
        // Empty string means use the current logged-in user
        $user_id = get_current_user_id();
    } else {
        // Specific user ID provided
        $user_id = absint( $atts['user'] );
    }

    // Build the element
    $element_class = 'automatorwp-manual-trigger-btn';

    if ( ! empty( $atts['class'] ) ) {
        $element_class .= ' ' . sanitize_html_class( $atts['class'] );
    }

    // We need a nonce for security
    $nonce = wp_create_nonce( 'automatorwp_manual_trigger_' . $trigger_id );

    // Data attributes for the JS handler
    $data_attrs = sprintf(
        'data-trigger="%d" data-user="%d" data-nonce="%s"',
        $trigger_id,
        $user_id,
        esc_attr( $nonce )
    );

    $label = esc_html( $atts['label'] );

    if ( $atts['type'] === 'link' ) {
        $html = sprintf(
            '<a href="#" class="%s" %s>%s</a>',
            esc_attr( $element_class ),
            $data_attrs,
            $label
        );
    } else {
        $html = sprintf(
            '<button type="button" class="%s" %s>%s</button>',
            esc_attr( $element_class ),
            $data_attrs,
            $label
        );
    }

    // Enqueue the front-end script (only once)
    automatorwp_manual_trigger_enqueue_frontend_scripts();

    return $html;

}
add_shortcode( 'automatorwp_manual_trigger', 'automatorwp_manual_trigger_shortcode' );

/**
 * Enqueue front-end scripts for the shortcode
 *
 * @since 1.0.0
 */
function automatorwp_manual_trigger_enqueue_frontend_scripts() {

    // Only enqueue once
    if ( wp_script_is( 'automatorwp-manual-triggers-shortcode', 'enqueued' ) ) {
        return;
    }

    // Frontend JS
    wp_enqueue_script(
        'automatorwp-manual-triggers-shortcode',
        AUTOMATORWP_MANUAL_TRIGGERS_URL . 'assets/js/shortcode.js',
        array(),
        AUTOMATORWP_MANUAL_TRIGGERS_VER,
        true
    );

    // Localize script with AJAX URL and i18n strings
    wp_localize_script( 'automatorwp-manual-triggers-shortcode', 'automatorwp_manual_triggers_shortcode', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'i18n'     => array(
            'running' => __( 'Running...', 'automatorwp-manual-triggers' ),
            'done'    => __( 'Done!', 'automatorwp-manual-triggers' ),
            'error'   => __( 'Error', 'automatorwp-manual-triggers' ),
        ),
    ) );

}

/**
 * AJAX handler for the front-end shortcode trigger
 *
 * Handles both logged-in and non-logged-in (anonymous) users.
 *
 * @since 1.0.0
 */
function automatorwp_manual_trigger_ajax_handler() {

    $trigger_id = isset( $_POST['trigger_id'] ) ? absint( $_POST['trigger_id'] ) : 0;
    $user_id    = isset( $_POST['user_id'] )    ? absint( $_POST['user_id'] )    : 0;
    $nonce      = isset( $_POST['nonce'] )      ? sanitize_text_field( $_POST['nonce'] ) : '';

    // Verify nonce
    if ( ! wp_verify_nonce( $nonce, 'automatorwp_manual_trigger_' . $trigger_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'automatorwp-manual-triggers' ) ) );
    }

    if ( $trigger_id === 0 ) {
        wp_send_json_error( array( 'message' => __( 'No trigger ID provided.', 'automatorwp-manual-triggers' ) ) );
    }

    // Run the trigger
    automatorwp_run_trigger( $trigger_id, $user_id );

    wp_send_json_success( array( 'message' => __( 'Trigger executed successfully.', 'automatorwp-manual-triggers' ) ) );

}
// For logged-in users
add_action( 'wp_ajax_automatorwp_manual_trigger_run', 'automatorwp_manual_trigger_ajax_handler' );
// For non-logged-in users (anonymous)
add_action( 'wp_ajax_nopriv_automatorwp_manual_trigger_run', 'automatorwp_manual_trigger_ajax_handler' );
