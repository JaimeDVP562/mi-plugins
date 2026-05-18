<?php
/**
 * Rest API
 * 
 * @package   AutomatorWP\Dailybot\Webhooks\Rest_API
 * @author    AutomatorWP
 * @since     1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register recive data from dailybot endpoints on the Wordpress Rest API
 * 
 * @since 1.0.0
 */
function automatorwp_dailybot_rest_api_init() {
    register_rest_route( 'dailybot/v1', '/webhooks', array(
        'methods' => 'POST',
        'callback' =>'automatorwp_dailybot_rest_api_cb',
        'permission_callback' => '__return_true'
    ) );
}
add_action('rest_api_init','automatorwp_dailybot_rest_api_init'); 

/**
 * Callback used to handle dailybot received requests
 * 
 * @since 1.0.0
 * 
 * @param  WP_REST_Request $data
 * 
 * @return WP_REST_Response
 */
function automatorwp_dailybot_rest_api_cb( $data ) {

    // Request response received from dailybot
    $params = $data->get_params();

    if ( ! isset( $params ) ) {
        return new WP_REST_Response( array( 'success'=>false, 'message'=>__('No parameters received',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ), 400 );
    }
    
    // Snitize params array
    $params = map_deep( $params, 'sanitize_text_field' );

    // Sanitize specific fields
    if( isset( $params['email'] ) ) {
        $params['email'] = sanitize_email( $params['email'] );
     $email = $params['email'];
     $user = get_user_by( 'email',$email);
    }
     $user = $user ? $user->ID : 0;
   
    if($params['results']['status'] === 'resolved'){
        do_action( 'automatorwp_dailybot_invitation_accepted', $params, $user );
    }

    if($params['results']['status'] ==='pending'){
        do_action('automatorwp_dailybot_invitation_created', $params, $user);
    }

    return new WP_REST_Response( array( 'success'=> true ), 200 );
}