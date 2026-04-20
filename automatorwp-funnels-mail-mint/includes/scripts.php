<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_mailmint_register_scripts() {
    // Register admin scripts/styles for AutomatorWP settings page
    wp_register_script( 'automatorwp-mailmint-admin', AUTOMATORWP_MAILMINT_URL . 'assets/admin.js', array('jquery'), AUTOMATORWP_MAILMINT_VER, true );
    wp_register_style( 'automatorwp-mailmint-admin', AUTOMATORWP_MAILMINT_URL . 'assets/admin.css', array(), AUTOMATORWP_MAILMINT_VER );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailmint_register_scripts' );

function automatorwp_mailmint_enqueue_settings_scripts( $hook ) {
    // Only enqueue on AutomatorWP settings screen
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'automatorwp_settings' ) {
        wp_enqueue_script( 'automatorwp-mailmint-admin' );
        wp_enqueue_style( 'automatorwp-mailmint-admin' );
        wp_localize_script( 'automatorwp-mailmint-admin', 'automatorwp_mailmint_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'automatorwp_admin' ),
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mailmint_enqueue_settings_scripts' );
