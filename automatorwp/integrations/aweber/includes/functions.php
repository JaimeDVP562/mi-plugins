<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\AWeber\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get account from AWeber
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_aweber_options_cb_account( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any account', 'automatorwp-mailerlite' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $account_id ) {

            // Skip option none
            if( $account_id === $none_value ) {
                continue;
            }

            $options[$account_id] = automatorwp_aweber_get_account_name( $account_id );
        }
    }

    return $options;

}

/**
 * Get accounts
 *
 * @since 1.0.0
 * 
 * @return array
 */
function automatorwp_aweber_get_accounts( ) {

    $params = automatorwp_aweber_get_request_parameters( );
    
    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $options = array();
    $url  = 'https://api.aweber.com/1.0/accounts';
    $response = wp_remote_get( $url, $params );

    $response = json_decode( wp_remote_retrieve_body( $response ), true );
    
    foreach( $response['entries'] as $account ) {

        $options[] = array(
            'id'        => $account['id'],
            'company'   => $account['company']
        );

    }

    return $options;
}

/**
* Get the account name
*
* @since 1.0.0
* 
* @param int $account_id
*
* @return string
*/
function automatorwp_aweber_get_account_name( $account_id ) {

    $params = automatorwp_aweber_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $url  = 'https://api.aweber.com/1.0/accounts/' . $account_id;
    $response = wp_remote_get( $url, $params );
    
    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code !== 200 ) {
        return false;
    }

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    
    return $response['company'];
}

/**
 * Get account from AWeber
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_aweber_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any account', 'automatorwp-aweber' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    $account_id = ct_get_object_meta( $field->object_id, 'account', true );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $list_id ) {

            // Skip option none
            if( $list_id === $none_value ) {
                continue;
            }

            $options[$list_id] = automatorwp_aweber_get_list_name( $account_id, $list_id );
        }
    }

    return $options;

}

/**
 * Get Lists
 *
 * @since 1.0.0
 * 
 * @param int $account_id
 * 
 * @return array
 */
function automatorwp_aweber_get_lists( $account_id ) {

    $params = automatorwp_aweber_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $options = array();
    $url  = 'https://api.aweber.com/1.0/accounts/' . $account_id . '/lists';
    $response = wp_remote_get( $url, $params );

    $response = json_decode( wp_remote_retrieve_body( $response ), true );
    
    foreach( $response['entries'] as $list ) {

        $options[] = array(
            'id'    => $list['id'],
            'name'  => $list['name']
        );

    }

    return $options;
}

/**
* Get the list name
*
* @since 1.0.0
* 
* @param int $account_id
* @param int $list_id
*
* @return string
*/
function automatorwp_aweber_get_list_name( $account_id, $list_id ) {

    $params = automatorwp_aweber_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $url  = 'https://api.aweber.com/1.0/accounts/' . $account_id . '/lists/' . $list_id;
    $response = wp_remote_get( $url, $params );
    
    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code !== 200 ) {
        return false;
    }

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    
    return $response['name'];
}

/**
 * Add subscriber to AWeber list
 *
 * @since 1.0.0
 * 
 * @param array     $subscriber     The subscriber data
 * @param int       $account_id
 * @param int       $list_id
 * 
 * @return array
 */
function automatorwp_aweber_add_subscriber( $subscriber, $account_id, $list_id ) {

    $params = automatorwp_aweber_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $url  = 'https://api.aweber.com/1.0/accounts/' . $account_id . '/lists/' . $list_id . '/subscribers';

    $params['body'] = json_encode( $subscriber );
    
    $response = wp_remote_post( $url, $params );
     
    $response_body = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response_body;
    
}

/**
 * Add/Remove tags to subscriber
 *
 * @since 1.0.0
 * 
 * @param array     $subscriber_tags    The tags to add
 * @param int       $account_id
 * @param int       $list_id
 * @param string    $email
 * 
 * @return array
 */
function automatorwp_aweber_handle_tag_subscriber( $subscriber_tags, $account_id, $list_id, $email ) {

    $params = automatorwp_aweber_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return;
    }

    $url  = 'https://api.aweber.com/1.0/accounts/' . $account_id . '/lists/' . $list_id . '/subscribers?subscriber_email=' . $email;
    
    $params['method'] = 'PATCH';
    $params['body'] = json_encode( $subscriber_tags );
    
    $response = wp_remote_request( $url, $params );
     
    $response_body = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response_body;
    
}

/**
 * Get the request parameters
 *
 * @since 1.0.0
 *
 * @param string $platform
 *
 * @return array|false
 */
function automatorwp_aweber_get_request_parameters( ) {

    $auth = get_option( 'automatorwp_aweber_auth' );
    
    if( ! is_array( $auth ) ) {
        return false;
    }

    return array(
        'user-agent'  => 'AutomatorWP; ' . home_url(),
        'timeout'     => 120,
        'httpversion' => '1.1',
        'headers'     => array(
            'Authorization' => 'Bearer ' . $auth['access_token'],
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        )
    );
}

/**
 * Get the request parameters
 *
 * @since 1.0.0
 *
 * @param string $platform
 *
 * @return string|false|WP_Error
 */
function automatorwp_aweber_refresh_token( ) {

    $client_id = automatorwp_aweber_get_option( 'client_id', '' );
    $client_secret = automatorwp_aweber_get_option( 'client_secret', '' );

    if( empty( $client_id ) || empty( $client_secret ) ) {
        return false;
    }

    $auth = get_option( 'automatorwp_aweber_auth', false );

    if( ! is_array( $auth ) ) {
        return false;
    }

    $params = array(
        'headers' => array(
            'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
            'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
            'Accept'        => 'application/json',
        ),
        'body'  => array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $auth['refresh_token'],
        )
    );

    $response = wp_remote_post( 'https://auth.aweber.com/oauth2/token', $params );
    
    if( is_wp_error( $response ) ) {
        return false;
    }

    $response_code = wp_remote_retrieve_response_code( $response );

    if ( $response_code !== 200 ) {
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ) );

    $ref_token = $auth['refresh_token'];

    $auth = array(
        'access_token'  => $body->access_token,
        'refresh_token' => $ref_token,
        'token_type'    => $body->token_type,
        'expires_in'    => $body->expires_in,
        //'scope'         => $body->scope,
    );

    // Update the access and refresh tokens
    update_option( 'automatorwp_aweber_auth', $auth );

    return $body->access_token;

}

/**
 * Filters the HTTP API response immediately before the response is returned.
 *
 * @since 1.0.0
 *
 * @param array  $response    HTTP response.
 * @param array  $parsed_args HTTP request arguments.
 * @param string $url         The request URL.
 *
 * @return array
 */
function automatorwp_aweber_maybe_refresh_token( $response, $args, $url ) {

    // Ensure to only run this check to on Aweber request
    if( strpos( $url, 'api.aweber.com' ) !== false ) {
        
        $code = wp_remote_retrieve_response_code( $response );
        
        if( $code === 401 ) {

            $access_token = automatorwp_aweber_refresh_token( );

            // Send again the request if token gets refreshed successfully
            if( $access_token ) {

                $args['headers']['Authorization'] = 'Bearer ' . $access_token;

                $response = wp_remote_request( $url, $args );

            }

        }

    }

    return $response;

}
add_filter( 'http_response', 'automatorwp_aweber_maybe_refresh_token', 10, 3 );
