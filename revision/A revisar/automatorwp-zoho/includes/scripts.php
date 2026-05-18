<?php

/**
 * Scripts for Zoho integration
 */

if (! defined('ABSPATH')) exit;

function automatorwp_zoho_admin_enqueue_scripts($hook)
{
    // Solo cargamos en la página de ajustes de AutomatorWP
    if ($hook !== 'automatorwp_page_automatorwp_settings') {
        return;
    }

    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

    // Encolar CSS
    wp_enqueue_style(
        'automatorwp-zoho-css',
        AUTOMATORWP_ZOHO_URL . 'assets/css/automatorwp-zoho' . $suffix . '.css',
        array(),
        AUTOMATORWP_ZOHO_VER
    );

    // Encolar JS
    wp_enqueue_script(
        'automatorwp-zoho-js',
        AUTOMATORWP_ZOHO_URL . 'assets/js/automatorwp-zoho' . $suffix . '.js',
        array('jquery'),
        AUTOMATORWP_ZOHO_VER,
        true
    );

    // LOCALIZACIÓN: Esto es lo que evita que el botón refresque la página
    wp_localize_script('automatorwp-zoho-js', 'automatorwp_zoho', array(
        'nonce'   => wp_create_nonce('automatorwp_zoho_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}
// Prioridad alta (99) para asegurar que cargue después de los scripts de AutomatorWP
add_action('admin_enqueue_scripts', 'automatorwp_zoho_admin_enqueue_scripts', 99);
