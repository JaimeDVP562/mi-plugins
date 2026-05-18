<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Keap\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper function to get the Keap API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_keap_get_api() {
	$access_token = automatorwp_keap_get_option( 'access_token', '' );
    // Keap API v2 base URL
	$url = 'https://api.keap.com/crm/rest/v2';

	if ( empty( $access_token ) ) {
		return false;
	}

	return array(
		'access_token' => $access_token,
		'url'          => $url
	);
}

/**
 * Make an API Request to Keap
 *
 * @since 1.0.0
 *
 * @param string $endpoint The API endpoint (e.g., '/contacts')
 * @param string $method   GET, POST, etc.
 * @param array  $body     Request body (optional)
 *
 * @return array|WP_Error
 */
function automatorwp_keap_api_request( $endpoint, $method = 'GET', $body = array() ) {

    $api = automatorwp_keap_get_api();

    if ( ! $api ) {
        return new WP_Error( 'missing_creds', __( 'Keap integration is not configured.', 'automatorwp-keap' ) );
    }

    $args = array(
        'method'  => $method,
        'headers' => array(
            'Content-Type'  => 'application/json',
            'X-Keap-API-Key' => $api['access_token'], // Keap often uses X-Keap-API-Key for Personal Access Tokens, or Authorization: Bearer for OAuth. 
            // Since the field says "Access Token", we assume PAT or similar. 
            // If OAuth, it should be 'Authorization' => 'Bearer ' . $api['access_token']
            // Let's try the standard Bearer first as it's most common for "Access Token".
            'Authorization' => 'Bearer ' . $api['access_token'],
        ),
        'timeout' => 20,
    );

    if ( ! empty( $body ) ) {
        $args['body'] = json_encode( $body );
    }

    // Ensure endpoint has leading slash
    if ( strpos( $endpoint, '/' ) !== 0 ) {
        $endpoint = '/' . $endpoint;
    }

    $response = wp_remote_request( $api['url'] . $endpoint, $args );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( $code >= 400 ) {
         // Attempt to extract error message
         $message = isset( $data['message'] ) ? $data['message'] : wp_remote_retrieve_response_message( $response );
         return new WP_Error( 'api_error', $message, array( 'status' => $code, 'data' => $data ) );
    }

    return $data;
}

/**
 * Check Keap settings status (Test Connection)
 *
 * @since 1.0.0
 *
 * @param array $credentials
 * @return bool
 */
function automatorwp_keap_check_settings_status( $credentials ) {

    $access_token = isset( $credentials['access_token'] ) ? $credentials['access_token'] : '';

    if ( empty( $access_token ) ) {
        return false;
    }
    
    // We manually construct args here to test specific credentials passed from AJAX, not saved ones.
    $url = 'https://api.keap.com/crm/rest/v2/contacts'; // Valid v2 endpoint to test list
    
    $args = array(
        'method'  => 'GET',
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
    );

    $response = wp_remote_get( $url, $args );
    $code     = wp_remote_retrieve_response_code( $response );

    if ( $code === 200 ) {
        return true;
    }

    return false;
}

/**
 * Get Tags from Keap
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_keap_get_tags() {

    $tags = array();
    // Keap API V1 or V2 for tags. V1 is /tags. V2 might differ.
    // Let's use V1 endpoint for tags if V2 is complex, but we defined base URL as V2.
    // Keap V2 doesn't seem to have a simple "list all tags" endpoint easily documented, 
    // but check V1: https://api.infusionsoft.com/crm/rest/v1/tags
    
    // Let's try to handle URL override for V1 if needed, or assume V2 has it. 
    // Actually, V1 is still widely used. Let's stick to V1 for Tags if V2 fails? 
    // No, let's keep it simple. If we use V2 base, we might need to adjust.
    // Wait, the previous code used `https://api.infusionsoft.com/crm/rest/v2/automations`.
    // Let's use the standard Keap REST API Base.
    // Note: Keap API docs say `https://api.keap.com/crm/rest/v1/tags` for listing tags.
    
    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return $tags;
    }
    
    // Override URL for V1 Tags endpoint
    $url = 'https://api.keap.com/crm/rest/v1/tags?limit=1000';
    
    $args = array(
        'headers' => array(
             'Authorization' => 'Bearer ' . $api['access_token'],
        )
    );

    $response = wp_remote_get( $url, $args );
    
    if ( is_wp_error( $response ) ) {
        return $tags;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
        foreach ( $data['tags'] as $tag ) {
            $tags[] = array(
                'id'   => $tag['id'],
                'name' => $tag['name']
            );
        }
    }
    
    return $tags;
}

/**
 * Get Tag Name
 *
 * @since 1.0.0
 *
 * @param int $tag_id
 * @return string
 */
function automatorwp_keap_get_tag_name( $tag_id ) {
    
    if ( empty( $tag_id ) ) {
        return '';
    }
    
    // We can't easily fetch just one tag name without querying all or a specific endpoint.
    // For performance, normally we'd cache. For now, fetch all and filter, or just return ID if not found.
    // Or check if there is a get tag endpoint. /v1/tags/{id} exists.
    
    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return $tag_id;
    }
    
    $url = 'https://api.keap.com/crm/rest/v1/tags/' . $tag_id;
    
    $args = array(
        'headers' => array(
             'Authorization' => 'Bearer ' . $api['access_token'],
        )
    );

    $response = wp_remote_get( $url, $args );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( isset( $data['name'] ) ) {
        return $data['name'];
    }
    
    return $tag_id;
}


/**
 * Create a Contact in Keap
 *
 * @since 1.0.0
 * 
 * @param array $contact_data (email, given_name, family_name, etc.)
 * 
 * @return int|false Contact ID or false
 */
function automatorwp_keap_create_contact( $contact_data ) {
    
    // API V1: POST /contacts
    // API V2: POST /contacts
    
    // Data mapping for V1:
    // { "email_addresses": [ { "email": "..." } ], "given_name": "...", ... }
    
    $body = array(
        'email_addresses' => array(
            array(
                'email' => isset( $contact_data['email'] ) ? $contact_data['email'] : '',
                'field' => 'EMAIL1'
            )
        )
    );
    
    if ( ! empty( $contact_data['first_name'] ) ) {
        $body['given_name'] = $contact_data['first_name'];
    }
    
    if ( ! empty( $contact_data['last_name'] ) ) {
        $body['family_name'] = $contact_data['last_name'];
    }
    
    // Switch to V1 for contacts creation as it's standard
    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }
    
    $url = 'https://api.keap.com/crm/rest/v1/contacts';
    
    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['access_token'],
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode( $body )
    );
    
    $response = wp_remote_post( $url, $args );
    $body     = wp_remote_retrieve_body( $response );
    $data     = json_decode( $body, true );
    
    if ( isset( $data['id'] ) ) {
        return $data['id'];
    }
    
    return false;
}

/**
 * Add Tag to Contact
 *
 * @since 1.0.0
 * 
 * @param int $contact_id
 * @param int $tag_id
 * 
 * @return bool
 */
function automatorwp_keap_add_tag_to_contact( $contact_id, $tag_id ) {
    
    if ( empty( $contact_id ) || empty( $tag_id ) ) {
        return false;
    }
    
    // POST /contacts/{contactId}/tags
    // Body: { "tagIds": [ tagId ] }
    
    $api = automatorwp_keap_get_api();
    if ( ! $api ) {
        return false;
    }
    
    $url = "https://api.keap.com/crm/rest/v1/contacts/{$contact_id}/tags";
    
    $body = array(
        'tagIds' => array( (int) $tag_id )
    );
    
    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['access_token'],
            'Content-Type'  => 'application/json',
        ),
        'body'    => json_encode( $body )
    );
    
    $response = wp_remote_post( $url, $args );
    $code     = wp_remote_retrieve_response_code( $response );
    
    if ( $code === 200 || $code === 201 ) {
        return true;
    }
    
    return false;
}

/* Callbacks for Options */

/**
 * Options callback for Tags
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 * @return array
 */
function automatorwp_keap_options_cb_tags( $field ) {
    
    $value      = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp-keap' );
    $options    = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if ( ! empty( $value ) ) {
        if ( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach ( $value as $tag_id ) {
            if ( $tag_id === $none_value ) {
                continue;
            }
            $options[$tag_id] = automatorwp_keap_get_tag_name( $tag_id );
        }
    }

    return $options;
}


    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idLabels', array(
        'body' => array(
            'value'=> $label_id,
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;

}

/**
 * Add member in card
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_keap_add_member( $card_id, $member_id ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idMembers', array(
        'body' => array(
            'value'=> $member_id,
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Add checklist item in checklist
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_keap_add_checklist_item( $checklist_id, $checklist_item ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/checklists/' . $checklist_id . '/checkItems', array(
        'body' => array(
            'name'=> $checklist_item,
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
    
}

/**
 * Change card list
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * @param string    $list_id        List id
 * 
 * @return int
 */
function automatorwp_keap_change_card_list( $card_id, $new_list_id ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'idList' => $new_list_id,
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}

/**
 * Change card description
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_keap_change_card_desc( $card_id, $new_desc ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'desc' => $new_desc,
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}

/**
 * Archive card
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_keap_archive_card( $card_id ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'closed' => true,
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
 * Delete card
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_keap_delete_card( $card_id ) {

    $api = automatorwp_keap_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'token' => $api['access_token']
        ),
        'method' => 'DELETE'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}