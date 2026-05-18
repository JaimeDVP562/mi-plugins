<?php
/**
 * Shortcodes
 *
 * @package GamiPress\Credly\Shortcodes
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Shortcodes
require_once GAMIPRESS_CREDLY_DIR . 'includes/shortcodes/gamipress_credly_login.php';

/**
 * Adds the "credly" parameter to [gamipress_achievements]
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return mixed
 */
function gamipress_credly_achievements_shortcode_fields( $fields ) {

    $fields['credly'] = array(
        'name'        => __( 'Show Credly Achievements Only', 'gamipress-credly' ),
        'description' => __( 'Display only achievements synchronized with Credly.', 'gamipress-credly' ),
        'type' 	=> 'checkbox',
        'classes' => 'gamipress-switch',
    );

    return $fields;

}
add_filter( 'gamipress_gamipress_achievements_shortcode_fields', 'gamipress_credly_achievements_shortcode_fields' );

/**
 * Adds the "credly" field to the general tab on [gamipress_achievements]
 *
 * @since 1.0.0
 *
 * @param array $tabs
 *
 * @return mixed
 */
function gamipress_credly_achievements_shortcode_tabs( $tabs ) {

    $tabs['general']['fields'][] = 'credly';

    return $tabs;

}
add_filter( 'gamipress_gamipress_achievements_shortcode_tabs', 'gamipress_credly_achievements_shortcode_tabs' );

/**
 * Default value for "credly" parameter
 *
 *
 * @since 1.0.0
 *
 * @param array  $out       The output array of shortcode attributes.
 * @param array  $pairs     The supported attributes and their defaults.
 * @param array  $atts      The user defined shortcode attributes.
 * @param string $shortcode The shortcode name.
 *
 * @return array
 */
function gamipress_credly_achievements_shortcode_default_atts( $out, $pairs, $atts, $shortcode ) {

    $out['credly'] = ( isset( $atts['credly'] ) ? $atts['credly'] : '' );

    return $out;

}
add_filter( 'shortcode_atts_gamipress_achievements', 'gamipress_credly_achievements_shortcode_default_atts', 10, 4 );

/**
 * Filters achievements list query args
 *
 * @since 1.0.0
 *
 * @param array $query_args Query args to be passed to WP_Query
 * @param array $args       Function received args (Note: to pass your own args on achievements list request, check JS event 'gamipress_achievements_list_request_data')
 *
 * @return array
 */
function gamipress_credly_achievements_shortcode_query_args( $query_args, $args ) {

    $prefix = '_gamipress_credly_';

    if( isset( $args['credly'] ) && $args['credly'] === 'yes' ) {

        if( ! isset( $query_args['meta_query'] ) ) {
            $query_args['meta_query'] = array();
        }

        $query_args['meta_query'][] = array(
            'key' => $prefix . 'sync',
            'value' => 'on',
        );

    }

    return $query_args;

}
add_filter( 'gamipress_achievements_shortcode_query_args', 'gamipress_credly_achievements_shortcode_query_args', 10, 2 );