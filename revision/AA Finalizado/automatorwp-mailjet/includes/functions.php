<?php

/**
 * Helper function to get the Mailjet API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_mailjet_get_api()
{
    $api_key = automatorwp_mailjet_get_option('api_key', ''); // API_KEY
    $secret_key = automatorwp_mailjet_get_option('secret_key', ''); // SECRET_KEY
    $url = 'https://api.mailjet.com/v3/REST/contact'; // URL base de Mailjet

    if (empty($secret_key) || empty($api_key)) {
        return false;
    }

    return array(
        'api_key' => $api_key,
        'secret_key'  => $secret_key,
        'url'     => $url,
    );
}

function automatorwp_mailjet_check_settings_status($credentials)
{
    $return = false;

    $response = wp_remote_get('https://api.mailjet.com/v3/REST/contact', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($credentials['api_key'] . ':' . $credentials['secret_key'])
        ),
    ));

    $status_code = wp_remote_retrieve_response_code($response);

    if (200 !== $status_code) {
        wp_send_json_error(array('message' => __('Please, check your API credentials', 'automatorwp-mailjet')));
        return $return;
    } else {
        $return = true;
    }

    return $return;
}

/**
 * Delete contact
 *
 * @since 1.0.0
 * 
 * @param string    $contact_email   Contact email address
 * 
 * @return int|false                  HTTP status code or false on failure
 */
function automatorwp_mailjet_delete_contact($contact_email)
{

    $api = automatorwp_mailjet_get_api();

    if (! $api || empty($contact_email)) {
        return false;
    }

    $response = wp_remote_request("https://api.mailjet.com/v4/contacts/{$contact_email}", array(
        "method"    => "DELETE",
        'headers'   => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key']),
            'Content-Type'  => 'application/json'
        ),
    ));

    if (is_wp_error($response)) {
        //error_log('Mailjet Response: ' . print_r($response, true), 3, 'debug3.log');
        return false;
    }

    return wp_remote_retrieve_response_code($response);
}
/**
 * Create Contact
 *
 * @since 1.0.0
 * 
 * @param string    $first_name
 * @param string    $email      
 * 
 * @return int
 */
function automatorwp_mailjet_create_contact($first_name, $email)
{

    $api = automatorwp_mailjet_get_api();

    if (! $api) {
        return;
    }
    $body = array(
        'Name' => $first_name,
        'Email' => $email,
    );
    $response = wp_remote_post('https://api.mailjet.com/v3/REST/contact', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key']),
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode($body),
    ));

    $status_code = wp_remote_retrieve_response_code($response);

    return $status_code;
}
/**
 * Update a Mailjet contact.
 *
 * @since 1.0.0
 *
 * @param string $email       Email of the contact to update.
 * @param string $first_name  New first name (optional).
 *
 * @return int|null Response status code from Mailjet or null if API not configured.
 */
ffunction automatorwp_mailjet_update_contact( $email, $first_name = '' )
{

       $api = automatorwp_mailjet_get_api();

    if ( ! $api ) {
        return;
    }

    if ( $first_name === '' ) {
        return 200;
    }

    $body = array(
        'Data' => array(
            array(
                'Name'  => 'Name',      
                'Value' => $first_name,  
            ),
        ),
    );

    $response = wp_remote_request(
        'https://api.mailjet.com/v3/REST/contactdata/' . rawurlencode( $email ),
        array(
            'method'  => 'PUT',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $api['api_key'] . ':' . $api['secret_key'] ),
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( $body ),
        )
    );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}
}

/**
 * Create Contact list
 *
 * @since 1.0.0
 * 
 * @param string    $name      List name
 * 
 * @return int                 HTTP status code
 */
function automatorwp_mailjet_create_contact_list($name)
{

    $api = automatorwp_mailjet_get_api();

    if (! $api) {
        return;
    }
    $body = array(
        'Name' => $name,
    );
    $response = wp_remote_post('https://api.mailjet.com/v3/REST/contactslist', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key']),
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode($body),
    ));

    $status_code = wp_remote_retrieve_response_code($response);

    return $status_code;
}

/**
 * Get Contacts from Mailjet
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_mailjet_get_contacts()
{
    $contacts = array();

    $api = automatorwp_mailjet_get_api();
    if (! $api) {
        return $contacts;
    }

    $response = wp_remote_get('https://api.mailjet.com/v3/REST/contact', array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key'])
        ),
    ));

    if (is_wp_error($response)) {
        return $contacts;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['Data'])) {
        return $body['Data'];
    }

    return $contacts;
}

/**
 * Add contact to a Mailjet list
 *
 * @since 1.0.0
 * 
 * @param string    $email      Contact email address
 * @param int       $list_id    Mailjet list ID
 * 
 * @return int|false            HTTP status code or false on failure
 */
function automatorwp_mailjet_add_contact_to_list($contact_id, $list_id)
{

    $api = automatorwp_mailjet_get_api();

    if (! $api) {
        return false;
    }

    // Prepare the request body
    $body = array(
        'IsUnsubscribed' => false,
        'ContactID' => $contact_id,
        'ListID' => $list_id
    );

    // Make the API request to add contact to list
    $response = wp_remote_post(
        'https://api.mailjet.com/v3/REST/listrecipient',
        array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key']),
                'Content-Type'  => 'application/json'
            ),
            'body' => json_encode($body),
        )
    );

    // Check for WP error
    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    return $status_code;
}

function automatorwp_mailjet_get_lists($search = '')
{
    $api = automatorwp_mailjet_get_api();

    if (! $api) {
        return false;
    }

    $url = 'https://api.mailjet.com/v3/REST/contactslist?Limit=100';

    if (! empty($search)) {
        $url .= '&NameLike=' . urlencode($search);
    }

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api['api_key'] . ':' . $api['secret_key'])
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        //error_log('Mailjet API Error (get lists): ' . $response->get_error_message(), 3, ABSPATH . 'debug.log');
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code !== 200 || ! isset($body['Data'])) {
        return false;
    }

    return $body['Data'];
}
