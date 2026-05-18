<?php

/**
 * Scripts and styles for Grok integration
 */

if (! defined('ABSPATH')) exit;

/**
 * Enqueue admin scripts and styles
 */
function automatorwp_grok_admin_enqueue_scripts()
{

    // Load our minified CSS for the Grok icon
    wp_enqueue_style(
        'automatorwp-grok-admin',
        AUTOMATORWP_GROK_ASSETS_URL . 'css/automatorwp-grok.min.css',
        array(),
        AUTOMATORWP_GROK_VER
    );

    // Load our minified JS for the AJAX authorization
    wp_enqueue_script(
        'automatorwp-grok-admin',
        AUTOMATORWP_GROK_ASSETS_URL . 'js/automatorwp-grok.min.js',
        array('jquery'),
        AUTOMATORWP_GROK_VER,
        true
    );

    // Pass PHP data to the JS file (The localized object)
    wp_localize_script('automatorwp-grok-admin', 'automatorwp_grok', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('automatorwp_grok_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'automatorwp_grok_admin_enqueue_scripts');
