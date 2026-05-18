<?php
/**
 * Tags
 *
 * @package     AutomatorWP\WP_Options\Tags
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register WP Options tags
 *
 * Tags allow users to dynamically retrieve option values in their automations.
 * Usage: {wp_option:option_name} — replaced with the value of the WP option.
 *
 * @since 1.0.0
 */
function automatorwp_wp_options_register_tags() {

    // Register the global tag for getting any WP option value
    automatorwp_register_tag( 'wp_option', array(
        'integration'   => 'wp_options',
        'label'         => __( 'WP Option', 'automatorwp-wp-options' ),
        'schema'        => 'wp_option:{option_name}',
        'preview'       => __( 'Value of a WordPress option. Replace {option_name} with the option name (e.g. blogname, blogdescription)', 'automatorwp-wp-options' ),
        'function'      => 'automatorwp_wp_options_tag_replacement',
        'parse_content' => 'automatorwp_wp_options_parse_tag_content',
    ) );

}
add_action( 'automatorwp_init', 'automatorwp_wp_options_register_tags', 20 );

/**
 * Parse automation tags to replace {wp_option:OPTION_NAME} with the actual option value
 *
 * This function hooks into AutomatorWP's tag parsing system and replaces
 * any occurrence of {wp_option:OPTION_NAME} with the actual WordPress option value.
 *
 * @since 1.0.0
 *
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 *
 * @return string
 */
function automatorwp_wp_options_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Look for {wp_option:OPTION_NAME} pattern
    preg_match_all( "/\{wp_option:\s*(.*?)\s*\}/", $parsed_content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $option_name ) {

            $option_name = sanitize_text_field( $option_name );

            if( ! empty( $option_name ) ) {
                $value = automatorwp_wp_options_get_option_value( $option_name );
                $new_replacements['{wp_option:' . $option_name . '}'] = $value;
            }

        }

    }

    if( count( $new_replacements ) ) {

        $tags = array_keys( $new_replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $new_replacements, $parsed_content );

    }

    return $parsed_content;

}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_wp_options_parse_automation_tags', 10, 5 );
