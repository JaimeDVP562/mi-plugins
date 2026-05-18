<?php
/**
 * Functions
 * Helper and utility functions for Keap integration
 *
 * @package     AutomatorWP\Integrations\Keap\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get Keap API parameters
 *
 * @since 1.0.0
 *
 * @return array|false Array with access_token and base URLs, or false if not configured
 */
function automatorwp_keap_get_api() {

    $access_token = automatorwp_keap_get_option( 'access_token', '' );

    if( empty( $access_token ) ) {
        return false;
    }

    return array(
        'access_token' => $access_token,
        'url_v1'       => 'https://api.infusionsoft.com/crm/rest/v1',
        'url_v2'       => 'https://api.infusionsoft.com/crm/rest/v2',
    );
}

/**
 * Check if Keap settings are valid and connected
 *
 * @since 1.0.0
 *
 * @param array $credentials Optional credentials array with access_token key
 *
 * @return bool
 */
function automatorwp_keap_check_settings_status( $credentials = array() ) {

    if ( empty( $credentials ) ) {
        $credentials = automatorwp_keap_get_api();
    }

    if ( ! $credentials ) {
        return false;
    }

    return automatorwp_keap_validate_credentials( $credentials['access_token'] );
}

/**
 * Get list of campaigns for UI selectors
 *
 * @since 1.1.0
 *
 * @return array
 */
function automatorwp_keap_get_campaigns_list() {

    $campaigns = automatorwp_keap_get_campaigns();
    $options   = array();

    if ( is_array( $campaigns ) ) {
        foreach ( $campaigns as $campaign ) {
            $name = isset( $campaign['name'] ) ? $campaign['name'] : 'Campaign ' . $campaign['id'];
            $options[ $campaign['id'] ] = $name;
        }
    }

    return $options;
}

/**
 * Get list of tags for UI selectors
 *
 * @since 1.1.0
 *
 * @return array
 */
function automatorwp_keap_get_tags_list() {

    $tags    = automatorwp_keap_get_tags();
    $options = array();

    if ( is_array( $tags ) ) {
        foreach ( $tags as $tag ) {
            $name = isset( $tag['name'] ) ? $tag['name'] : 'Tag ' . $tag['id'];
            $options[ $tag['id'] ] = $name;
        }
    }

    return $options;
}

/**
 * Resolve tag name to tag ID
 * If the value is numeric, returns it as-is.
 * If it's a string, searches tags by name.
 *
 * @since 1.1.0
 *
 * @param string|int $tag_value Tag ID or tag name
 *
 * @return int|false Tag ID or false if not found
 */
function automatorwp_keap_resolve_tag_id( $tag_value ) {

    // If numeric, use directly
    if ( is_numeric( $tag_value ) ) {
        return intval( $tag_value );
    }

    // Search by name
    $tags = automatorwp_keap_get_tags();

    if ( ! is_array( $tags ) ) {
        return false;
    }

    foreach ( $tags as $tag ) {
        if ( isset( $tag['name'] ) && strtolower( $tag['name'] ) === strtolower( $tag_value ) ) {
            return intval( $tag['id'] );
        }
    }

    return false;
}

/**
 * Callback for campaign options selector
 *
 * @since 1.1.0
 *
 * @param stdClass $field Field object
 *
 * @return array
 */
function automatorwp_keap_options_cb_campaign( $field ) {

    $value      = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any campaign', 'automatorwp-keap' );
    $options    = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {

        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        $campaigns = automatorwp_keap_get_campaigns_list();

        foreach( $value as $campaign_id ) {

            if( $campaign_id === $none_value ) {
                continue;
            }

            $options[ $campaign_id ] = isset( $campaigns[ $campaign_id ] )
                ? $campaigns[ $campaign_id ]
                : 'Campaign ' . $campaign_id;
        }
    }

    return $options;
}

/**
 * Callback for tag options selector
 *
 * @since 1.1.0
 *
 * @param stdClass $field Field object
 *
 * @return array
 */
function automatorwp_keap_options_cb_tag( $field ) {

    $value      = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp-keap' );
    $options    = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {

        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        $tags = automatorwp_keap_get_tags_list();

        foreach( $value as $tag_id ) {

            if( $tag_id === $none_value ) {
                continue;
            }

            $options[ $tag_id ] = isset( $tags[ $tag_id ] )
                ? $tags[ $tag_id ]
                : 'Tag ' . $tag_id;
        }
    }

    return $options;
}