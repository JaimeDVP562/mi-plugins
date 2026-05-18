<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\Google_Sheets\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Get the spreadsheets
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_google_sheets_get_spreadsheets() {

    $spreadsheets = array();

    $params = automatorwp_google_sheets_get_request_parameters( );

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return $spreadsheets;
    }

    $url = 'https://www.googleapis.com/drive/v3/files?q=mimeType="application/vnd.google-apps.spreadsheet"&pageSize=1000&includeItemsFromAllDrives=true&supportsAllDrives=true&corpora=allDrives';

    $response = wp_remote_get( $url, $params );

    $response = json_decode( wp_remote_retrieve_body( $response ), true );

    if( isset( $response['files'] ) && is_array( $response['files'] ) ) {

        foreach( $response['files'] as $key => $sheet ) {

                $spreadsheets[] = array(
                    'id' => $sheet['id'],
                    'name' => $sheet['name'],
                );

        } 
    }

    return $spreadsheets;
}

/**
 * Options callback for select2 fields assigned to spreadsheets
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_google_sheets_options_cb_spreadsheets( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any spreadsheet', 'automatorwp-google-sheets' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $spreadsheet_id ) {

            // Skip option none
            if( $spreadsheet_id === $none_value ) {
                continue;
            }

            $options[$spreadsheet_id] = automatorwp_google_sheets_get_spreadsheet_title( $spreadsheet_id );
        }
    }

    return $options;

}

/**
 * Get the spreadsheet title
 *
 * @since 1.0.0
 *
 * @param int $spreadsheet_id
 *
 * @return string|null
 */
function automatorwp_google_sheets_get_spreadsheet_title( $spreadsheet_id ) {

    // Empty title if no ID provided
    if( absint( $spreadsheet_id ) === 0 ) {
        return '';
    }

    $spreadsheets = automatorwp_google_sheets_get_spreadsheets();
    $spreadsheets_name = '';

    foreach( $spreadsheets as $spreadsheet ) {

        if( $spreadsheet['id'] === $spreadsheet_id ) {
            $spreadsheets_name = $spreadsheet['name'];
            break;
        }
    }

    return $spreadsheets_name;

}

/**
 * Get the worksheets
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_google_sheets_get_worksheets( $spreadsheet_id ) {

    $worksheets = array();
    $params = automatorwp_google_sheets_get_request_parameters();

    // Bail if the authorization has not been setup from settings
    if( $params === false ) {
        return $worksheets;
    }

    $url  = 'https://sheets.googleapis.com/v4/spreadsheets/'. $spreadsheet_id;
      
    $response = wp_remote_get( $url, $params );

    $response = json_decode( wp_remote_retrieve_body( $response ), true );

    if( isset( $response['sheets'] ) && is_array( $response['sheets'] ) ) {

        foreach( $response['sheets'] as $key => $sheet ) {
            $worksheets[] = array(
                'id' => $sheet['properties']['sheetId'],
                'title' => $sheet['properties']['title']
            );
        }
    }

    return $worksheets;
}


/**
 * Options callback for select2 fields assigned to worksheets
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_google_sheets_options_cb_worksheets( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any worksheet', 'automatorwp-google-sheets' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    $spreadsheet_id = ct_get_object_meta( $field->object_id, 'spreadsheet', true );

    if( ! is_array( $value ) ) {
        $value = array( $value );
    }

    foreach( $value as $worksheet_id ) {

        // Skip option none
        if( $worksheet_id === $none_value ) {
            continue;
        }

        $options[$worksheet_id] = automatorwp_google_sheets_get_worksheet_title( $spreadsheet_id, $worksheet_id, $field );

    }

    return $options;

}


/**
 * Get the worksheet title
 *
 * @since 1.0.0
 *
 * @param int $spreadsheet_id
 * @param int $worksheet_id
 *
 * @return string|null
 */
function automatorwp_google_sheets_get_worksheet_title( $spreadsheet_id, $worksheet_id, $field ) {

    $spreadsheet = ct_get_object_meta( $field->object_id, 'spreadsheet', true );

    $worksheets = automatorwp_google_sheets_get_worksheets( $spreadsheet );
    
    $worksheet_name = '';

    foreach( $worksheets as $worksheet ) {

        if( $worksheet['id']  ==  $worksheet_id ) {
            $worksheet_name = $worksheet['title'];
            break;
        }

    }
    return $worksheet_name;

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
function automatorwp_google_sheets_get_request_parameters( ) {

    $auth = get_option( 'automatorwp_google_sheets_auth' );
    
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
function automatorwp_google_sheets_refresh_token( ) {

    $client_id = automatorwp_google_sheets_get_option( 'client_id', '' );
    $client_secret = automatorwp_google_sheets_get_option( 'client_secret', '' );

    if( empty( $client_id ) || empty( $client_secret ) ) {
        return false;
    }

    $auth = get_option( 'automatorwp_google_sheets_auth', false );

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

    $response = wp_remote_post( 'https://www.googleapis.com/oauth2/v4/token', $params );

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
        'scope'         => $body->scope,
    );

    // Update the access and refresh tokens
    update_option( 'automatorwp_google_sheets_auth', $auth );

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
function automatorwp_google_sheets_maybe_refresh_token( $response, $args, $url ) {

    // Ensure to only run this check to on Google API request
    if( strpos( $url, 'googleapis.com' ) !== false ) {
        
        $code = wp_remote_retrieve_response_code( $response );
        
        if( $code === 401 ) {

            $access_token = automatorwp_google_sheets_refresh_token( );

            // Send again the request if token gets refreshed successfully
            if( $access_token ) {

                $args['headers']['Authorization'] = 'Bearer ' . $access_token;

                $response = wp_remote_request( $url, $args );

            }

        }

    }

    return $response;

}
add_filter( 'http_response', 'automatorwp_google_sheets_maybe_refresh_token', 10, 3 );
