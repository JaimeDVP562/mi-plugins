<?php
/**
 * Keap API Functions
 * Extended API functions for Keap integration
 *
 * @package     AutomatorWP\Integrations\Keap\API
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Create a new contact in Keap
 * Endpoint: POST /crm/rest/v2/contacts
 *
 * @since 1.0.0
 *
 * @param array $contact_data Contact information
 *
 * @return array|false
 */
function automatorwp_keap_create_contact( $contact_data ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    // Build Keap v2 contact payload
    $payload = array();

    // Email address — Keap v2 requires nested email_addresses array
    if ( ! empty( $contact_data['email'] ) ) {
        $payload['email_addresses'] = array(
            array(
                'email' => sanitize_email( $contact_data['email'] ),
                'field' => 'EMAIL1',
            )
        );
    }

    if ( ! empty( $contact_data['first_name'] ) ) {
        $payload['given_name'] = sanitize_text_field( $contact_data['first_name'] );
    }

    if ( ! empty( $contact_data['last_name'] ) ) {
        $payload['family_name'] = sanitize_text_field( $contact_data['last_name'] );
    }

    // Phone — Keap v2 requires nested phone_numbers array
    if ( ! empty( $contact_data['phone'] ) ) {
        $payload['phone_numbers'] = array(
            array(
                'number' => sanitize_text_field( $contact_data['phone'] ),
                'field'  => 'PHONE1',
            )
        );
    }

    if ( ! empty( $contact_data['company'] ) ) {
        $payload['company'] = array(
            'name' => sanitize_text_field( $contact_data['company'] ),
        );
    }

    $response = wp_remote_post(
        $api['url_v2'] . '/contacts',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $data = automatorwp_keap_handle_api_response( $response, 'create_contact' );

    if ( $data && isset( $data['id'] ) ) {
        automatorwp_keap_log( 'Contact created: ' . $data['id'], 'info', $contact_data );
        return $data;
    }

    return false;
}

/**
 * Update an existing contact in Keap
 * Endpoint: PATCH /crm/rest/v2/contacts/{id}
 *
 * @since 1.0.0
 *
 * @param int   $contact_id   Contact ID in Keap
 * @param array $contact_data Updated contact information (Keap v2 field names)
 *
 * @return array|false
 */
function automatorwp_keap_update_contact( $contact_id, $contact_data ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    $response = wp_remote_request(
        $api['url_v2'] . '/contacts/' . absint( $contact_id ),
        array(
            'method'  => 'PATCH',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'body'    => wp_json_encode( $contact_data ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $data = automatorwp_keap_handle_api_response( $response, 'update_contact' );

    if ( $data ) {
        automatorwp_keap_log( 'Contact updated: ' . $contact_id, 'info' );
        return $data;
    }

    return false;
}

/**
 * Add tag to a contact
 * Endpoint: POST /crm/rest/v2/tags/{tag_id}/contacts:applyTags
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID in Keap
 * @param int $tag_id     Tag ID
 *
 * @return bool
 */
function automatorwp_keap_add_contact_tag( $contact_id, $tag_id ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    // Resolve tag name to ID if needed
    $resolved_tag_id = automatorwp_keap_resolve_tag_id( $tag_id );

    if ( ! $resolved_tag_id ) {
        automatorwp_keap_log( 'Tag not found: ' . $tag_id, 'error' );
        return false;
    }

    $response = wp_remote_post(
        $api['url_v2'] . '/tags/' . $resolved_tag_id . '/contacts:applyTags',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'body'    => wp_json_encode( array(
                'contact_ids' => array( intval( $contact_id ) ),
            ) ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code === 200 || $status_code === 204 ) {
        automatorwp_keap_log( 'Tag added to contact: ' . $contact_id, 'info', array( 'tag_id' => $resolved_tag_id ) );
        return true;
    }

    automatorwp_keap_log( 'Failed to add tag to contact: ' . $contact_id, 'error', array(
        'tag_id'      => $resolved_tag_id,
        'status_code' => $status_code,
    ) );

    return false;
}

/**
 * Remove tag from a contact
 * Endpoint: DELETE /crm/rest/v2/contacts/{contact_id}/tags/{tag_id}
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID in Keap
 * @param int $tag_id     Tag ID
 *
 * @return bool
 */
function automatorwp_keap_remove_contact_tag( $contact_id, $tag_id ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    $response = wp_remote_request(
        $api['url_v2'] . '/contacts/' . absint( $contact_id ) . '/tags/' . absint( $tag_id ),
        array(
            'method'  => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code === 204 ) {
        automatorwp_keap_log( 'Tag removed from contact: ' . $contact_id, 'info', array( 'tag_id' => $tag_id ) );
        return true;
    }

    return false;
}

/**
 * Get contact by email
 * Endpoint: GET /crm/rest/v2/contacts?email={email}
 *
 * @since 1.0.0
 *
 * @param string $email Contact email
 *
 * @return array|false First matching contact or false
 */
function automatorwp_keap_get_contact_by_email( $email ) {

    $cache_key = 'contact_' . md5( $email );

    return automatorwp_keap_get_cached_data(
        $cache_key,
        function() use ( $email ) {

            $api = automatorwp_keap_get_api();
            if ( ! $api ) {
                return false;
            }

            $response = wp_remote_get(
                $api['url_v2'] . '/contacts?email=' . rawurlencode( $email ),
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api['access_token'],
                    ),
                    'timeout'   => 30,
                    'sslverify' => false,
                )
            );

            $data = automatorwp_keap_handle_api_response( $response, 'get_contact_by_email' );

            // Keap v2 wraps results in { "contacts": [...] }
            if ( isset( $data['contacts'] ) && count( $data['contacts'] ) > 0 ) {
                return $data['contacts'][0];
            }

            return false;
        },
        300 // Cache contact lookups for 5 minutes only
    );
}

/**
 * Add contact to a campaign sequence
 * Endpoint: POST /crm/rest/v1/campaigns/{campaign_id}/sequences/{sequence_id}/contacts
 *
 * @since 1.0.0
 *
 * @param int $contact_id  Contact ID in Keap
 * @param int $campaign_id Campaign ID in Keap
 * @param int $sequence_id Sequence ID within the campaign
 *
 * @return bool
 */
function automatorwp_keap_add_contact_to_campaign( $contact_id, $campaign_id, $sequence_id ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    $response = wp_remote_post(
        $api['url_v1'] . '/campaigns/' . absint( $campaign_id ) . '/sequences/' . absint( $sequence_id ) . '/contacts',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'body'    => wp_json_encode( array(
                'contact_id' => intval( $contact_id ),
            ) ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code === 200 || $status_code === 204 ) {
        automatorwp_keap_log( 'Contact added to campaign sequence', 'info', array(
            'contact_id'  => $contact_id,
            'campaign_id' => $campaign_id,
            'sequence_id' => $sequence_id,
        ) );
        return true;
    }

    automatorwp_keap_log( 'Failed to add contact to campaign', 'error', array(
        'contact_id'  => $contact_id,
        'campaign_id' => $campaign_id,
        'sequence_id' => $sequence_id,
        'status_code' => $status_code,
    ) );

    return false;
}

/**
 * Send email to contact
 * Endpoint: POST /crm/rest/v1/emails/queue
 *
 * @since 1.0.0
 *
 * @param int    $contact_id Contact ID in Keap
 * @param string $subject    Email subject
 * @param string $body_html  Email body (HTML)
 *
 * @return bool
 */
function automatorwp_keap_send_email( $contact_id, $subject, $body_html ) {

    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }

    $response = wp_remote_post(
        $api['url_v1'] . '/emails/queue',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api['access_token'],
            ),
            'body'    => wp_json_encode( array(
                'contacts'     => array( intval( $contact_id ) ),
                'subject'      => sanitize_text_field( $subject ),
                'html_content' => wp_kses_post( $body_html ),
            ) ),
            'timeout' => 30,
            'sslverify' => false,
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code === 200 || $status_code === 204 ) {
        automatorwp_keap_log( 'Email queued for contact: ' . $contact_id, 'info', array( 'subject' => $subject ) );
        return true;
    }

    return false;
}

/**
 * Get all campaigns from Keap
 * Endpoint: GET /crm/rest/v1/campaigns
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_keap_get_campaigns() {

    return automatorwp_keap_get_cached_data(
        'campaigns_list',
        function() {

            $api = automatorwp_keap_get_api();
            if ( ! $api ) {
                return array();
            }

            $response = wp_remote_get(
                $api['url_v1'] . '/campaigns?limit=200',
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api['access_token'],
                    ),
                    'timeout'   => 30,
                    'sslverify' => false,
                )
            );

            $data = automatorwp_keap_handle_api_response( $response, 'get_campaigns' );

            // Keap v1 wraps results in { "campaigns": [...] }
            return ( isset( $data['campaigns'] ) && is_array( $data['campaigns'] ) )
                ? $data['campaigns']
                : array();
        }
    );
}

/**
 * Get sequences for a specific campaign
 * Endpoint: GET /crm/rest/v1/campaigns/{campaign_id}?optional_properties=sequences
 *
 * @since 1.1.0
 *
 * @param int $campaign_id Campaign ID
 *
 * @return array
 */
function automatorwp_keap_get_campaign_sequences( $campaign_id ) {

    $cache_key = 'campaign_sequences_' . absint( $campaign_id );

    return automatorwp_keap_get_cached_data(
        $cache_key,
        function() use ( $campaign_id ) {

            $api = automatorwp_keap_get_api();
            if ( ! $api ) {
                return array();
            }

            $response = wp_remote_get(
                $api['url_v1'] . '/campaigns/' . absint( $campaign_id ) . '?optional_properties=sequences',
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api['access_token'],
                    ),
                    'timeout'   => 30,
                    'sslverify' => false,
                )
            );

            $data = automatorwp_keap_handle_api_response( $response, 'get_campaign_sequences' );

            return ( isset( $data['sequences'] ) && is_array( $data['sequences'] ) )
                ? $data['sequences']
                : array();
        }
    );
}

/**
 * Get all tags from Keap
 * Endpoint: GET /crm/rest/v2/tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_keap_get_tags() {

    return automatorwp_keap_get_cached_data(
        'tags_list',
        function() {

            $api = automatorwp_keap_get_api();
            if ( ! $api ) {
                return array();
            }

            $response = wp_remote_get(
                $api['url_v2'] . '/tags?limit=200',
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api['access_token'],
                    ),
                    'timeout'   => 30,
                    'sslverify' => false,
                )
            );

            $data = automatorwp_keap_handle_api_response( $response, 'get_tags' );

            // Keap v2 wraps results in { "tags": [...] }
            return ( isset( $data['tags'] ) && is_array( $data['tags'] ) )
                ? $data['tags']
                : array();
        }
    );
}