<?php
/**
 * Functions
 *
 * @package     AutomatorWP\CampaignMonitor\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the Campaign Monitor url
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_campaign_monitor_get_url() {
    return 'https://api.createsend.com/api/v3.3/';
}

/**
 * Helper function to get the Campaign Monitor API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_campaign_monitor_get_api() {

    $url = automatorwp_campaign_monitor_get_url();
    $client_id = automatorwp_campaign_monitor_get_option('client_id', '');
    $api_key = automatorwp_campaign_monitor_get_option('api_key', '');

    if (empty($client_id) || empty($api_key)) {
        return false;
    }

    return array(
        'url' => $url,
        'client_id' => $client_id,
        'api_key' => $api_key,
    );

}


/**
 * Get lists from Campaign Monitor
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_campaign_monitor_get_lists() {

    $lists = array();

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return $lists;
    }

    // To get lists
    $response = wp_remote_get( $api['url'] . "clients/" . $api['client_id'] . "/lists.json", array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api['api_key'] ),
        )
    ));

    if ( is_wp_error( $response ) ) {
        return $lists;
    }

    $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

    // Check for errors in the response
    if ( is_array( $response_body ) ) {

        foreach ( $response_body as $list ) {

            if ( isset( $list['ListID'] ) && isset( $list['Name'] ) ) {

                $lists[] = array(
                    'id'   => $list['ListID'],
                    'name' => $list['Name'],
                );
            }
        }
    }

    return $lists;
}


/**
 * Get list from Campaign Monitor
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_campaign_monitor_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $list_id ) {

            // Skip option none
            if( $list_id === $none_value ) {
                continue;
            }

            $options[$list_id] = automatorwp_campaign_monitor_get_list_title( $list_id );
        }
    }

    return $options;
}

/**
 * Get the list name
 *
 * @since 1.0.0
 * 
 * @param string $list_id
 *
 * @return array
 */
function automatorwp_campaign_monitor_get_list_title( $list_id ) {

    if( empty( $list_id ) ) {
        return '';
    }

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return '';
    }

    // To get list details
    $response = wp_remote_get( $api['url'] . 'lists/' . $list_id . '.json', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api['api_key'] ) ),
           
        )
    );

    if ( is_wp_error( $response ) ) {
        return '';
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    
    if ( $response_code !== 200 ) {
        return '';
    }   

    $list = json_decode( wp_remote_retrieve_body( $response ), true );
    
    return $list['Title'];
}

/**
 * Get subscriber to list
 *
 * @since 1.0.0
 * 
 * @param string $list_id
 * @param string $subscriber_email
 *
 * @return array
 */
function automatorwp_campaign_monitor_get_subscriber( $list_id, $subscriber_email ) {

    $lists = array();

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return $lists;
    }

    // To get subscriber
    $url = $api['url'] . "subscribers/" . $list_id . ".json?email=" . urlencode( $subscriber_email );
    
    $response = wp_remote_get( $url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api['api_key'] ),
        ),
    ) );
 
    if ( is_wp_error( $response ) ) {
        return;
    }

    return $response;
}

/**
 * Add subscriber to list
 *
 * @since 1.0.0
 *
 * @param string $list_id
 * @param array $subscriber_data
 * 
 * @return array
 */
function automatorwp_campaign_monitor_add_subscriber( $list_id, $subscriber_data ) {

    $lists = array();

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return $lists;
    }

    //add subscriber
    $url = $api['url'] . "subscribers/" . $list_id . ".json";

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api['api_key'] ),
        ),
        'body' => json_encode( $subscriber_data ),
    ));
 
    if ( is_wp_error( $response ) ) {
        return;
    }

    return $response;
}

/**
 * Update subscriber to list
 *
 * @since 1.0.0
 *
 * @param string $list_id
 * @param array $subscriber_data
 * @param string $subscriber_email
 * 
 * @return array
 */
function automatorwp_campaign_monitor_update_subscriber( $list_id, $subscriber_data, $subscriber_email ) {

    $lists = array();

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return $lists;
    }

    // Update subscriber
    $url = $api['url'] . "subscribers/" . $list_id . ".json?email=" . urlencode( $subscriber_email );

    $response = wp_remote_request($url, array(
        'method' => 'PUT',
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $api['api_key'] ),
        ),
        'body' => json_encode( $subscriber_data ),
    ));


    if ( is_wp_error( $response ) ) {
        return;
    }

    return $response;
}

/**
 * Remove subscriber from list
 *
 * @since 1.0.0
 * 
 * @param string $list_id
 * @param string $subscriber_email
 *
 * @return array
 */
function automatorwp_campaign_monitor_delete_subscriber( $list_id, $subscriber_email ) {

    $lists = array();

    $api = automatorwp_campaign_monitor_get_api();

    if ( !$api ) {
        return $lists;
    }

    // To remove subscriber
    $url = $api['url'] . "subscribers/" . $list_id . ".json?email=" . urlencode( $subscriber_email );

    $response = wp_remote_request( $url, array(
        'method' => 'DELETE',
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] ),
        ),
    ));

    if (is_wp_error($response)) {
        return;
    }

    return $response;
}