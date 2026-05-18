<?php
/**
 * Scripts and styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register admin scripts/styles.
 */
function automatorwp_mistral_ai_admin_register_scripts() {

    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_register_style(
        'automatorwp-mistral-ai-css',
        AUTOMATORWP_MISTRAL_AI_URL . 'assets/css/automatorwp-mistral-ai' . $suffix . '.css',
        array(),
        AUTOMATORWP_MISTRAL_AI_VER
    );

    wp_register_script(
        'automatorwp-mistral-ai-js',
        AUTOMATORWP_MISTRAL_AI_URL . 'assets/js/automatorwp-mistral-ai' . $suffix . '.js',
        array( 'jquery' ),
        AUTOMATORWP_MISTRAL_AI_VER,
        true
    );
}
add_action( 'admin_init', 'automatorwp_mistral_ai_admin_register_scripts' );

/**
 * Enqueue admin scripts/styles.
 */
function automatorwp_mistral_ai_admin_enqueue_scripts( $hook ) {

    // Sanitize page parameter (hygiene improvement)
    $page = isset( $_GET['page'] )
        ? sanitize_text_field( wp_unslash( $_GET['page'] ) )
        : '';

    // Load only on AutomatorWP settings screens
    if ( $page !== 'automatorwp_settings' ) {
        return;
    }

    wp_enqueue_style( 'automatorwp-mistral-ai-css' );

    wp_localize_script(
        'automatorwp-mistral-ai-js',
        'automatorwp_mistral_ai',
        array(
            'nonce' => function_exists( 'automatorwp_get_admin_nonce' )
                ? automatorwp_get_admin_nonce()
                : wp_create_nonce( 'automatorwp_admin' ),
        )
    );

    wp_enqueue_script( 'automatorwp-mistral-ai-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_mistral_ai_admin_enqueue_scripts', 100 );
