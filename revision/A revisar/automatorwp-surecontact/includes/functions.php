<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function AutomatorWP_SureContact_get_api() {

    $api_key = AutomatorWP_SureContact_get_option( 'token', '' );

    if( empty( $api_key ) ) {
        return false;
    }

    return array(
        'url'     => 'https://api.surecontact.com/api/v1/public',
        'api_key' => $api_key,
    );

}
function AutomatorWP_SureContact_create_contact( $email, $first_name, $last_name ) {

    $api = AutomatorWP_SureContact_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/contacts', array(
        'headers' => array(
            'X-API-Key'    => $api['api_key'],
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode( array(
            'primary_fields' => array(
                'email'      => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
            )
        ) )
    ) );

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if( isset( $body['data']['uuid'] ) ) {
        return $body['data']['uuid'];
    }

    return false;

}
function AutomatorWP_SureContact_attach_tag( $contact_uuid, $tag_uuid ) {

    $api = AutomatorWP_SureContact_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/contacts/' . $contact_uuid . '/tags/attach', array(
        'headers' => array(
            'X-API-Key'    => $api['api_key'],
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode( array(
            'tag_uuids' => array( $tag_uuid )
        ) )
    ) );

    $code = wp_remote_retrieve_response_code( $response );

    return $code === 200;

}

function AutomatorWP_SureContact_attach_list( $contact_uuid, $list_uuid ) {

    $api = AutomatorWP_SureContact_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/contacts/' . $contact_uuid . '/lists/attach', array(
        'headers' => array(
            'X-API-Key'    => $api['api_key'],
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode( array(
            'list_uuids' => array( $list_uuid )
        ) )
    ) );

    $code = wp_remote_retrieve_response_code( $response );

    return $code === 200;

}

function AutomatorWP_SureContact_get_api_tags() {

    $tags = array();

    $api = AutomatorWP_SureContact_get_api();

    if( ! $api ) {
        return $tags;
    }

    $response = wp_remote_get( $api['url'] . '/tags', array(
        'headers' => array(
            'X-API-Key'    => $api['api_key'],
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        )
    ) );

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if( empty( $body['data'] ) ) {
        return $tags;
    }

    foreach( $body['data'] as $tag ) {
        $tags[] = array(
            'id'   => $tag['uuid'],
            'name' => $tag['name'],
        );
    }

    return $tags;

}
function AutomatorWP_SureContact_get_lists() {

    $lists = array();

    $api = AutomatorWP_SureContact_get_api();

    if( ! $api ) {
        return $lists;
    }

    $response = wp_remote_get( $api['url'] . '/lists', array(
        'headers' => array(
            'X-API-Key'    => $api['api_key'],
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        )
    ) );

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if( empty( $body['data'] ) ) {
        return $lists;
    }

    foreach( $body['data'] as $list ) {
        $lists[] = array(
            'id'   => $list['uuid'],
            'name' => $list['name'],
        );
    }

    return $lists;

}
