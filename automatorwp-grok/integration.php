<?php
/**
 * Integration registration.
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Grok integration.
 *
 * @return void
 */
function automatorwp_grok_register_integration() {

    if ( ! function_exists( 'automatorwp_register_integration' ) ) {
        return;
    }

    automatorwp_register_integration(
        'grok',
        array(
            'label' => __( 'Grok (xAI)', 'automatorwp-grok' ),
        )
    );
}
add_action( 'init', 'automatorwp_grok_register_integration', 20 );
