<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Bento\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the Bento url
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_bento_get_url() {

    return 'https://api.getbento.com';

}

/**
 * Helper function to get the Bento API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_bento_get_api() {

    $url = automatorwp_bento_get_url();
    $id = automatorwp_bento_get_option( 'client_id' );
    $secret = automatorwp_bento_get_option( 'client_secret' );

    if( empty( $id ) ) {
        return false;
    }

    return array(
        'url' => $url,
        'token' => $id,
        'secret' => $secret,
    );

}

/**
 * Helper function to check API key
 *
 * @since 1.0.0
 * 
 * @param string    $secret API key
 *
 * @return array|false
 */
function automatorwp_bento_check_api_secret( $secret ) {

    $return = false;

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth
    );

    $response = wp_remote_get( 'https://api.getbento.com/v2/user', array(
        'headers' => $headers
    ) );

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $status_code ) {
        wp_send_json_error (array( 'message' => __( 'Please, check your API key', 'automatorwp-bento' ) ) );
        return $return;
	} else {
        $return = true;
    }

    return $return;

}

/**
 * Helper function to check Account ID
 *
 * @since 1.0.0
 * 
 * @param string    $id Account ID
 * @param string    $secret API key
 *
 * @return array|false
 */
function automatorwp_bento_check_api_key( ) {
    $return = false;

    $api = automatorwp_bento_get_api();

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth
    );

    $response = wp_remote_get( 'https://api.getbento.com/v2/' . $id . '/subscribers', array(
        'headers' => $headers
    ) );

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $status_code ) {
        wp_send_json_error (array( 'message' => __( 'Please, check your Account ID', 'automatorwp-bento' ) ) );
        return $return;
	} else {
        $return = true;
    }

    return $return;

}

/**
 * Create/Update subscriber Bento
 *
 * @since 1.0.0
 * 
 * @param array     $subscriber     The new subscriber data
 */
function automatorwp_bento_create_update_subscriber( $subscriber ) {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return;
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth,
        'Content-Type'  => 'application/json',
        'contentType'   => 'application/json',
    );

    $subscriber_payload = array(
        'email'         => $subscriber['email'],
        'first_name'    => $subscriber['first_name'],
    );

    if( isset( $subscriber['last_name'] ) && ! empty( $subscriber['last_name'] ) ) {
        $subscriber_payload['last_name'] = $subscriber['last_name'];
    }

    $response = wp_remote_post( $api['url'] . '/v2/' . $id . '/subscribers', array(
        'headers' => $headers,
        'body' => json_encode( array(
            'subscribers' => array( $subscriber_payload ),
        ) )
    ) );

    return $response['response']['code'];
}

/**
 * Remove Subscriber
 *
 * @since 1.0.0
 * 
 * @param string     $subscriber_email     The subscriber email
 */
function automatorwp_bento_remove_subscriber( $subscriber_email ) {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return;
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth,
    );

    $response = wp_remote_request( $api['url'] . '/v2/' . $id . '/subscribers/' . $subscriber_email, array(
        'headers' => $headers,
        'method' => 'DELETE',
    ) );

    return $response['response']['code'];
    
}

/**
 * Add tag to Subscriber
 *
 * @since 1.0.0
 * 
 * @param string     $subscriber_email     The subscriber email
 * @param int        $tag                  The tag name
 */
function automatorwp_bento_add_tag_subscriber( $subscriber_email, $tag ) {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return;
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth,
        'Content-Type'  => 'application/json',
        'contentType'   => 'application/json',
    );

    $response = wp_remote_post( $api['url'] . '/v2/' . $id . '/tags', array(
        'headers' => $headers,
        'body' => json_encode( array(
            'tags' => array(
                array(
                    'email'     => $subscriber_email,
                    'tag'      => $tag
                )
            ),
        ) )
    ) );

    return $response['response']['code'];
}

/**
* Get tags from Bento
*
* @since 1.0.0
*
* @param string $search
* @param int $page
*
* @return array
*/
function automatorwp_bento_get_tags( ) {

    $tags = array();

    $api = automatorwp_bento_get_api();
    
    $id = $api['token'];
    $secret = $api['secret'];

    if( ! $api ) {
        return array();
    }

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth
    );

    $response = wp_remote_get( 'https://api.getbento.com/v2/' . $id . '/tags', array(
        'headers' => $headers
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    $tags = $response['tags'];

    return $tags;

}

/**
 * Options callback for Bento tags selector
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_bento_options_cb_tags( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp-bento' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $tag ) {

            // Skip option none
            if( $tag === $none_value ) {
                continue;
            }

            $options[$tag] = $tag;

        }
    }

    return $options;

}

/**
 * Remove tag from Subscriber
 *
 * @since 1.0.0
 * 
 * @param string     $subscriber_email     The subscriber email
 * @param int        $tag                  The tag name
 */
function automatorwp_bento_remove_tag_subscriber( $subscriber_email, $tag ) {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return;
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth,
    );

    $response = wp_remote_request( $api['url'] . '/v2/' . $id . '/subscribers/' . $subscriber_email . '/tags/' . $tag, array(
        'headers' => $headers,
        'method' => 'DELETE',
    ) );

    return $response['response']['code'];
    
}

/**
* Get campaigns from Bento
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_bento_get_campaigns() {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return array();
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth
    );

    $campaigns = array();

    $response = wp_remote_get( 'https://api.getbento.com/v2/' . $id . '/campaigns?status=all', array(
        'headers' => $headers
    ) );

    if( ! is_wp_error( $response ) ) {
        $response = json_decode( wp_remote_retrieve_body( $response ), true );

        if( isset( $response['campaigns'] ) && is_array( $response['campaigns'] ) ) {
            foreach( $response['campaigns'] as $campaign ) {
                if( ! is_array( $campaign ) ) {
                    continue;
                }

                if( empty( $campaign['id'] ) ) {
                    continue;
                }

                $campaigns[] = array(
                    'id' => $campaign['id'],
                    'name' => isset( $campaign['name'] ) ? $campaign['name'] : $campaign['id'],
                );
            }
        }
    }

    if( ! empty( $campaigns ) ) {
        return $campaigns;
    }

    // Single Email Campaigns are exposed as broadcasts on some Bento accounts
    $response = wp_remote_get( 'https://api.getbento.com/v2/' . $id . '/broadcasts?status=all', array(
        'headers' => $headers
    ) );

    if( is_wp_error( $response ) ) {
        return array();
    }

    $response = json_decode( wp_remote_retrieve_body( $response ), true );

    if( isset( $response['broadcasts'] ) && is_array( $response['broadcasts'] ) ) {
        foreach( $response['broadcasts'] as $broadcast ) {
            if( ! is_array( $broadcast ) ) {
                continue;
            }

            if( empty( $broadcast['id'] ) ) {
                continue;
            }

            $campaigns[] = array(
                'id' => $broadcast['id'],
                'name' => isset( $broadcast['name'] ) ? $broadcast['name'] : ( isset( $broadcast['subject'] ) ? $broadcast['subject'] : $broadcast['id'] ),
            );
        }
    }

    return $campaigns;

}

/**
 * Add subscriber to campaign
 *
 * @since 1.0.0
 *
 * @param array     $subscriber     The subscriber data
 * @param int       $campaign_id    Campaign ID
 *
 * @return int|null
 */
function automatorwp_bento_add_subscriber_campaign( $subscriber, $campaign_id ) {

    $api = automatorwp_bento_get_api();

    if( ! $api ) {
        return;
    }

    $id = $api['token'];
    $secret = $api['secret'];

    $base64_auth = base64_encode($secret . ':');

    $headers = array(
        'Authorization' => 'Basic ' . $base64_auth,
        'Content-Type'  => 'application/json',
        'contentType'   => 'application/json',
    );

    $subscriber_payload = array(
        'email'         => $subscriber['email'],
        'first_name'    => $subscriber['first_name'],
    );

    if( isset( $subscriber['last_name'] ) ) {
        $subscriber_payload['last_name'] = $subscriber['last_name'];
    }

    $subscriber_payload['campaigns'] = array(
        array(
            'id' => $campaign_id,
        )
    );

    $response = wp_remote_post( $api['url'] . '/v2/' . $id . '/subscribers', array(
        'headers' => $headers,
        'body' => json_encode( array(
            'subscribers' => array( $subscriber_payload ),
        ) )
    ) );

    if( is_wp_error( $response ) ) {
        return;
    }

    return $response['response']['code'];

}

/**
 * Options callback for Bento campaigns selector
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_bento_options_cb_campaign( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any campaign', 'automatorwp-bento' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $campaign ) {

            // Skip option none
            if( $campaign === $none_value ) {
                continue;
            }

            $options[$campaign] = $campaign;

        }
    }

    return $options;

}
