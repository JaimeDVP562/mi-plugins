<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\Constant_Contact\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Aquí genero la URL para que el usuario pueda autorizar la aplicación de Constant Contact

 *
 * @return string|false URL de autorización de Constant Contact
 */
function automatorwp_constant_contact_get_authorization_url() {
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $redirect_uri = urlencode( admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' ) );

    if( empty( $client_id ) ) {
        return false;
    }

    return 'https://authz.constantcontact.com/oauth2/default/v1/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=contact_data%20campaign_data%20offline_access';

}

/**
 * Con esta función consigo los tokens de acceso y refresco usando el código que me han dado

 *
 * @param string $authorization_code Código de autorización
 * @return array|false Tokens de acceso y refresco
 */
function automatorwp_constant_contact_get_access_and_refresh_tokens( $authorization_code ) {
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $client_secret = automatorwp_constant_contact_get_option( 'client_secret', '' );
    $redirect_uri = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' );

    if( empty( $client_id ) || empty( $client_secret ) || empty( $authorization_code ) ) {
        return false;
    }

    $params = array(
        'body' => array(
            'grant_type'    => 'authorization_code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'code'          => $authorization_code,
            'redirect_uri'  => $redirect_uri
        )
    );

    $response = wp_remote_post( 'https://authz.constantcontact.com/oauth2/default/v1/token', $params );

    if( is_wp_error( $response ) ) {
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Primero compruebo que los datos no sean falsos antes de guardarlos en la base de datos

    if ( isset( $data['access_token'] ) && isset( $data['refresh_token'] ) ) {
        // Verifica que los valores no sean false
        if ( false !== $data['access_token'] && false !== $data['refresh_token'] ) {
            // Guardamos los tokens de manera segura solo si son válidos
            update_option( 'automatorwp_constant_contact_auth', array(
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'token_type'    => $data['token_type'],
                'expires_in'    => $data['expires_in'],
                'scope'         => isset($data['scope']) ? $data['scope'] : null,
            ) );
            return $data;
        } else {
            error_log('Datos de acceso o refresco inválidos');
        }
    }

    return array();
}



/**
 * Esta función es para sacar la lista de contactos de Constant Contact

 *
 * @return array Lista de contactos
 */
function automatorwp_constant_contact_get_contacts() {
    $contacts = array();
    $params = automatorwp_constant_contact_get_request_parameters();

    if( $params === false ) {
        return $contacts;
    }

    $url = 'https://api.cc.email/v3/contacts?limit=50';
    $response = wp_remote_get( $url, $params );
    
    // Comprobar errores
    if ( is_wp_error( $response ) ) {
        return $contacts;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if( isset( $data['contacts'] ) && is_array( $data['contacts'] ) ) {
        foreach( $data['contacts'] as $contact ) {
            $contacts[] = array(
                'id'    => $contact['contact_id'],
                'name'  => $contact['first_name'] . ' ' . $contact['last_name'],
            );
        }
    }

    return $contacts;
}

/**
 * Aquí preparo los parámetros que necesito para llamar a la API

 *
 * @return array|false Parámetros de solicitud
 */
function automatorwp_constant_contact_get_request_parameters() {
    $auth = get_option('automatorwp_constant_contact_auth');

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
 * Si el token ha caducado, intento refrescarlo aquí

 *
 * @param array  $response Respuesta de la solicitud
 * @param array  $args Argumentos de la solicitud
 * @param string $url URL de la solicitud
 * @return array Respuesta de la solicitud
 */
function automatorwp_constant_contact_maybe_refresh_token( $response, $args, $url ) {
    if( strpos( $url, 'cc.email' ) !== false || strpos( $url, 'constantcontact.com' ) !== false ) {
        $code = wp_remote_retrieve_response_code( $response );
        
        if( $code === 401 ) {
            $access_token = automatorwp_constant_contact_refresh_token();
            
            if( $access_token ) {
                $args['headers']['Authorization'] = 'Bearer ' . $access_token;
                $response = wp_remote_request( $url, $args );
            }
        }
    }

    return $response;
}

/**
 * Uso el token de refresco para conseguir un token de acceso nuevo

 *
 * @return string|false Token de acceso renovado o false si falla
 */
function automatorwp_constant_contact_refresh_token() {
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $client_secret = automatorwp_constant_contact_get_option( 'client_secret', '' );

    if( empty( $client_id ) || empty( $client_secret ) ) {
        return false;
    }

    $auth = get_option( 'automatorwp_constant_contact_auth', false );
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
            'refresh_token' => $auth['refresh_token'],
        )
    );

    $response = wp_remote_post( 'https://authz.constantcontact.com/oauth2/default/v1/token', $params );

    if( is_wp_error( $response ) ) {
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ) );

    $auth = array(
        'access_token'  => $body->access_token,
        'refresh_token' => $auth['refresh_token'],
        'token_type'    => $body->token_type,
        'expires_in'    => $body->expires_in,
        'scope'         => $body->scope,
    );

    update_option( 'automatorwp_constant_contact_auth', $auth );

    error_log('Tokens guardados: ' . print_r($data, true));
    return $body->access_token;
}

// Añado un filtro para refrescar el token automágicamente si hace falta

add_filter( 'http_response', 'automatorwp_constant_contact_maybe_refresh_token', 10, 3 );
