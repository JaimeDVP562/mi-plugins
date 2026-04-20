<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return option name for API key. Prefer existing settings helper if present.
 *
 * @return string
 */
function automatorwp_grok_option_name() {
    if ( function_exists( 'automatorwp_grok_api_key_option_name' ) ) {
        return automatorwp_grok_api_key_option_name();
    }

    return 'automatorwp_grok_api_key';
}

/**
 * Get stored settings for the integration.
 *
 * @return array
 */
function automatorwp_grok_get_settings() {
    $api_key = get_option( automatorwp_grok_option_name(), '' );
    return array( 'api_key' => is_string( $api_key ) ? trim( $api_key ) : '' );
}

/**
 * Update stored settings for the integration.
 *
 * @param array $new_settings Associative array with keys like 'api_key'.
 * @return void
 */
function automatorwp_grok_update_settings( $new_settings ) {
    if ( isset( $new_settings['api_key'] ) ) {
        update_option( automatorwp_grok_option_name(), sanitize_text_field( $new_settings['api_key'] ) );
    }
}

/**
 * Convenience getter for API key.
 *
 * @return string
 */
function automatorwp_grok_get_api_key() {
    $settings = automatorwp_grok_get_settings();
    return isset( $settings['api_key'] ) ? $settings['api_key'] : '';
}
