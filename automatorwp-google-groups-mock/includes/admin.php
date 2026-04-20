<?php
/**
 * Administration Settings for Google Groups
 *
 * @package     AutomatorWP\GoogleGroups\Admin
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcut function to retrieve plugin settings
 *
 * @since 1.0.0
 * @param string $option_name The setting name.
 * @param mixed  $default     The default value.
 * @return mixed The option value.
 */
if ( ! function_exists( 'automatorwp_googlegroups_get_option' ) ) {
    function automatorwp_googlegroups_get_option( $option_name, $default = false ) {
        $prefix = 'automatorwp_googlegroups_';
        return automatorwp_get_option( $prefix . $option_name, $default );
    }
}

/**
 * Register the integration settings section
 *
 * @since 1.0.0
 * @param array $automatorwp_settings_sections Current settings sections.
 * @return array Modified settings sections.
 */
function automatorwp_googlegroups_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['googlegroups'] = array(
        'title' => __( 'Google Groups', 'automatorwp-googlegroups' ),
        'icon'  => 'dashicons-groups',
    );

    return $automatorwp_settings_sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_googlegroups_settings_sections' );

/**
 * Register settings meta boxes and fields
 *
 * @since 1.0.0
 * @param array $meta_boxes Current settings meta boxes.
 * @return array Modified settings meta boxes.
 */
function automatorwp_googlegroups_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_googlegroups_';

    $meta_boxes['automatorwp-googlegroups-settings'] = array(
        'title'  => automatorwp_dashicon( 'groups' ) . __( 'Google Groups Settings', 'automatorwp-googlegroups' ),
        'fields' => apply_filters( 'automatorwp_googlegroups_settings_fields', array(
            $prefix . 'configured' => array(
                'name' => __( 'Configured', 'automatorwp-googlegroups' ),
                'desc' => __( 'Mark this option when you have configured Google credentials (service account) on the server.  Once this is checked the plugin will automatically stop using the mock client.', 'automatorwp-googlegroups' ),
                'type' => 'checkbox',
            ),
            $prefix . 'service_account_email' => array(
                'name' => __( 'Service account impersonated email', 'automatorwp-googlegroups' ),
                'desc' => __( 'Email account to impersonate when using domain-wide delegation (admin@example.com). Optional for now.', 'automatorwp-googlegroups' ),
                'type' => 'text',
            ),
            $prefix . 'service_account_domain' => array(
                'name' => __( 'Service account domain', 'automatorwp-googlegroups' ),
                'desc' => __( 'Primary domain to list groups from (e.g. example.com). If empty, the plugin will try to infer domain from the impersonated email.', 'automatorwp-googlegroups' ),
                'type' => 'text',
            ),
             $prefix . 'service_account_json' => array(
                 'name' => __( 'Service Account JSON', 'automatorwp-googlegroups' ),
                 'desc' => __( 'Paste the JSON key of your Google Service Account here (keep it private). Required to call the Directory API. Use domain-wide delegation and provide an impersonated admin email above.', 'automatorwp-googlegroups' ),
                 'type' => 'textarea',
                 'attrs' => array( 'rows' => 10 ),
             ),
             $prefix . 'test_mode' => array(
                 'name' => __( 'Force test/mock mode', 'automatorwp-googlegroups' ),
                 'desc' => __( 'Keep this checked to always use the built-in mock client. The option will be unchecked automatically once valid credentials are supplied and the plugin is marked as configured.', 'automatorwp-googlegroups' ),
                 'type' => 'checkbox',
             ),
         ) ),
     );

     return $meta_boxes;
 }
 add_filter( "automatorwp_settings_googlegroups_meta_boxes", 'automatorwp_googlegroups_settings_meta_boxes' );

