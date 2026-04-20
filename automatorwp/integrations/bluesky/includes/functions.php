<?php

/**
 * Helper function to get the Bluesky API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_bluesky_get_api() {

    $user_handle = automatorwp_bluesky_get_option( 'user_handle', '' );
    $user_password = automatorwp_bluesky_get_option( 'user_password', '' );
    $base_url = 'https://bsky.social/xrpc';
    $chat_url = 'https://api.bsky.chat/xrpc';

    if( empty( $user_handle ) || empty( $user_password ) ) {
        return false;
    }

    return array(
        'user_handle' => $user_handle,
        'user_password' => $user_password,
        'base_url' => $base_url,
        'chat_url' => $chat_url,
    );
}

/**
 * Function to check the user credentials with a POST request to the Bluesky API
 * Also saving the refresh token and user DID
 * 
 * @param mixed $credentials - User credentials
 * 
 * @return bool - True if the credentials are correct, false otherwise
 */
function automatorwp_bluesky_check_settings_status( $credentials ) {

    $return = false;

    // Preparar los datos en formato JSON
    $body = json_encode( array(
        'identifier' => $credentials['user_handle'],
        'password'   => $credentials['user_password'],
    ) );

    // Arguments for the POST request
    $args = array(
        'body'        => $body,
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'     => 20,
        'data_format' => 'body',
    );

    
    // POST request to the Bluesky API
    $response = wp_remote_post( 'https://bsky.social/xrpc/com.atproto.server.createSession', $args );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API credentials', 'automatorwp' ) ) );
        return $return;
    } else {
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        // Check if the response has the refresh token
        if ( isset( $data['refreshJwt'] ) ) {
            // Update refresh token and user did
            update_option( 'bluesky_refresh_token', $data['refreshJwt'] );
            update_option( 'bluesky_did', $data['did'] );
            $return = true;
        } else {
            wp_send_json_error( array( 'message' => __( 'Refresh token not found in the response.', 'automatorwp' ) ) );
            return $return;
        }
        $return = true;
    }

    return $return;
}

/**
 * Get access token from Bluesky and save the new refresh token
 * 
 * @return string|false - Access token
 */
function automatorwp_bluesky_get_access_token( ) {
    $api = automatorwp_bluesky_get_api();

    $refresh_token = get_option( 'bluesky_refresh_token' );

    if ( !$refresh_token || !$api ) {
        return false;
    }

    $args = array(
        'headers'     => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $refresh_token,
        ),
        'timeout'     => 20,
        'data_format' => 'body',
    );

    $response = wp_remote_post( $api["base_url"] . '/com.atproto.server.refreshSession', $args );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        return false;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $data['accessJwt'] ) ) {
        if ( isset( $data['refreshJwt'] ) ) {
            update_option( 'bluesky_refresh_token', $data['refreshJwt'] );
        }
        return $data['accessJwt'];
    } else {
        return false;
    }
}

/**
 * Add post to bluesky
 *
 * @since 1.0.0
 * 
 * @param string    $post_content      Content of the post
 * 
 * @return int - Status code
 */
function automatorwp_bluesky_create_post( $post_content ) {

    $api = automatorwp_bluesky_get_api();

    if( ! $api ) {
        return;
    }

    $access_token = automatorwp_bluesky_get_access_token();

    if ( ! $access_token ) {
        return;
    }

    $date = new DateTime( 'now', new DateTimeZone('UTC'));
    $created_at = $date->format('Y-m-d\TH:i:s\Z');

    $body = array(
        'repo'      => $api['user_handle'],
        'collection'=> 'app.bsky.feed.post',
        'record'    => array(
            'text'      => $post_content,
            'createdAt' => $created_at,
        ),
    );

    $args = array(
        'body'    => json_encode( $body ),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api['base_url'] . '/com.atproto.repo.createRecord', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Get user DID from bluesky
 * 
 * @param mixed $handle - User handle
 * @param mixed $access_token - Access token
 * 
 * @return string|false - User DID
 */
function automatorwp_bluesky_get_user_did($handle, $access_token) {
    $api = automatorwp_bluesky_get_api();

    if (!$handle || !$access_token) {
        return false;
    }

    $args = array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $response = wp_remote_get( $api["base_url"] . "/app.bsky.actor.getProfile?actor=" . $handle, $args );

    if ( is_wp_error( $response ) ) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );

    if ($status_code != 200) {
        return false;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $data['did'] ) ) {
        return $data['did'];
    }
}

/**
 * Get conversation ID from bluesky
 * 
 * @param mixed $to_handle - User handle to send the message
 * @param mixed $access_token - Access token
 * 
 * @return string|false - Convo ID
 */
function automatorwp_bluesky_get_convo($to_handle, $access_token) {
    $api = automatorwp_bluesky_get_api();
    $from_did = get_option( 'bluesky_did' );
    $to_did = automatorwp_bluesky_get_user_did( $to_handle, $access_token );

    if (!$to_did || !$from_did) {
        return false;
    }

    $url = $api["chat_url"] . '/chat.bsky.convo.getConvoForMembers?members=' . $from_did . '&members=' . $to_did;

    $args = array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $response = wp_remote_get( $url, $args );
    
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        return false;
    }

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( null === $data ) {
        return false;
    }

    // Check if data is array
    if ( !is_array(($data)) ) {
        return false;
    }

    if (isset($data['convo']) && isset($data['convo']['id'])) {
        return $data['convo']['id'];
    } else {
        return false;
    }
}

/**
 * Send a menssage on bluesky
 *
 * @since 1.0.0
 * 
 * @param string    $message_content      Content of the message
 * @param string    $to_user              User handle to send the message
 * 
 * @return int - Status code
 */
function automatorwp_bluesky_send_message( $message_content, $to_user ) {

    $api = automatorwp_bluesky_get_api();

    if( ! $api ) {
        return;
    }

    $access_token = automatorwp_bluesky_get_access_token();

    if ( !$access_token ) {
        return;
    }

    $convo = automatorwp_bluesky_get_convo($to_user, $access_token);

    if (!$convo) {
        return;
    }

    $body = array(
        'convoId'      => $convo,
        'message'    => array(
            'text'      => $message_content,
        ),
    );

    $args = array(
        'body'    => json_encode( $body ),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api["chat_url"] . '/chat.bsky.convo.sendMessage', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Function to download an image from URI and upload it to bluesky
 * 
 * @param mixed $post_image - Image URI
 * @param mixed $access_token - Access token
 * 
 * @return object|false - Blob object
 */
function automatorwp_bluesky_upload_image_to_blob( $post_image, $access_token ) {
    $api = automatorwp_bluesky_get_api();

    if (!$post_image || !$access_token || !$api) {
        return;
    }

    $image_response = wp_remote_get( $post_image, array( 'timeout' => 20 ) );

    if ( is_wp_error( $image_response ) ) {
        return;
    }

    $image_data = wp_remote_retrieve_body( $image_response );

    if ( empty( $image_data ) ) {
        return;
    }

    $image_size = strlen( $image_data );
    // 1,000,000 bytes limit
    if ( $image_size > 1000000 ) {
        return;
    }

    $image_mime = wp_remote_retrieve_header( $image_response, 'content-type' );

    if ( empty( $image_mime ) ) {
        $image_mime = 'image/jpeg';
    }

    $upload_args = array(
        'body'    => $image_data,
        'headers' => array(
            'Content-Type'  => $image_mime,
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $upload_response = wp_remote_post( $api['base_url'] . '/com.atproto.repo.uploadBlob', $upload_args );
    if ( is_wp_error( $upload_response ) ) {
        return $upload_response;
    }

    $upload_body = wp_remote_retrieve_body( $upload_response );
    $upload_data = json_decode( $upload_body, true );

    if ( ! isset( $upload_data['blob'] ) ) {
        return new WP_Error( 'upload_failed', 'Error uploading file.' );
    }

    $blob = $upload_data['blob'];

    return $blob;
}

/**
 * Create a post with an image on bluesky
 *
 * @since 1.0.0
 * 
 * @param string    $post_content         Content of the post
 * @param string    $post_image           Image uri
 * 
 * @return int - Status code
 */
function automatorwp_bluesky_create_post_with_image( $post_content, $image ) {

    $api = automatorwp_bluesky_get_api();

    if( ! $api ) {
        return;
    }

    $access_token = automatorwp_bluesky_get_access_token();

    if ( ! $access_token ) {
        return;
    }

    $image_blob = automatorwp_bluesky_upload_image_to_blob( $image, $access_token );

    if ( ! $image_blob ) {
        return;
    }

    // Get current date in UTC
    $date = new DateTime( 'now', new DateTimeZone('UTC'));
    $created_at = $date->format('Y-m-d\TH:i:s\Z');

    // Prepare data to send on post request
    $embed = array(
        '$type'  => 'app.bsky.embed.images',
        'images' => array(
            array(
                'image' => $image_blob,
                'alt'   => 'post image',
            ),
        ),
    );

    $body = array(
        'repo'      => $api['user_handle'],
        'collection'=> 'app.bsky.feed.post',
        'record'    => array(
            '$type' => "app.bsky.feed.post",
            'text'      => $post_content,
            'createdAt' => $created_at,
            'embed' => $embed,
        ),
    );

    $args = array(
        'body'    => json_encode( $body ),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api['base_url'] . '/com.atproto.repo.createRecord', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Sanitize name account
 * 
 * @param string $account  The name account
 *
 * @since 1.0.0
 *
 * @return string|false|WP_Error
 */
function automatorwp_bluesky_validate_name_account( $account ) {

    // Filter the characters from string
    $account = preg_replace('/[^a-zA-Z0-9.]/', '', $account);

    return $account;

}