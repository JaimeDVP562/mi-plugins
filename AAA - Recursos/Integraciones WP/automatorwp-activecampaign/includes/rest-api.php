<?php
/**
 * Rest API
 *
 * @package     AutomatorWP\Webhooks\Rest_API
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register receive data from activecampaign endpoints on the WordPress Rest API
 *
 * @since 1.0.0
 */
function automatorwp_activecampaign_rest_api_init() {

    register_rest_route( 'activecampaign/webhooks', automatorwp_activecampaign_get_webhook_slug(), array(
        'methods' => 'POST',
        'callback' => 'automatorwp_activecampaign_rest_api_cb',
        'permission_callback' => '__return_true',
    ) );

}
add_action( 'rest_api_init', 'automatorwp_activecampaign_rest_api_init');

/**
 * Callback used to handle activecampaign received requests
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $data
 *
 * @return WP_REST_Response
 */
function automatorwp_activecampaign_rest_api_cb( $data ) {

    // Request response received from ActiveCampaign
    $params = $data->get_params();

    if ( ! isset( $params ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => __( 'No parameters received', 'automatorwp-activecampaign' ) ), 400 );
    }

    $type = sanitize_text_field( $params['type'] );
    $email = sanitize_text_field( $params['contact']['email'] );
    $user = get_user_by( 'email', $email );
    
    // Actions when a user/contact is subscribed or added to list
    if ( $type === 'subscribe' ) {

        if ( absint( $params['list'] ) !== 0 ) {
            do_action( 'automatorwp_activecampaign_contact_list_added', $params );
            do_action( 'automatorwp_activecampaign_user_list_added', $params, $user->ID );
        } else {
            do_action( 'automatorwp_activecampaign_contact_subscribed', $params );
            do_action( 'automatorwp_activecampaign_user_subscribed', $params, $user->ID );
        }
        
    }

    // Actions when a user/contact is unsubscribed from a list
    if ( $type === 'unsubscribe' ) {

        do_action( 'automatorwp_activecampaign_contact_unsubscribed', $params );
        do_action( 'automatorwp_activecampaign_user_unsubscribed', $params, $user->ID );

    }

    // Actions when a tag is added to user/contact
    if ( $type === 'contact_tag_added') {

        do_action( 'automatorwp_activecampaign_contact_tag_added', $params );
        do_action( 'automatorwp_activecampaign_user_tag_added', $params, $user->ID );

    }

    // Actions when a tag is removed from user/contact
    if ( $type === 'contact_tag_removed') {

        do_action( 'automatorwp_activecampaign_contact_tag_removed', $params );
        do_action( 'automatorwp_activecampaign_user_tag_removed', $params, $user->ID );

    }

    return new WP_REST_Response( array( 'success' => true ), 200 );

}