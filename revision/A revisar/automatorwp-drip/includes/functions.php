<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Drip\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the Drip API base URL.
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_drip_get_url() {

    return 'https://api.getdrip.com';

}

/**
 * Helper function to get the Drip API credentials from settings.
 * Returns false if any required field is missing.
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_drip_get_api() {

    $id     = automatorwp_drip_get_option( 'key' );
    $secret = automatorwp_drip_get_option( 'secret' );

    if ( empty( $id ) || empty( $secret ) ) {
        return false;
    }

    return array(
        'url'    => automatorwp_drip_get_url(),
        'token'  => $id,
        'secret' => $secret,
    );

}

/**
 * Unified HTTP request helper for the Drip REST API.
 * All API calls go through this function to avoid duplicating auth headers.
 *
 * Returns an array with 'code' (HTTP status) and 'body' (decoded JSON) keys,
 * following the same pattern as other AutomatorWP integrations (freshdesk, telegram).
 *
 * @since 1.0.0
 *
 * @param string     $endpoint  Path relative to /v2/{account_id}/, e.g. 'subscribers'
 * @param string     $method    HTTP method: GET, POST, PUT, DELETE
 * @param array|null $body      Optional request body as PHP array
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_api_request( $endpoint, $method = 'GET', $body = null, $version = 'v2' ) {

    $api = automatorwp_drip_get_api();

    if ( ! $api ) {
        return array( 'code' => 0, 'body' => array() );
    }

    $base64_auth = base64_encode( $api['secret'] . ':' );

    $args = array(
        'method'  => $method,
        'headers' => array(
            'Authorization' => 'Basic ' . $base64_auth,
            'Content-Type'  => 'application/json',
        ),
        'timeout' => 30,
    );

    if ( ! is_null( $body ) ) {
        $args['body'] = json_encode( $body );
    }

    $url      = $api['url'] . '/' . $version . '/' . $api['token'] . '/' . ltrim( $endpoint, '/' );
    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        return array( 'code' => 0, 'body' => array() );
    }

    $code         = (int) wp_remote_retrieve_response_code( $response );
    $decoded_body = json_decode( wp_remote_retrieve_body( $response ), true );

    return array(
        'code' => $code,
        'body' => is_array( $decoded_body ) ? $decoded_body : array(),
    );

}

// -------------------------------------------------------
// Credential check (used by the Authorize AJAX handler)
// -------------------------------------------------------

/**
 * Test a pair of credentials against the Drip API.
 * Calls wp_send_json_error directly on failure (designed for AJAX context).
 *
 * @since 1.0.0
 *
 * @param string $key    Account ID
 * @param string $secret API Key
 *
 * @return bool
 */
function automatorwp_drip_check_api_key( $key, $secret ) {

    $base64_auth = base64_encode( $secret . ':' );

    $response = wp_remote_get( automatorwp_drip_get_url() . '/v2/' . $key . '/subscribers', array(
        'headers' => array( 'Authorization' => 'Basic ' . $base64_auth ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your Account ID and API Key', 'automatorwp-drip' ) ) );
        return false;
    }

    return true;

}

// -------------------------------------------------------
// Subscriber helpers
// -------------------------------------------------------

/**
 * Create or update a Drip subscriber.
 *
 * @since 1.0.0
 *
 * @param array $subscriber Subscriber data (email, first_name, last_name, tags)
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_create_update_subscriber( $subscriber ) {

    return automatorwp_drip_api_request( 'subscribers', 'POST', array( 'subscribers' => array( $subscriber ) ) );

}

/**
 * Delete a Drip subscriber by email address.
 *
 * @since 1.0.0
 *
 * @param string $email
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_remove_subscriber( $email ) {

    return automatorwp_drip_api_request( 'subscribers/' . rawurlencode( $email ), 'DELETE' );

}

/**
 * Fetch a Drip subscriber by email address.
 * Used internally to resolve the Drip subscriber ID before workflow removal.
 *
 * @since 1.0.0
 *
 * @param string $email
 *
 * @return array|false Subscriber data array or false on failure
 */
function automatorwp_drip_get_subscriber_by_email( $email ) {

    $response = automatorwp_drip_api_request( 'subscribers/' . rawurlencode( $email ) );

    return isset( $response['body']['subscribers'][0] ) ? $response['body']['subscribers'][0] : false;

}

/**
 * Unsubscribe a subscriber from a specific campaign (or all campaigns).
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $campaign_id Leave empty to remove from all campaigns
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_unsubscribe_from_campaign( $email, $campaign_id = '' ) {

    $body = ! empty( $campaign_id ) ? array( 'campaign_id' => $campaign_id ) : array();

    return automatorwp_drip_api_request( 'subscribers/' . rawurlencode( $email ) . '/campaign_unsubscribe', 'POST', $body );

}

/**
 * Unsubscribe a subscriber from all Drip email marketing.
 *
 * @since 1.0.0
 *
 * @param string $email
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_unsubscribe_all( $email ) {

    return automatorwp_drip_api_request( 'unsubscribes/batches', 'POST', array( 'batches' => array( array( 'subscribers' => array( array( 'email' => $email ) ) ) ) ) );

}

// -------------------------------------------------------
// Tag helpers
// -------------------------------------------------------

/**
 * Apply a tag to a Drip subscriber.
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $tag
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_add_tag_subscriber( $email, $tag ) {

    return automatorwp_drip_api_request( 'tags', 'POST', array( 'tags' => array( array( 'email' => $email, 'tag' => $tag ) ) ) );

}

/**
 * Get all tags from the Drip account.
 *
 * @since 1.0.0
 *
 * @return array Flat array of tag name strings
 */
function automatorwp_drip_get_tags() {

    $response = automatorwp_drip_api_request( 'tags' );

    return isset( $response['body']['tags'] ) && is_array( $response['body']['tags'] ) ? $response['body']['tags'] : array();

}

/**
 * Remove a tag from a Drip subscriber.
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $tag
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_remove_tag_subscriber( $email, $tag ) {

    return automatorwp_drip_api_request( 'subscribers/' . rawurlencode( $email ) . '/tags/' . rawurlencode( $tag ), 'DELETE' );

}

// -------------------------------------------------------
// Campaign helpers
// -------------------------------------------------------

/**
 * Get all campaigns from the Drip account.
 *
 * @since 1.0.0
 *
 * @return array Associative array of [ campaign_id => campaign_name ]
 */
function automatorwp_drip_get_campaigns() {

    $response  = automatorwp_drip_api_request( 'campaigns?status=all' );
    $campaigns = array();

    if ( isset( $response['body']['campaigns'] ) ) {
        foreach ( $response['body']['campaigns'] as $campaign ) {
            $campaigns[ $campaign['id'] ] = $campaign['name'];
        }
    }

    return $campaigns;

}

/**
 * Subscribe a subscriber to a Drip campaign.
 *
 * @since 1.0.0
 *
 * @param array  $subscriber_data email, first_name, last_name
 * @param string $campaign_id
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_add_subscriber_campaign( $subscriber_data, $campaign_id ) {

    return automatorwp_drip_api_request( 'campaigns/' . $campaign_id . '/subscribers', 'POST', array( 'subscribers' => array( $subscriber_data ) ) );

}

// -------------------------------------------------------
// Event helpers
// -------------------------------------------------------

/**
 * Record a custom event for a Drip subscriber.
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $action_name  The event action name (e.g. "Logged in")
 * @param array  $properties   Optional key-value pairs attached to the event
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_record_event( $email, $action_name, $properties = array() ) {

    $event = array(
        'email'  => $email,
        'action' => $action_name,
    );

    if ( ! empty( $properties ) ) {
        $event['properties'] = $properties;
    }

    return automatorwp_drip_api_request( 'events', 'POST', array( 'events' => array( $event ) ) );

}

// -------------------------------------------------------
// Workflow helpers
// -------------------------------------------------------

/**
 * Get all workflows from the Drip account.
 *
 * @since 1.0.0
 *
 * @return array Associative array of [ workflow_id => workflow_name ]
 */
function automatorwp_drip_get_workflows() {

    $response  = automatorwp_drip_api_request( 'workflows' );
    $workflows = array();

    if ( isset( $response['body']['workflows'] ) ) {
        foreach ( $response['body']['workflows'] as $workflow ) {
            $workflows[ $workflow['id'] ] = $workflow['name'];
        }
    }

    return $workflows;

}

/**
 * Enroll a subscriber into a Drip workflow.
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $workflow_id
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_enroll_workflow( $email, $workflow_id ) {

    return automatorwp_drip_api_request( 'workflows/' . $workflow_id . '/subscribers', 'POST', array( 'subscribers' => array( array( 'email' => $email ) ) ) );

}

/**
 * Remove a subscriber from a Drip workflow.
 * The Drip remove endpoint requires the subscriber's Drip ID, not their email,
 * so this function fetches the ID internally before making the remove call.
 *
 * @since 1.0.0
 *
 * @param string $email
 * @param string $workflow_id
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_remove_from_workflow( $email, $workflow_id ) {

    $subscriber = automatorwp_drip_get_subscriber_by_email( $email );

    if ( ! $subscriber || empty( $subscriber['id'] ) ) {
        return array( 'code' => 404, 'body' => array() );
    }

    return automatorwp_drip_api_request( 'workflows/' . $workflow_id . '/subscribers/' . $subscriber['id'] . '/remove', 'POST' );

}

// -------------------------------------------------------
// Shopper Activity API v3 helpers
// -------------------------------------------------------

/**
 * Create or update an order via the Drip Shopper Activity API (v3).
 *
 * @since 1.0.0
 *
 * @param array $order Order data (email, action, order_id, grand_total, currency, etc.)
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_create_update_order( $order ) {

    return automatorwp_drip_api_request( 'shopper_activity/order/batch', 'POST', array( 'orders' => array( $order ) ), 'v3' );

}

/**
 * Create or update a cart via the Drip Shopper Activity API (v3).
 *
 * @since 1.0.0
 *
 * @param array $cart Cart data (email, action, cart_id, grand_total, currency, cart_url, etc.)
 *
 * @return array { code: int, body: array }
 */
function automatorwp_drip_create_update_cart( $cart ) {

    return automatorwp_drip_api_request( 'shopper_activity/cart/batch', 'POST', array( 'carts' => array( $cart ) ), 'v3' );

}

// -------------------------------------------------------
// Options callbacks (used by AJAX selector fields)
// -------------------------------------------------------

/**
 * Options cb for campaigns selector — returns label for a stored campaign ID.
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return array
 */
function automatorwp_drip_options_cb_campaign( $value ) {

    if ( empty( $value ) || ! is_scalar( $value ) ) {
        return array();
    }

    return array( $value => $value );

}

/**
 * Options cb for tags selector — returns label for a stored tag name.
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return array
 */
function automatorwp_drip_options_cb_tag( $value ) {

    if ( empty( $value ) || ! is_scalar( $value ) ) {
        return array();
    }

    return array( $value => $value );

}

/**
 * Options cb for workflows selector — returns label for a stored workflow ID.
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return array
 */
function automatorwp_drip_options_cb_workflow( $value ) {

    if ( empty( $value ) || ! is_scalar( $value ) ) {
        return array();
    }

    return array( $value => $value );

}
