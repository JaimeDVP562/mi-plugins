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
 * Generación de URL de autorización
 *
 * @return string|false URL de autorización de Constant Contact
 */
function automatorwp_constant_contact_get_authorization_url() {
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $redirect_uri = urlencode( 'http://localhost:8888/pag_wp_cc/wp-admin/admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' );


    var_dump( $client_id ); // Verifica el valor de client_id
    error_log('autorización url llamada'); // Registra en el log de errores

    if( empty( $client_id ) ) {
        return false;
    }

    echo 'https://api.constantcontact.com/v2/oauth2/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&response_type=code';

    return 'https://api.constantcontact.com/v2/oauth2/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&response_type=code';

}

/**
 * Obtener los tokens de acceso y refresco utilizando el código de autorización
 *
 * @param string $authorization_code Código de autorización
 * @return array|false Tokens de acceso y refresco
 */
function automatorwp_constant_contact_get_access_and_refresh_tokens( $authorization_code ) {
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $client_secret = automatorwp_constant_contact_get_option( 'client_secret', '' );
    $redirect_uri = 'http://localhost:8888/pag_wp_cc/wp-admin/admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact';

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

    $response = wp_remote_post( 'https://api.constantcontact.com/v2/oauth2/token', $params );

    if( is_wp_error( $response ) ) {
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Verificamos que los datos no sean falsos antes de guardarlos
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
 * Función para obtener los contactos desde Constant Contact
 *
 * @return array Lista de contactos
 */
function automatorwp_constant_contact_get_contacts() {
    $contacts = array();
    $params = automatorwp_constant_contact_get_request_parameters();

    if( $params === false ) {
        return $contacts;
    }

    $url = 'https://api.constantcontact.com/v2/contacts?limit=1000';
    $response = wp_remote_get( $url, $params );
    $response = json_decode( wp_remote_retrieve_body( $response ), true );

    if( isset( $response['contacts'] ) && is_array( $response['contacts'] ) ) {
        foreach( $response['contacts'] as $contact ) {
            $contacts[] = array(
                'id'    => $contact['id'],
                'name'  => $contact['first_name'] . ' ' . $contact['last_name'],
            );
        }
    }

    return $contacts;
}

/**
 * Obtener los parámetros de solicitud para la API de Constant Contact
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
 * Refresca el token de acceso si es necesario
 *
 * @param array  $response Respuesta de la solicitud
 * @param array  $args Argumentos de la solicitud
 * @param string $url URL de la solicitud
 * @return array Respuesta de la solicitud
 */
function automatorwp_constant_contact_maybe_refresh_token( $response, $args, $url ) {
    if( strpos( $url, 'constantcontact.com' ) !== false ) {
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
 * Refresca el token de acceso utilizando el token de refresco
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
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $auth['refresh_token'],
        )
    );

    $response = wp_remote_post( 'https://api.constantcontact.com/v2/oauth2/token', $params );

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

// Añadir filtro para refrescar el token si es necesario
add_filter( 'http_response', 'automatorwp_constant_contact_maybe_refresh_token', 10, 3 );
