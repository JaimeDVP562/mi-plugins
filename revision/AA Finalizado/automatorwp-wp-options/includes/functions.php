<?php
/**
 * Functions
 *
 * @package     AutomatorWP\WP_Options\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get an option value safely
 *
 * @since 1.0.0
 *
 * @param string $option_name   The option name to retrieve
 * @param mixed  $default       Default value if option doesn't exist
 *
 * @return mixed The option value, or default if not found
 */
function automatorwp_wp_options_get_option_value( $option_name, $default = '' ) {

    if( empty( $option_name ) ) {
        return $default;
    }

    $option_name = sanitize_text_field( $option_name );

    $value = get_option( $option_name, $default );

    // If the value is an array or object, JSON encode it for display
    if( is_array( $value ) || is_object( $value ) ) {
        $value = wp_json_encode( $value );
    }

    return $value;

}

/**
 * Validates an option name to prevent modifications to protected/critical options
 *
 * @since 1.0.0
 *
 * @param string $option_name The option name to validate
 *
 * @return bool True if the option name is safe to modify, false otherwise
 */
function automatorwp_wp_options_is_safe_option( $option_name ) {

    // List of protected core options that should not be modified
    $protected_options = array(
        'siteurl',
        'home',
        'admin_email',
        'users_can_register',
        'default_role',
        'db_version',
        'initial_db_version',
        'wp_user_roles',
        'auth_key',
        'secure_auth_key',
        'logged_in_key',
        'nonce_key',
        'auth_salt',
        'secure_auth_salt',
        'logged_in_salt',
        'nonce_salt',
        'active_plugins',
        'template',
        'stylesheet',
        'current_theme',
    );

    /**
     * Filter to modify the list of protected options
     *
     * @since 1.0.0
     *
     * @param array $protected_options List of protected option names
     */
    $protected_options = apply_filters( 'automatorwp_wp_options_protected_options', $protected_options );

    return ! in_array( $option_name, $protected_options, true );

}

/**
 * Get option autoload value
 *
 * @since 1.0.0
 *
 * @param string $autoload Autoload value ('yes' or 'no')
 *
 * @return string Sanitized autoload value
 */
function automatorwp_wp_options_sanitize_autoload( $autoload ) {

    return in_array( $autoload, array( 'yes', 'no' ), true ) ? $autoload : 'yes';

}
