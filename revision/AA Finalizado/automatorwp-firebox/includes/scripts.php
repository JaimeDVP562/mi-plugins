<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\FireBox\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Enqueue frontend scripts.
 *
 * Handles two things:
 * 1. Listen for FireBox conversion events and relay them to PHP via AJAX.
 * 2. Open a pending popup stored by the "Show popup" action.
 *
 * @since 1.0.0
 */
function automatorwp_firebox_enqueue_scripts()
{

    wp_enqueue_script(
        'automatorwp-firebox-js',
        AUTOMATORWP_FIREBOX_URL . 'assets/js/automatorwp-firebox.js',
        array('jquery'),
        AUTOMATORWP_FIREBOX_VER,
        true
    );

    // Check if there is a popup pending to be shown for the current user
    $pending_popup = 0;
    $user_id       = get_current_user_id();

    if ($user_id > 0) {
        $stored = get_transient('automatorwp_firebox_show_popup_' . $user_id);

        if ($stored) {
            $pending_popup = absint($stored);
            delete_transient('automatorwp_firebox_show_popup_' . $user_id);
        }
    }

    wp_localize_script('automatorwp-firebox-js', 'automatorwp_firebox', array(
        'ajax_url'      => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('automatorwp_firebox'),
        'pending_popup' => $pending_popup,
    ));

}
add_action('wp_enqueue_scripts', 'automatorwp_firebox_enqueue_scripts');
