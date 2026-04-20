<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_mailmint_get_settings() {
    $settings = get_option( 'automatorwp_settings', array() );
    return array(
        'auth_method' => isset( $settings['automatorwp_mailmint_auth_method'] ) ? $settings['automatorwp_mailmint_auth_method'] : 'internal',
        'api_key' => isset( $settings['automatorwp_mailmint_api_key'] ) ? $settings['automatorwp_mailmint_api_key'] : '',
        'api_base' => isset( $settings['automatorwp_mailmint_api_base'] ) ? $settings['automatorwp_mailmint_api_base'] : '',
    );
}

function automatorwp_mailmint_api_request( $method, $url, $body = array(), $api_key = '' ) {
    $args = array(
        'method' => $method,
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 20,
        'sslverify' => false,
    );
    if ( $api_key ) {
        $args['headers']['Authorization'] = 'Bearer ' . $api_key;
    }
    if ( ! empty( $body ) ) {
        $args['body'] = wp_json_encode( $body );
    }
    return wp_remote_request( $url, $args );
}

function automatorwp_mailmint_add_contact( $data ) {
    // Prefer Mail Mint helper functions if available
    if ( function_exists( 'mailmint_create_single_contact' ) ) {
        // Mail Mint expects 'lists' as array and other field names
        $mdata = $data;
        if ( isset( $mdata['list_id'] ) && ! empty( $mdata['list_id'] ) ) {
            $mdata['lists'] = array( $mdata['list_id'] );
            unset( $mdata['list_id'] );
        }
        // Call external plugin function while suppressing a known non-fatal warning
        $prev_handler = set_error_handler( function( $errno, $errstr ) {
            if ( false !== strpos( $errstr, 'Undefined array key "status"' ) ) {
                return true; // mark as handled to suppress the warning
            }
            return false; // let other errors bubble
        } );
        $resp = mailmint_create_single_contact( $mdata );
        restore_error_handler();
        if ( is_wp_error( $resp ) ) return $resp;
        if ( is_array( $resp ) ) {
            if ( isset( $resp['status'] ) ) {
                return in_array( strtolower( (string) $resp['status'] ), array( 'ok', 'success', 'created' ), true );
            }
            return ! empty( $resp );
        }
        return (bool) $resp;
    }
    if ( function_exists( 'mailmint_create_multiple_contacts' ) ) {
        // use single contact wrapper
        $mdata = $data;
        if ( isset( $mdata['list_id'] ) && ! empty( $mdata['list_id'] ) ) {
            $mdata['lists'] = array( $mdata['list_id'] );
            unset( $mdata['list_id'] );
        }
        $prev_handler = set_error_handler( function( $errno, $errstr ) {
            if ( false !== strpos( $errstr, 'Undefined array key "status"' ) ) {
                return true;
            }
            return false;
        } );
        $resp = mailmint_create_multiple_contacts( array( $mdata ) );
        restore_error_handler();
        if ( is_wp_error( $resp ) ) return $resp;
        if ( is_array( $resp ) ) {
            return ! empty( $resp );
        }
        return (bool) $resp;
    }
    if ( function_exists( 'mailmint_add_contact' ) ) {
        $prev_handler = set_error_handler( function( $errno, $errstr ) {
            if ( false !== strpos( $errstr, 'Undefined array key "status"' ) ) {
                return true;
            }
            return false;
        } );
        $resp = mailmint_add_contact( $data );
        restore_error_handler();
        if ( is_wp_error( $resp ) ) return $resp;
        if ( is_array( $resp ) ) {
            return ! empty( $resp );
        }
        return (bool) $resp;
    }
    if ( function_exists( 'mrm_add_contact' ) ) {
        $prev_handler = set_error_handler( function( $errno, $errstr ) {
            if ( false !== strpos( $errstr, 'Undefined array key "status"' ) ) {
                return true;
            }
            return false;
        } );
        $resp = mrm_add_contact( $data );
        restore_error_handler();
        if ( is_wp_error( $resp ) ) return $resp;
        if ( is_array( $resp ) ) {
            return ! empty( $resp );
        }
        return (bool) $resp;
    }

    $settings = automatorwp_mailmint_get_settings();
    if ( $settings['auth_method'] === 'api' && ! empty( $settings['api_key'] ) && ! empty( $settings['api_base'] ) ) {
        $url = rtrim( $settings['api_base'], '/' ) . '/contacts';
        $resp = automatorwp_mailmint_api_request( 'POST', $url, $data, $settings['api_key'] );
        if ( is_wp_error( $resp ) ) return $resp;
        // Normalize WP HTTP response
        $code = wp_remote_retrieve_response_code( $resp );
        $body = wp_remote_retrieve_body( $resp );
        if ( $code >= 200 && $code < 300 ) {
            return true;
        }
        // Log body for debugging
        error_log( 'automatorwp_mailmint_api_request failed: HTTP ' . $code . ' body: ' . $body );
        return false;
    }

    $queue = get_option( 'automatorwp_mailmint_queue', array() );
    $queue[] = $data;
    update_option( 'automatorwp_mailmint_queue', $queue );
    return false;
}

