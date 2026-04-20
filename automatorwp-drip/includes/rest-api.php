<?php
/**
 * Rest API - Drip Webhook Endpoint
 *
 * @package     AutomatorWP\Drip\Rest_API
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the Drip webhook endpoint on the WordPress REST API.
 *
 * @since 1.0.0
 */
function automatorwp_drip_rest_api_init() {

    register_rest_route( 'automatorwp-drip/v1', '/webhook', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'automatorwp_drip_rest_api_cb',
        'permission_callback' => '__return_true',
    ) );

}
add_action( 'rest_api_init', 'automatorwp_drip_rest_api_init' );

/**
 * Callback to handle incoming Drip webhook requests.
 * Dispatches a WordPress action per Drip event type.
 *
 * Drip does not document a public HMAC signature header, so source
 * verification relies on validating that payloads contain known account data.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request
 *
 * @return WP_REST_Response
 */
function automatorwp_drip_rest_api_cb( WP_REST_Request $request ) {

    $payload = $request->get_json_params();

    if ( empty( $payload ) || empty( $payload['event'] ) ) {
        return new WP_REST_Response( array( 'success' => false, 'message' => 'Missing event or payload' ), 400 );
    }

    $event      = sanitize_text_field( $payload['event'] );
    $subscriber = isset( $payload['subscriber'] ) ? $payload['subscriber'] : array();
    $props      = isset( $payload['properties'] ) ? $payload['properties'] : array();

    switch ( $event ) {

        // Subscriber lifecycle
        case 'subscriber.created':
            do_action( 'automatorwp_drip_subscriber_created', $subscriber );
            break;

        case 'subscriber.deleted':
            do_action( 'automatorwp_drip_subscriber_deleted', $subscriber );
            break;

        case 'subscriber.reactivated':
            do_action( 'automatorwp_drip_subscriber_reactivated', $subscriber );
            break;

        case 'subscriber.subscribed_to_email_marketing':
            do_action( 'automatorwp_drip_subscribed_to_email_marketing', $subscriber );
            break;

        case 'subscriber.unsubscribed_all':
            do_action( 'automatorwp_drip_unsubscribed_all', $subscriber );
            break;

        case 'subscriber.updated_alias':
            do_action( 'automatorwp_drip_updated_alias', $subscriber, isset( $props['new_email'] ) ? $props['new_email'] : '' );
            break;

        // Tags
        case 'subscriber.applied_tag':
            do_action( 'automatorwp_drip_applied_tag', $subscriber, isset( $props['tag'] ) ? $props['tag'] : '' );
            break;

        case 'subscriber.removed_tag':
            do_action( 'automatorwp_drip_removed_tag', $subscriber, isset( $props['tag'] ) ? $props['tag'] : '' );
            break;

        // Email engagement
        case 'subscriber.received_email':
            do_action( 'automatorwp_drip_received_email', $subscriber, isset( $props['email_subject'] ) ? $props['email_subject'] : '', isset( $props['email_id'] ) ? $props['email_id'] : '' );
            break;

        case 'subscriber.opened_email':
            do_action( 'automatorwp_drip_opened_email', $subscriber, isset( $props['email_subject'] ) ? $props['email_subject'] : '', isset( $props['email_id'] ) ? $props['email_id'] : '' );
            break;

        case 'subscriber.clicked_email':
            do_action( 'automatorwp_drip_clicked_email', $subscriber, isset( $props['email_subject'] ) ? $props['email_subject'] : '', isset( $props['link_url'] ) ? $props['link_url'] : '' );
            break;

        case 'subscriber.clicked_trigger_link':
            do_action( 'automatorwp_drip_clicked_trigger_link', $subscriber, isset( $props['link_url'] ) ? $props['link_url'] : '' );
            break;

        case 'subscriber.bounced':
            do_action( 'automatorwp_drip_bounced', $subscriber, isset( $props['bounce_code'] ) ? $props['bounce_code'] : '' );
            break;

        case 'subscriber.complained':
            do_action( 'automatorwp_drip_complained', $subscriber );
            break;

        // Campaigns
        case 'subscriber.subscribed_to_campaign':
            do_action( 'automatorwp_drip_subscribed_to_campaign', $subscriber, isset( $props['campaign_id'] ) ? $props['campaign_id'] : '' );
            break;

        case 'subscriber.completed_campaign':
            do_action( 'automatorwp_drip_completed_campaign', $subscriber, isset( $props['campaign_id'] ) ? $props['campaign_id'] : '' );
            break;

        case 'subscriber.unsubscribed_from_campaign':
            do_action( 'automatorwp_drip_unsubscribed_from_campaign_trigger', $subscriber, isset( $props['campaign_id'] ) ? $props['campaign_id'] : '' );
            break;

        case 'subscriber.removed_from_campaign':
            do_action( 'automatorwp_drip_removed_from_campaign', $subscriber, isset( $props['campaign_id'] ) ? $props['campaign_id'] : '' );
            break;

        // Data changes
        case 'subscriber.updated_email_address':
            do_action( 'automatorwp_drip_updated_email_address', $subscriber, isset( $props['new_email'] ) ? $props['new_email'] : '' );
            break;

        case 'subscriber.updated_custom_field':
            do_action( 'automatorwp_drip_updated_custom_field', $subscriber, isset( $props['custom_field_identifier'] ) ? $props['custom_field_identifier'] : '', isset( $props['value'] ) ? $props['value'] : '' );
            break;

        case 'subscriber.updated_lifetime_value':
            do_action( 'automatorwp_drip_updated_lifetime_value', $subscriber, isset( $props['value'] ) ? $props['value'] : '' );
            break;

        case 'subscriber.updated_time_zone':
            do_action( 'automatorwp_drip_updated_time_zone', $subscriber, isset( $props['time_zone'] ) ? $props['time_zone'] : '' );
            break;

        case 'subscriber.updated_lead_score':
            do_action( 'automatorwp_drip_updated_lead_score', $subscriber, isset( $props['lead_score'] ) ? $props['lead_score'] : '' );
            break;

        // Behavioral
        case 'subscriber.performed_custom_event':
            do_action( 'automatorwp_drip_performed_custom_event', $subscriber, isset( $props['action'] ) ? $props['action'] : '' );
            break;

        case 'subscriber.visited_page':
            do_action( 'automatorwp_drip_visited_page', $subscriber, isset( $props['url'] ) ? $props['url'] : '' );
            break;

        // Lead scoring / deliverability
        case 'subscriber.became_lead':
            do_action( 'automatorwp_drip_became_lead', $subscriber );
            break;

        case 'subscriber.became_non_prospect':
            do_action( 'automatorwp_drip_became_non_prospect', $subscriber );
            break;

        case 'subscriber.marked_as_deliverable':
            do_action( 'automatorwp_drip_marked_as_deliverable', $subscriber );
            break;

        case 'subscriber.marked_as_undeliverable':
            do_action( 'automatorwp_drip_marked_as_undeliverable', $subscriber );
            break;

    }

    return new WP_REST_Response( array( 'success' => true ), 200 );

}
