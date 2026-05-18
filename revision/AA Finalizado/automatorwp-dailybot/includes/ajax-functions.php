<?php
/**
 * Ajax Functions
 *
 * @since    1.0.0
 * @package  AutomatorWP\Dailybot\Ajax_Functions
 * @author   AutomatorWP
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_dailybot_ajax_authorize() {

    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if ( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );
    }

    $prefix = 'automatorwp_dailybot_';
    $key    = sanitize_text_field( $_POST['key'] );

    // Check parameters given
    if ( empty( $key ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with Dailybot.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) ) );
        return;
    }

    // Test connection using /users/me endpoint
    $url      = AUTOMATORWP_DAILYBOT_API_BASE . 'users/me/';
    $response = wp_remote_get( $url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY'    => $key,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        return;
    }

    $response_code = wp_remote_retrieve_response_code( $response );

    if ( $response_code !== 200 ) {
        wp_send_json_error( array( 'message' => __( 'Please check your credentials.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) ) );
        return;
    }

    $settings = get_option( 'automatorwp_settings', array() );
    $settings[ $prefix . 'key' ] = $key;
    update_option( 'automatorwp_settings', $settings );

    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-dailybot' );

    wp_send_json_success( array(
        'message'      => __( 'Connected with Dailybot successfully.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
        'redirect_url' => $admin_url,
    ) );
}
add_action( 'wp_ajax_automatorwp_dailybot_authorize', 'automatorwp_dailybot_ajax_authorize' );

/**
 * Get Dailybot user UUID by full name
 *
 * @since 1.0.0
 *
 * @param string $username Full name to search
 *
 * @return string|false UUID or false on failure
 */
function automatorwp_dailybot_get_uuid( $username ) {

    $url = AUTOMATORWP_DAILYBOT_API_BASE . 'users/';
    $key = automatorwp_dailybot_get_option( 'key', '' );

    $response = wp_remote_get( $url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY'    => $key,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    // DailyBot returns { "count": N, "results": [ { "uuid": "...", "full_name": "..." } ] }
    if ( empty( $body['results'] ) || ! is_array( $body['results'] ) ) {
        return false;
    }

    foreach ( $body['results'] as $user ) {
        if ( isset( $user['uuid'], $user['full_name'] ) && $user['full_name'] === trim( $username ) ) {
            return $user['uuid'];
        }
    }

    return false;
}

/**
 * Send email via Dailybot
 *
 * @since 1.0.0
 *
 * @param string $uuid          Dailybot user UUID
 * @param string $email_subject Email subject
 * @param string $email_content Email body content
 *
 * @return array
 */
function automatorwp_dailybot_send_email( $uuid, $email_subject, $email_content ) {

    $url = AUTOMATORWP_DAILYBOT_API_BASE . 'send-email/';
    $key = automatorwp_dailybot_get_option( 'key', '' );

    $response = wp_remote_post( $url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY'    => $key,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'users_uuids'   => array( $uuid ),
            'email_subject' => $email_subject,
            'email_content' => $email_content,
        ) ),
    ) );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return array( 'success' => false, 'message' => __( 'Email could not be sent.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );
    }

    return array( 'success' => true, 'message' => __( 'Email sent successfully.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );
}

/**
 * Send message via Dailybot
 *
 * @since 1.0.0
 *
 * @param string $uuid    Dailybot user UUID
 * @param string $message Message content
 *
 * @return array
 */
function automatorwp_dailybot_send_message( $uuid, $message ) {

    $url = AUTOMATORWP_DAILYBOT_API_BASE . 'send-message/';
    $key = automatorwp_dailybot_get_option( 'key', '' );

    $response = wp_remote_post( $url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY'    => $key,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'message'      => $message,
            'target_users' => array( $uuid ),
        ) ),
    ) );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return array( 'success' => false, 'message' => __( 'Message could not be sent.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );
    }

    return array( 'success' => true, 'message' => __( 'Message sent successfully.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );
}

/**
 * Open conversation via Dailybot
 *
 * @since 1.0.0
 *
 * @param string $uuid Dailybot user UUID
 *
 * @return array
 */
function automatorwp_dailybot_open_conversation( $uuid ) {

    $url = AUTOMATORWP_DAILYBOT_API_BASE . 'open-conversation/';
    $key = automatorwp_dailybot_get_option( 'key', '' );

    $response = wp_remote_post( $url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY'    => $key,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ),
        'body' => wp_json_encode( array(
            'target_users' => array( $uuid ),
        ) ),
    ) );

    $code         = wp_remote_retrieve_response_code( $response );
    $body_content = wp_remote_retrieve_body( $response );
    $data         = json_decode( $body_content, true );

    if ( is_wp_error( $response ) || $code !== 200 ) {
        $detail = isset( $data['detail'] ) ? $data['detail'] : __( 'Failed to open conversation.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN );
        return array( 'success' => false, 'message' => $detail );
    }

    $result = array( 'success' => true, 'message' => __( 'Conversation opened successfully.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ) );

    if ( isset( $data['channel'] ) ) {
        $result['channel'] = $data['channel'];
    }

    return $result;
}