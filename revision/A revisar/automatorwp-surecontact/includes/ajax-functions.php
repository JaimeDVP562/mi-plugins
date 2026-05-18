<?php
if( !defined( 'ABSPATH' ) ) exit;

function AutomatorWP_SureContact_ajax_authorize() {

    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'AutomatorWP_SureContact_';
    $token = sanitize_text_field( $_POST['token'] );

    if( empty( $token ) ) {
        wp_send_json_error( array( 'message' => __( 'API Token is required.', 'automatorwp-surecontact' ) ) );
        return;
    }

    $response = wp_remote_get( 'https://api.surecontact.com/api/v1/public/contacts', array(
        'headers' => array(
            'X-API-Key'    => $token,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        )
    ) );

    if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        wp_send_json_error( array( 'message' => __( 'Please check your credentials.', 'automatorwp-surecontact' ) ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings' );
    $settings[$prefix . 'token'] = $token;
    update_option( 'automatorwp_settings', $settings );

    $admin_url = get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-surecontact';

    wp_send_json_success( array(
        'message'      => __( 'Connected with SureContact successfully.', 'automatorwp-surecontact' ),
        'redirect_url' => $admin_url
    ) );

    function AutomatorWP_SureContact_get_lists() {

        check_ajax_referer( 'automatorwp_admin', 'nonce' );

        $settings = get_option( 'automatorwp_settings' );
        $token = $settings['AutomatorWP_SureContact_token'] ?? '';

        if( empty( $token ) ) {
            wp_send_json_error( array( 'message' => 'API token missing.' ) );
        }

        $response = wp_remote_get( 'https://api.surecontact.com/api/v1/public/lists', array(
            'headers' => array(
                'X-API-Key'    => $token,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json'
            )
        ) );

        if( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Request error.' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if( empty( $body ) ) {
            wp_send_json_error( array( 'message' => 'No lists found.' ) );
        }

        $options = array();

        foreach( $body as $list ) {
            $options[] = array(
                'id'   => $list['id'],
                'name' => $list['name']
            );
        }

        wp_send_json_success( $options );
    }
    add_action( 'wp_ajax_AutomatorWP_SureContact_get_lists', 'AutomatorWP_SureContact_get_lists' );

}
add_action( 'wp_ajax_AutomatorWP_SureContact_authorize', 'AutomatorWP_SureContact_ajax_authorize' );