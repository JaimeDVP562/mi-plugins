<?php

/**
 * Scripts and styles for DeepSeek.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register admin scripts and styles.
 */
function automatorwp_deepseek_admin_register_scripts()
{
    // Use minified version in production, standard in debug mode
    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

    // Register CSS
    wp_register_style(
        'automatorwp-deepseek-css',
        AUTOMATORWP_DEEPSEEK_URL . 'assets/css/automatorwp-deepseek' . $suffix . '.css',
        array(),
        AUTOMATORWP_DEEPSEEK_VER
    );

    // Register JS
    wp_register_script(
        'automatorwp-deepseek-js',
        AUTOMATORWP_DEEPSEEK_URL . 'assets/js/automatorwp-deepseek' . $suffix . '.js',
        array('jquery'),
        AUTOMATORWP_DEEPSEEK_VER,
        true
    );
}
add_action('admin_init', 'automatorwp_deepseek_admin_register_scripts');

/**
 * Enqueue admin scripts and styles for DeepSeek.
 */
function automatorwp_deepseek_admin_enqueue_scripts($hook)
{
    // Load only on AutomatorWP settings screen to avoid overhead
    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

    if ($page !== 'automatorwp_settings') {
        return;
    }

    wp_enqueue_style('automatorwp-deepseek-css');

    // Ensure dashicon loads even if relative paths fail: add inline CSS using absolute plugin URL.
    if (defined('AUTOMATORWP_DEEPSEEK_URL')) {
        $inline_css = ".automatorwp-settings-section-icon-deepseek, .dashicons-deepseek {background-image: url('" . AUTOMATORWP_DEEPSEEK_URL . "assets/img/dashicon-deepseek.svg') !important; background-size: contain; background-repeat: no-repeat; background-position: center;}";
        wp_add_inline_style('automatorwp-deepseek-css', $inline_css);
    }

    /**
     * Localize script to pass PHP data to our JS file.
     * The object name 'automatorwp_deepseek' matches our JS implementation.
     */
    wp_localize_script(
        'automatorwp-deepseek-js',
        'automatorwp_deepseek', // Object name used in automatorwp-deepseek.js
        array(
            'ajax_url' => admin_url('admin-ajax.php'), // Correct endpoint for AJAX calls
            'nonce'    => function_exists('automatorwp_get_admin_nonce')
                ? automatorwp_get_admin_nonce()
                : wp_create_nonce('automatorwp_admin'),
        )
    );

    wp_enqueue_script('automatorwp-deepseek-js');
}
add_action('admin_enqueue_scripts', 'automatorwp_deepseek_admin_enqueue_scripts', 100);
