<?php
/**
 * Ajax Functions
 * 
 * @package     AutomatorWP\GitHub\Ajax_Functions
 * @author      AutomatorWP
 * @since       1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * GitHub authorize AJAX function
 * 
 * @since 1.0.0
 * 
 */
function automatorwp_github_ajax_authorize(){
    // Security check
    check_ajax_referer('automatorwp_admin' , 'nonce');

    // Permission check
    if( ! current_user_can( automatorwp_get_manager_capability() ) ) {
        wp_send_json_error(__('You\'re not allowed to perform this action.' , AUTOMATORWP_GITHUB_TEXT_DOMAIN));
    }

    $prefix = 'automatorwp_github_';

    // Check parameters given
    if( isset( $_POST['url'] ) && !empty($_POST['url'])  && isset( $_POST['key'] ) && !empty($_POST['key'])&& isset( $_POST['username'] ) && !empty($_POST['username']) && isset( $_POST['webhook_token'] ) && !empty($_POST['webhook_token']) ){
        $url = trim( sanitize_text_field($_POST['url']) );
        $username = trim( sanitize_text_field($_POST['username']) );
        $key = trim(sanitize_text_field($_POST['key']));
        $webhook = trim(sanitize_text_field($_POST['webhook_token']));
    }else{
        wp_send_json_error( array( 'message' => __('All fields are required to connect with GitHub' , AUTOMATORWP_GITHUB_TEXT_DOMAIN) ) );
        return;
    }

    $prefix_token = str_starts_with($key,'github_pat_')? 'Bearer ' : 'token ';


    // To get first answer and check the connection
    $response = wp_remote_get($url , array(
        'headers' => array(
            'Authorization'    => $prefix_token . $key,
            'Accept'       => 'application/vnd.github+json',
        ),
        'sslverify' => true
    ) );

    // Incorrect URL or API key (Personal Access Token)
    if(is_wp_error( $response )){
        wp_send_json_error( array( 'message'=>__('Please, check your credentials' , AUTOMATORWP_GITHUB_TEXT_DOMAIN ) ) );
        return;
    }
    // Get response code
    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        $error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';

        wp_send_json_error( array(
            'message' => __('GitHub error (' . $code . ') : '.$error_message, AUTOMATORWP_GITHUB_TEXT_DOMAIN)
        ) );
        return;
    }

    $settings = get_option( 'automatorwp_github_settings' );

    // Save client username, API key (Personal Access Token) and Webhook_token
    $settings[$prefix . 'username'] = $username;
    $settings[$prefix . 'key'] = $key;
    $settings[$prefix . 'webhook_token'] = $webhook;

    // Update settings
    update_option( 'automatorwp_github_settings', $settings );
    $admin_url = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-github');

    // Send Success JSON
    wp_send_json_success( array(
        'message' => __('Correct data to connect with GitHub',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
        'redirect_url' => $admin_url
    ) );
}
    add_action( 'wp_ajax_automatorwp_github_authorize' , 'automatorwp_github_ajax_authorize' );


