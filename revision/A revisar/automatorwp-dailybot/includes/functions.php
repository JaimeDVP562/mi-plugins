<?php
/**
 * Functions
 * 
 * @package     AutomatorWP\Dailybot\Functions
 * @since       1.0.0
 */
//Exit if accessed directly
if(!defined('ABSPATH'))exit;


/**
 * Helper function to get the Daylibot API parameters (URL & Key)
 * @since 1.0.0
 * @return array|false
 */

function automatorwp_dailybot_get_api(){
    
    $url= AUTOMATORWP_DAILYBOT_API_BASE;

    $api_key=automatorwp_dailybot_get_option('key','');

    if(empty($api_key)){
        return false;
    }

    return array(
        'url'       =>$url,
        'api_key'   =>$api_key,
    );
}

/**
 * Helper function to check API key
 * @since 1.0.0
 * @param string $api_key
 * @return bool
 */

function dailybot_check_api_key($api_key){

    $headers=array(
        'Authorization' => 'Bearer ' . $api_key,
        'Accept'        => 'application/json',
    );

    $response = wp_remote_get(automatorwp_dailybot_get_url() . '/auth/validate',array(
        'headers' => $headers
    ));

    //Check for connection errors
    if(is_wp_error($response)){
        return false;
    }

    $status_code=wp_remote_retrieve_response_code($response);

    //Only 200 is considered valid
    if(200!=$status_code){
        return false;
    }

    return true;
}

/**
 * Create a new task in Dailybot
 * @since 1.0.0
 * @param string $title
 * @return int|bool
 */
function automatorwp_dailybot_create_task($title,$description=''){

    $api=automatorwp_dailybot_get_api();

    if(! $api){
        return false;
    }

   $headers = array(
        'Authorization' => 'Bearer ' . $api['api_key'],
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
    );

    $body = array(
        "title"       => sanitize_text_field( $title ),
        "description" => sanitize_textarea_field( $description ),
    );

    $response = wp_remote_post( $api['url'] . '/tasks', array(
        'headers' => $headers,
        'body'    => wp_json_encode( $body ),
    ) );

    if ( is_wp_error( $response ) ) {
        return false;
    }

    return wp_remote_retrieve_response_code( $response ); 

}

/**
 * Get tasks from Dailybot
 * @since 1.0.0
 * @return array
 */
function automatorwp_dailybot_get_tasks(){

    $tasks = array();
    $api = automatorwp_dailybot_get_api();

    if(!$api){
        return $tasks;
    }

    $response = wp_remote_get( $api['url'] . '/tasks', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Accept'        => 'application/json',
        )
    ) );

    if ( is_wp_error( $response ) ) {
        return $tasks;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['data'] ) ) {
        return $tasks;
    }

    foreach ( $body['data'] as $task ) {

        $tasks[] = array(
            'id'   => $task['id'],
            'name' => $task['title'],
        );
    }

    return $tasks;
}


