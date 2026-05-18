<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Brevo\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Helper function to get the Brevo API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_brevo_get_api()
{

    $url = automatorwp_brevo_get_url();
    $token = automatorwp_brevo_get_option('token', '');

    if (empty($token)) {
        return false;
    }

    return array(
        'url' => $url,
        'token' => $token,
    );

}

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_brevo_get_option($option_name, $default = false)
{

    $prefix = 'automatorwp_brevo_';

    return automatorwp_get_option($prefix . $option_name, $default);
}

/**
 * Helper function to get the Brevo url
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_brevo_get_url()
{

    return 'https://api.brevo.com/v3/';

}

/**
 * Get folders from Brevo
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_brevo_get_folders()
{

    $folders = array();

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . '/contacts/folders',
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            )
        )
    );
    $response = json_decode(wp_remote_retrieve_body($response), true);

    foreach ($response['folders'] as $folder) {

        $folders[] = array(
            'id' => $folder['id'],
            'name' => $folder['name'],
        );

    }

    return $folders;

}

/**
 * Get folder from Brevo
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_brevo_options_cb_folder($field)
{

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any folder', 'automatorwp-brevo');
    $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    if (!empty($value)) {
        if (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $folder_id) {

            // Skip option none
            if ($folder_id === $none_value) {
                continue;
            }

            $options[$folder_id] = automatorwp_brevo_get_folder_name($folder_id);
        }
    }
    return $options;

}

/**
 * Get the folder name
 *
 * @since 1.0.0
 * 
 * @param string $team_id
 *
 * @return array
 */
function automatorwp_brevo_get_folder_name($folder_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/contacts/folders/$folder_id",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['error']['code']) === 404 || !isset($response['name'])) {
        return;
    }

    return $response['name'];
}

// lists

/**
 * Get lists from Brevo
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_brevo_get_lists($folder_id)
{
    $lists = array();

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/contacts/folders/$folder_id/lists",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            )
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    foreach ($response['lists'] as $list) {

        $lists[] = array(
            'id' => $list['id'],
            'name' => $list['name'],
        );

    }
    return $lists;

}

/**
 * Get list from Brevo
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_brevo_options_cb_list($field)
{

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any folder', 'automatorwp-brevo');
    $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    if (!empty($value)) {
        if (!is_array($value)) {
            $value = array($value);
        }
        foreach ($value as $list_id) {

            // Skip option none
            if ($list_id === $none_value) {
                continue;
            }

            $options[$list_id] = automatorwp_brevo_get_list_name($list_id);
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
function automatorwp_brevo_get_list_name($list_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/contacts/lists/$list_id",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['error']['code']) === 404 || !isset($response['name'])) {
        return;
    }

    return $response['name'];
}


// lists

/**
 * Get contacts from Brevo
 *
 * @since 1.0.0
 * 
 * @param string $list_id
 *
 * @return array
 */
function automatorwp_brevo_get_contacts_in_list($list_id)
{
    $contacts = array();

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/contacts/lists/$list_id/contacts",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            )
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    foreach ($response['contacts'] as $contact) {

        $contacts[] = array(
            'id' => $contact['id'],
            'email' => $contact['email'],
        );

    }
    return $contacts;

}

/**
 * Get contact from Brevo
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_brevo_options_cb_contact_in_list($field)
{

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any contact', 'automatorwp-brevo');
    $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    if (!empty($value)) {
        if (!is_array($value)) {
            $value = array($value);
        }
        foreach ($value as $contact_id) {

            // Skip option none
            if ($contact_id === $none_value) {
                continue;
            }

            $options[$contact_id] = automatorwp_brevo_get_contact_email($contact_id);
        }
    }

    return $options;

}

/**
 * Get the list name
 *
 * @since 1.0.0
 * 
 * @param string $contact_id
 *
 * @return array
 */
function automatorwp_brevo_get_contact_email($contact_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/contacts/$contact_id",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['error']['code']) === 404 || !isset($response['name'])) {
        return;
    }

    return $response['email'];
}


/**
 * Get pipelines from Brevo
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_brevo_get_pipelines()
{
    $pipelines = array();

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/crm/pipeline/details/all",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            )
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    foreach ($response as $pipeline) {

        $pipelines[] = array(
            'id' => $pipeline['pipeline'],
            'name' => $pipeline['pipeline_name'],
        );

    }
    return $pipelines;

}

/**
 * Get pipeline from Brevo
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_brevo_options_cb_pipeline($field)
{

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any pipeline', 'automatorwp-brevo');
    $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    if (!empty($value)) {
        if (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $pipeline_id) {

            // Skip option none
            if ($pipeline_id === $none_value) {
                continue;
            }

            $options[$pipeline_id] = automatorwp_brevo_get_pipeline_name($pipeline_id);
        }
    }
    return $options;

}

/**
 * Get the pipeline name
 *
 * @since 1.0.0
 * 
 * @param string $team_id
 *
 * @return array
 */
function automatorwp_brevo_get_pipeline_name($pipeline_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/crm/pipeline/details/$pipeline_id",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['error']['code']) === 404 || !isset($response[0]['pipeline_name'])) {
        return;
    }

    return $response[0]['pipeline_name'];
}


/**
 * Get stages from Brevo
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_brevo_get_stages($pipeline_id)
{
    $stages = array();

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_get(
        $api['url'] . "/crm/pipeline/details/$pipeline_id",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            )
        )
    );

    $response = json_decode(wp_remote_retrieve_body($response), true);

    foreach ($response[0]['stages'] as $stage) {

        $stages[] = array(
            'id' => $stage['id'],
            'name' => $stage['name'],
        );

    }
    return $stages;

}

/**
 * Get stage from Brevo
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_brevo_options_cb_stage($field)
{

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any folder', 'automatorwp-brevo');
    $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    return $options;

}

//<---------------------------------------------->

/**
 * Add contact to a list
 *
 * @since 1.0.0
 * 
 * @param double    $listId list id
 * @param string    $email  contact email
 * 
 * @return int
 */
function automatorwp_brevo_add_contact_to_list($email, $listId)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_post(
        $api['url'] . "/contacts/lists/$listId/contacts/add",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                array(
                    'emails' => array(
                        $email
                    )
                )
            )
        )
    );

    return $response['response']['code'];
}

/**
 * Create a contact
 *
 * @since 1.0.0
 * 
 * @param array    $contact_data contact data
 * 
 * @return int
 */
function automatorwp_brevo_create_contact($contact_data)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    // Try to create contact
    $response = wp_remote_post(
        $api['url'] . '/contacts',
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                $contact_data
            )
        )
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $code = isset($response['response']['code']) ? (int) $response['response']['code'] : null;

    // If created or updated response returned directly, return it
    if ($code === 201 || $code === 204) {
        return $code;
    }

    // If create failed, try to find existing contact by email and update it
    $email = isset($contact_data['email']) ? $contact_data['email'] : '';
    if (!empty($email)) {
        $search = wp_remote_get(
            $api['url'] . '/contacts?email=' . rawurlencode($email),
            array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'api-key' => $api['token']
                )
            )
        );

        if (!is_wp_error($search)) {
            $body = json_decode(wp_remote_retrieve_body($search), true);
            if (!empty($body['contacts']) && !empty($body['contacts'][0]['id'])) {
                $contact_id = $body['contacts'][0]['id'];

                // Prepare update payload: API accepts attributes for PUT
                $update_payload = array();
                if (isset($contact_data['attributes'])) {
                    $update_payload['attributes'] = $contact_data['attributes'];
                }
                // Keep email if provided
                if (!empty($email)) {
                    $update_payload['email'] = $email;
                }

                $put = wp_remote_request(
                    $api['url'] . "/contacts/{$contact_id}",
                    array(
                        'method' => 'PUT',
                        'headers' => array(
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'api-key' => $api['token']
                        ),
                        'body' => json_encode($update_payload),
                    )
                );

                if (!is_wp_error($put) && isset($put['response']['code'])) {
                    return (int) $put['response']['code'];
                }
            }
        }
    }

    // Return original code if nothing else matched
    return $code;
}

/**
 * Create a list
 *
 * @since 1.0.0
 * 
 * @param double    $list_name list name
 * @param string    $folder_id  folder id
 * 
 * @return int
 */
function automatorwp_brevo_create_list($list_name, $folder_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_post(
        $api['url'] . '/contacts/lists',
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                array(
                    'name' => $list_name,
                    'folderId' => intval($folder_id),
                )
            )
        )
    );

    return $response['response']['code'];
}

/**
 * Create a deal
 *
 * @since 1.0.0
 * 
 * @param array    $deal_data deal data
 * 
 * @return int
 */
function automatorwp_brevo_create_deal($deal_data)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_post(
        $api['url'] . '/crm/deals',
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                $deal_data
            )
        )
    );

    return $response['response']['code'];
}

/**
 * Send a transactional email
 *
 * @since 1.0.0
 * 
 * @param array    $email_data email data
 * 
 * @return int
 */
function automatorwp_brevo_send_email($email_data)
{
    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }

    $response = wp_remote_post(
        $api['url'] . '/smtp/email',
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                $email_data
            )
        )
    );

    return $response['response']['code'];
}


/**
 * Remove a contact from a list
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID to remove.
 * @param int $list_id    List ID.
 *
 * @return int
 */
function automatorwp_brevo_remove_contact_from_list($contact_id, $list_id)
{

    $api = automatorwp_brevo_get_api();

    if (!$api) {
        return;
    }
    
    $response = wp_remote_post(
        $api['url'] . "/contacts/lists/$list_id/contacts/remove",
        array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'api-key' => $api['token']
            ),
            'body' => json_encode(
                array(
                    'ids' => array(
                        intval($contact_id)
                    )
                )
            )
        )
    );
    
    return $response['response']['code'];
}