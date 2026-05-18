<?php
/**
 * Ajax-Functions
 *  
 * @since    1.0.0
 * @package  AutomatorWP\Dailybot\Ajax-Functions
 * @author   AutomatorWP
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * AJAX handler for the authorize action
 * 
 * @since 1.0.0
 */
function automatorwp_dailybot_ajax_authorize(){
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error(__('You\'re not allowed to perform this action.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) );
    }

    $prefix = 'automatorwp_dailybot_';

    $url = AUTOMATORWP_DAILYBOT_API_BASE . 'checkins/';
    $key = sanitize_text_field( $_POST['key'] );

    // Check parameters given
    if( empty($key) ) {
        wp_send_json_error( array( 'message' => __('All fields are required to connect with Dailybot',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ) );
        return;
    }

    // To get first answer and check the connection
    $response = wp_remote_get( $url, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Authorization' =>'Bearer ' . $key,
            'Content-Type' => 'application/json'
        )
    ) );

    if(is_wp_error( $response )) {
        wp_send_json_error( array('message' => $response->get_error_message() ) );
        return;
    }

    // Incorrect URL or API key
    $response_code = wp_remote_retrieve_response_code( $response );
    if( $response_code !== 200 ){
        wp_send_json_error( array('message'=> __('Please, check your creadentials', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN)) );
        return;
    }
    $settings = get_option( 'automatorwp_dailybot_settings' );

    // Save client API key
    $settings[$prefix . 'key'] = $key;

    // Update settings
    update_option( 'automatorwp_dailybot_settings', $settings );
    $admin_url = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-dailybot');

    wp_send_json_success( array(
        'message' => __('Correct data to connect with Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
        'redirect_url' => $admin_url

    ));
}
add_action('wp_ajax_automatorwp_dailybot_authorize', 'automatorwp_dailybot_ajax_authorize');

/**
 * Get Dailybot user uuid
 * 
 * @since 1.0.0
 *
 * @param string $username username to get uuid
 *  
 * @return string|false
 */
function automatorwp_dailybot_get_uuid($username){
    $url = AUTOMATORWP_DAILYBOT_API_BASE.'users/';
    $key = automatorwp_dailybot_get_option('key','');

    $response = wp_remote_get($url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => array(
            'X-API-KEY' => 'Bearer '.$key
        )
    ));

    // Check response
    $code = wp_remote_retrieve_response_code($response);

    if( is_wp_error($response) && $code !== 200 ){
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    

    if( !empty( $body ) && is_array( $body ) ) {
        foreach ( $body as $user ) {

            if( isset($user['results']['uuid']) && isset($user['results']['full_name']) && $user['results']['full_name'] === trim($username) ){
               return $user['results']['uuid'];
            }
        }
    }

    return false;
}

/**
 * Dailybot send email
 * 
 * @since 1.0.0
 * 
 * @param string $uuid dailybot uuid user 
 * @param string $email_subject email subject
 * @param string $email_content email content
 * 
 * @return array
 */
function automatorwp_dailybot_send_email($uuid,$email_subject,$email_content){
     $url = AUTOMATORWP_DAILYBOT_API_BASE.'send-email/';
     $key = automatorwp_dailybot_get_option('key','');

    $headers= array(
        'X-API-KEY' => 'Bearer '.$key,
        'Content-Type' => 'application/json'
    );

    $body = array(
        'users_uuids'   => array($uuid),
        'email_subject' => $email_subject,
        'email_content' => $email_content
    );

     $response = wp_remote_post($url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => $headers,
        'body' => json_encode( $body )
     ));

    // Check response
    $code = wp_remote_retrieve_response_code($response);

    if( is_wp_error($response) && $code !== 200 ){
        return array(
            'Success' => __('No sended email', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN));      
    }else{
        return array(
            'Success' => __(' successful email sended', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN)
        );
    }
    
}

/**
 * Dailybot send message
 * 
 * @since 1.0.0
 * 
 * @param string $uuid dailybot uuid user
 * @param string $message dailybot send message
 * 
 * @return array
 */
function automatorwp_dailybot_send_message($uuid,$message){
     $url = AUTOMATORWP_DAILYBOT_API_BASE.'send-message/';
     $key = automatorwp_dailybot_get_option('key','');

         $headers= array(
        'X-API-KEY' => 'Bearer '.$key,
        'Content-Type' => 'application/json'
    );

     $body = array(
        'message'   => $message,
        'target_users' => array($uuid)
    );

         $response = wp_remote_post($url, array(
        'timeout' => AUTOMATORWP_DAILYBOT_TIMEOUT,
        'headers' => $headers,
        'body' => json_encode( $body )
     ));

    // Check response
    $code = wp_remote_retrieve_response_code($response);

    if( is_wp_error($response) && $code !== 200 ){
        return array(
            'Success' => __('No sended message', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN));      
    }else{
        return array(
            'Success' => __(' successful message sended', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN)
        );
    }
}

function automatorwp_dailybot_open_conversation($uuid){
    $url = AUTOMATORWP_DAILYBOT_API_BASE.'open-conversation/';
    $key = automatorwp_dailybot_get_option('key','');

    $headers= array(
        'Authorization' => 'Bearer '.$key,
        'Content-Type' => 'application/json'
    );

    $body = array(
        'target_users' => array($uuid)
    );

    $response = wp_remote_post($url, array(
        'timeout' => 15,
        'headers' => $headers,
        'body' => json_encode( $body )
    ));

    // Check response
    $code = wp_remote_retrieve_response_code($response);
    $body_content = wp_remote_retrieve_body($response);
    
    $data = json_decode($body_content, true); 

    if( is_wp_error($response) || $code !== 200 ){ 
        if(isset($data['detail']) && isset($data['code'])){
            return array(
                'Success' => __('No sended message', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
                'Detail'  => $data['detail']
            );
        }
        return array(
            'Success' => __('Failed to open conversation.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN)
        );      
    } else {
        if(isset($data['channel'])){
            return array(
                'Success' => __(' successful message sended', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
                'Channel' => $data['channel']
            );
        }
        return array(
            'Success' => __(' successful message sended', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN)
        );
    }
}